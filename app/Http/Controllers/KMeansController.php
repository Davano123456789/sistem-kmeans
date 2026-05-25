<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Centroid;
use App\Models\RiwayatClustering;
use App\Models\HasilClustering;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KMeansController extends Controller
{
    public function index()
    {
        $mahasiswa = Mahasiswa::with('nilaiKuesioner')->get();
        return view('kmeans.index', compact('mahasiswa'));
    }

    public function hitung(Request $request)
    {
        $request->validate([
            'centroids' => 'required|array|size:4',
            'centroids.*' => 'required|exists:mahasiswa,id_mahasiswa',
        ]);

        $features = ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'b3', 'b4', 'd1', 'd2', 'd3', 'd4'];
        
        // 1. Ambil data semua mahasiswa yang punya nilai
        $allMahasiswa = Mahasiswa::with('nilaiKuesioner')->whereHas('nilaiKuesioner')->get();
        
        if ($allMahasiswa->count() < 4) {
            return back()->with('error', 'Data mahasiswa minimal harus 4 untuk proses clustering.');
        }

        // 2. Inisialisasi Centroid Awal dari pilihan user
        $centroids = [];
        foreach ($request->centroids as $index => $id) {
            $mhs = Mahasiswa::with('nilaiKuesioner')->find($id);
            foreach ($features as $feature) {
                $centroids[$index][$feature] = $mhs->nilaiKuesioner->$feature ?? 0;
            }
        }

        $history = [];
        $maxIterations = 10; // Batasi iterasi agar tidak loop selamanya jika data fluktuatif
        $converged = false;

        for ($iter = 1; $iter <= $maxIterations; $iter++) {
            $clusters = [[], [], [], []];
            $iterResults = [];

            // Ambil info cluster dari iterasi sebelumnya jika ada
            $prevClusters = [];
            if ($iter > 1) {
                foreach ($history[$iter - 2]['results'] as $prevRes) {
                    $prevClusters[$prevRes['mahasiswa']->id_mahasiswa] = $prevRes['cluster'];
                }
            }

            // 3. Hitung Jarak Euclidean & Assign Cluster
            foreach ($allMahasiswa as $mhs) {
                $distances = [];
                foreach ($centroids as $cIndex => $centroid) {
                    $sum = 0;
                    foreach ($features as $feature) {
                        $val = $mhs->nilaiKuesioner->$feature ?? 0;
                        $sum += pow($val - $centroid[$feature], 2);
                    }
                    $distances[$cIndex] = sqrt($sum);
                }

                $minDistance = min($distances);
                $assignedCluster = array_search($minDistance, $distances);
                
                $clusters[$assignedCluster][] = $mhs;
                
                $currentCluster = $assignedCluster + 1;
                $moved = false;
                if ($iter > 1 && isset($prevClusters[$mhs->id_mahasiswa])) {
                    if ($prevClusters[$mhs->id_mahasiswa] !== $currentCluster) {
                        $moved = true;
                    }
                }
                
                $iterResults[] = [
                    'mahasiswa' => $mhs,
                    'distances' => $distances,
                    'min_distance' => $minDistance,
                    'cluster' => $currentCluster,
                    'moved' => $moved
                ];
            }

            // =====================================
            // 📊 HITUNG PCA UNTUK ITERASI INI
            // =====================================
            // Format input: Array Nx4 (jarak C1, C2, C3, C4 dari masing-masing mahasiswa)
            $pcaInput = [];
            foreach ($iterResults as $res) {
                $pcaInput[] = $res['distances'];
            }

            $pcaResult = $this->calculatePCA($pcaInput);
            $projection = $pcaResult['projection'];

            // 📍 Penyelarasan letak sumbu PC1 (Horizontal Anchor Flip) agar letak kelompok presisi sama dengan Python
            // Kita ingin Centroid Cluster 1 (Application Developer) berada di sebelah kanan (PC1 > 0)
            $sumPC1_C1 = 0.0;
            $countC1 = 0;
            foreach ($iterResults as $idx => $res) {
                if ($res['cluster'] === 1) { // Cluster 1: Application Developer
                    $sumPC1_C1 += $projection[$idx]['PC1'];
                    $countC1++;
                }
            }
            
            $flipPC1 = 1.0;
            if ($countC1 > 0) {
                $meanPC1_C1 = $sumPC1_C1 / $countC1;
                if ($meanPC1_C1 < 0.0) $flipPC1 = -1.0;
            }

            // Terapkan flip PC1 ke seluruh proyeksi (PC2 sumbu vertikal dibiarkan natural & deterministik)
            foreach ($projection as &$p) {
                $p['PC1'] *= $flipPC1;
            }
            unset($p);

            // Tempelkan nilai PC1 dan PC2 ke hasil iterasi dan model mahasiswa
            foreach ($iterResults as $idx => &$res) {
                $res['PC1'] = $projection[$idx]['PC1'];
                $res['PC2'] = $projection[$idx]['PC2'];
                
                // Tempelkan juga ke model agar terbaca di tab anggota kelompok
                $res['mahasiswa']->PC1 = $projection[$idx]['PC1'];
                $res['mahasiswa']->PC2 = $projection[$idx]['PC2'];
            }
            unset($res); // putus referensi

            // =====================================
            // 📍 HITUNG CENTROID PCA & CONVEX HULL
            // =====================================
            $centroidsPca = [];
            $clusterPoints = [[], [], [], []];

            // Kumpulkan koordinat PC untuk tiap cluster
            foreach ($iterResults as $res) {
                $cIdx = $res['cluster'] - 1;
                $clusterPoints[$cIdx][] = [
                    'nama' => $res['mahasiswa']->nama,
                    'x' => $res['PC1'],
                    'y' => $res['PC2']
                ];
            }

            // Hitung rata-rata PC1 & PC2 untuk centroid PCA, dan buat Convex Hull
            $hulls = [];
            foreach ($clusterPoints as $cIdx => $points) {
                if (count($points) > 0) {
                    $sumPC1 = 0.0;
                    $sumPC2 = 0.0;
                    foreach ($points as $pt) {
                        $sumPC1 += $pt['x'];
                        $sumPC2 += $pt['y'];
                    }
                    $centroidsPca[$cIdx] = [
                        'PC1' => $sumPC1 / count($points),
                        'PC2' => $sumPC2 / count($points)
                    ];
                } else {
                    $centroidsPca[$cIdx] = [
                        'PC1' => 0.0,
                        'PC2' => 0.0
                    ];
                }

                // Hitung Convex Hull jika minimal ada 3 titik
                if (count($points) >= 3) {
                    $hulls[$cIdx] = $this->getConvexHull($points);
                } else {
                    $hulls[$cIdx] = [];
                }
            }

            $history[] = [
                'iterasi' => $iter,
                'centroids' => $centroids,
                'results' => $iterResults,
                'clusters' => $clusters,
                'centroids_pca' => $centroidsPca,
                'hulls' => $hulls,
                'explained_variance_ratio' => $pcaResult['explained_variance_ratio']
            ];

            // 4. Hitung Centroid Baru
            $newCentroids = [];
            foreach ($clusters as $cIndex => $members) {
                if (count($members) > 0) {
                    foreach ($features as $feature) {
                        $sum = 0;
                        foreach ($members as $m) {
                            $sum += $m->nilaiKuesioner->$feature ?? 0;
                        }
                        $newCentroids[$cIndex][$feature] = $sum / count($members);
                    }
                } else {
                    // Jika cluster kosong, tetap gunakan centroid lama
                    $newCentroids[$cIndex] = $centroids[$cIndex];
                }
            }

            // 5. Cek Konvergensi (Apakah centroid berubah?)
            if ($centroids === $newCentroids) {
                $converged = true;
                break;
            }

            $centroids = $newCentroids;
        }

        // =====================================
        // 💾 SIMPAN DATA EXPORT KE SESSION
        // =====================================
        $exportData = [];
        foreach ($history as $h) {
            $iterNum = $h['iterasi'];
            $exportData[$iterNum] = [
                'centroids_pca' => $h['centroids_pca'],
                'clusters' => []
            ];
            
            for ($c = 0; $c < 4; $c++) {
                $exportData[$iterNum]['clusters'][$c] = [];
            }

            foreach ($h['results'] as $res) {
                $cIdx = $res['cluster'] - 1;
                $exportData[$iterNum]['clusters'][$cIdx][] = [
                    'nama' => $res['mahasiswa']->nama,
                    'PC1' => $res['PC1'],
                    'PC2' => $res['PC2']
                ];
            }
        }
        
        session([
            'kmeans_export_data' => $exportData
        ]);

        return view('kmeans.index', [
            'mahasiswa' => $allMahasiswa,
            'history' => $history,
            'converged' => $converged,
            'selectedCentroids' => $request->centroids
        ]);
    }

    public function export(Request $request)
    {
        $iterasi = $request->query('iterasi');
        $exportData = session('kmeans_export_data');
        
        if (!$exportData || !isset($exportData[$iterasi])) {
            return back()->with('error', 'Data export tidak ditemukan. Silakan jalankan perhitungan K-Means terlebih dahulu.');
        }
        
        $iterData = $exportData[$iterasi];
        $centroidsPca = $iterData['centroids_pca'];
        $clustersData = $iterData['clusters'];
        
        $topics = [
            'Application Developer',
            'Data Analyst',
            'System Analyst',
            'IT Auditor & Governance'
        ];
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // hapus sheet bawaan
        
        for ($cIdx = 0; $cIdx < 4; $cIdx++) {
            $members = $clustersData[$cIdx] ?? [];
            $centroidRow = $centroidsPca[$cIdx];
            $topicName = $topics[$cIdx];
            
            $ws = $spreadsheet->createSheet();
            // Judul sheet maksimal 31 karakter
            $sheetTitle = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $topicName), 0, 30);
            $ws->setTitle($sheetTitle ?: "Cluster " . ($cIdx + 1));
            
            // 1. TULIS INFO CENTROID DI ATAS
            $ws->setCellValue('A1', "Cluster " . ($cIdx + 1) . " (" . $topicName . ")");
            $ws->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            
            $ws->setCellValue('A2', "Jumlah Mahasiswa");
            $ws->setCellValue('B2', count($members));
            
            $ws->setCellValue('A3', "Centroid PC1");
            $ws->setCellValue('B3', round($centroidRow['PC1'], 3));
            
            $ws->setCellValue('A4', "Centroid PC2");
            $ws->setCellValue('B4', round($centroidRow['PC2'], 3));
            
            // Baris kosong
            
            // HEADER DATA
            $ws->setCellValue('A6', "Nama");
            $ws->setCellValue('B6', "PC1");
            $ws->setCellValue('C6', "PC2");
            $ws->getStyle('A6:C6')->getFont()->setBold(true);
            
            // DATA MAHASISWA
            $rowNum = 7;
            foreach ($members as $m) {
                $ws->setCellValue('A' . $rowNum, $m['nama']);
                $ws->setCellValue('B' . $rowNum, round($m['PC1'], 3));
                $ws->setCellValue('C' . $rowNum, round($m['PC2'], 3));
                $rowNum++;
            }
            
            // Auto size kolom
            foreach (range('A', 'C') as $col) {
                $ws->getColumnDimension($col)->setAutoSize(true);
            }
        }
        
        $fileName = "hasil_cluster_pca_iterasi_" . $iterasi . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Helper PCA (Principal Component Analysis) 2 Dimensi.
     * Menggunakan Standardisasi, Matriks Kovarians, dan Deflation Power Iteration.
     */
    private function calculatePCA($data)
    {
        $n = count($data);
        if ($n === 0) return ['projection' => [], 'explained_variance_ratio' => [0.0, 0.0]];
        $d = count($data[0]);

        // 1. Standardisasi (StandardScaler)
        $means = array_fill(0, $d, 0.0);
        $stds = array_fill(0, $d, 0.0);

        for ($j = 0; $j < $d; $j++) {
            $sum = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sum += $data[$i][$j];
            }
            $means[$j] = $sum / $n;
        }

        for ($j = 0; $j < $d; $j++) {
            $sumSq = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sumSq += pow($data[$i][$j] - $means[$j], 2);
            }
            $stds[$j] = sqrt($sumSq / $n);
            if ($stds[$j] == 0.0) $stds[$j] = 1.0;
        }

        $scaled = [];
        for ($i = 0; $i < $n; $i++) {
            $scaled[$i] = [];
            for ($j = 0; $j < $d; $j++) {
                $scaled[$i][$j] = ($data[$i][$j] - $means[$j]) / $stds[$j];
            }
        }

        // 2. Buat Matriks Kovarians (ukuran d x d)
        $cov = [];
        for ($j = 0; $j < $d; $j++) {
            $cov[$j] = array_fill(0, $d, 0.0);
        }

        for ($j = 0; $j < $d; $j++) {
            for ($k = 0; $k < $d; $k++) {
                $sum = 0.0;
                for ($i = 0; $i < $n; $i++) {
                    $sum += $scaled[$i][$j] * $scaled[$i][$k];
                }
                $cov[$j][$k] = $sum / $n; // bias (seperti StandardScaler di python)
            }
        }

        // Helper vector norm
        $norm = function($v) {
            $sum = 0.0;
            foreach ($v as $val) $sum += $val * $val;
            return sqrt($sum);
        };

        // Helper perkalian matriks-vektor
        $matMul = function($matrix, $vector) use ($d) {
            $result = array_fill(0, $d, 0.0);
            for ($i = 0; $i < $d; $i++) {
                for ($j = 0; $j < $d; $j++) {
                    $result[$i] += $matrix[$i][$j] * $vector[$j];
                }
            }
            return $result;
        };

        // Helper Power Iteration
        $powerIteration = function($matrix) use ($d, $norm, $matMul) {
            $v = array_fill(0, $d, 1.0);
            $vNorm = $norm($v);
            for ($i = 0; $i < $d; $i++) $v[$i] /= $vNorm;

            $maxIter = 250;
            for ($iter = 0; $iter < $maxIter; $iter++) {
                $w = $matMul($matrix, $v);
                $wNorm = $norm($w);
                if ($wNorm < 1e-9) break;
                
                $nextV = [];
                for ($i = 0; $i < $d; $i++) $nextV[$i] = $w[$i] / $wNorm;
                
                $diff = 0.0;
                for ($i = 0; $i < $d; $i++) $diff += abs($v[$i] - $nextV[$i]);
                if ($diff < 1e-12) {
                    $v = $nextV;
                    break;
                }
                $v = $nextV;
            }

            $Av = $matMul($matrix, $v);
            $eigenvalue = 0.0;
            for ($i = 0; $i < $d; $i++) $eigenvalue += $v[$i] * $Av[$i];

            return ['vector' => $v, 'value' => $eigenvalue];
        };

        // Cari Principal Component ke-1
        $pc1Result = $powerIteration($cov);
        $v1 = $pc1Result['vector'];
        $val1 = $pc1Result['value'];

        // Deflasikan matriks untuk mencari PC ke-2
        $covDeflated = [];
        for ($i = 0; $i < $d; $i++) {
            $covDeflated[$i] = [];
            for ($j = 0; $j < $d; $j++) {
                $covDeflated[$i][$j] = $cov[$i][$j] - $val1 * $v1[$i] * $v1[$j];
            }
        }

        // Cari Principal Component ke-2
        $pc2Result = $powerIteration($covDeflated);
        $v2 = $pc2Result['vector'];
        $val2 = $pc2Result['value'];

        // Proyeksikan data ke PC1 & PC2
        // 6. Penyelarasan tanda Eigenvector (Deterministic Eigenvector Sign Flip)
        // Memaksa elemen dengan nilai absolut terbesar pada masing-masing eigenvector bernilai positif.
        // Ini adalah standar konvensi pencerminan sumbu dalam PCA (seperti prcomp di R).
        $maxIdx1 = 0;
        $maxVal1 = 0.0;
        for ($i = 0; $i < $d; $i++) {
            if (abs($v1[$i]) > $maxVal1) {
                $maxVal1 = abs($v1[$i]);
                $maxIdx1 = $i;
            }
        }
        if ($v1[$maxIdx1] < 0.0) {
            for ($i = 0; $i < $d; $i++) $v1[$i] *= -1.0;
        }

        $maxIdx2 = 0;
        $maxVal2 = 0.0;
        for ($i = 0; $i < $d; $i++) {
            if (abs($v2[$i]) > $maxVal2) {
                $maxVal2 = abs($v2[$i]);
                $maxIdx2 = $i;
            }
        }
        if ($v2[$maxIdx2] < 0.0) {
            for ($i = 0; $i < $d; $i++) $v2[$i] *= -1.0;
        }

        // Proyeksikan data ke PC1 & PC2
        $projection = [];
        for ($i = 0; $i < $n; $i++) {
            $pc1 = 0.0;
            $pc2 = 0.0;
            for ($j = 0; $j < $d; $j++) {
                $pc1 += $scaled[$i][$j] * $v1[$j];
                $pc2 += $scaled[$i][$j] * $v2[$j];
            }
            $projection[] = ['PC1' => $pc1, 'PC2' => $pc2];
        }

        $totalVar = 0.0;
        for ($i = 0; $i < $d; $i++) {
            $totalVar += $cov[$i][$i];
        }

        return [
            'projection' => $projection,
            'explained_variance_ratio' => [
                $totalVar > 0 ? $val1 / $totalVar : 0.0,
                $totalVar > 0 ? $val2 / $totalVar : 0.0
            ]
        ];
    }

    /**
     * Helper Andrew's Monotone Chain Convex Hull.
     * Mengembalikan koordinat pembatas terluar (closed loop).
     */
    private function getConvexHull($points)
    {
        $n = count($points);
        if ($n < 3) return $points;

        // Urutkan titik berdasarkan X, lalu berdasarkan Y
        usort($points, function($a, $b) {
            if (abs($a['x'] - $b['x']) < 1e-9) {
                return $a['y'] <=> $b['y'];
            }
            return $a['x'] <=> $b['x'];
        });

        // cross product vector OA dan OB
        $cross = function($o, $a, $b) {
            return ($a['x'] - $o['x']) * ($b['y'] - $o['y']) - ($a['y'] - $o['y']) * ($b['x'] - $o['x']);
        };

        // Lower Hull
        $lower = [];
        for ($i = 0; $i < $n; $i++) {
            while (count($lower) >= 2 && $cross($lower[count($lower) - 2], $lower[count($lower) - 1], $points[$i]) <= 0) {
                array_pop($lower);
            }
            $lower[] = $points[$i];
        }

        // Upper Hull
        $upper = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            while (count($upper) >= 2 && $cross($upper[count($upper) - 2], $upper[count($upper) - 1], $points[$i]) <= 0) {
                array_pop($upper);
            }
            $upper[] = $points[$i];
        }

        // Hapus duplikat titik ujung
        array_pop($lower);
        array_pop($upper);

        $hull = array_merge($lower, $upper);
        
        // Buat loop tertutup
        if (count($hull) > 0) {
            $hull[] = $hull[0];
        }

        return $hull;
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'centroids' => 'required|array|size:4',
            'centroids.*' => 'required|exists:mahasiswa,id_mahasiswa',
            'nama_riwayat' => 'required|string|max:255',
        ]);

        $features = ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'b3', 'b4', 'd1', 'd2', 'd3', 'd4'];
        
        // 1. Ambil data semua mahasiswa yang punya nilai
        $allMahasiswa = Mahasiswa::with('nilaiKuesioner')->whereHas('nilaiKuesioner')->get();
        
        if ($allMahasiswa->count() < 4) {
            return back()->with('error', 'Data mahasiswa minimal harus 4 untuk proses clustering.');
        }

        // 2. Pastikan Centroid Standar ada di tabel `centroid`
        $topics = [
            'Application Developer',
            'Data Analyst',
            'System Analyst',
            'IT Auditor & Governance'
        ];
        
        $dbCentroids = [];
        for ($i = 0; $i < 4; $i++) {
            $dbCentroids[$i] = Centroid::firstOrCreate(
                ['kode' => 'C' . ($i + 1)],
                ['topik' => $topics[$i]]
            );
        }

        // 3. Inisialisasi Centroid Awal dari pilihan user
        $centroids = [];
        foreach ($request->centroids as $index => $id) {
            $mhs = Mahasiswa::with('nilaiKuesioner')->find($id);
            foreach ($features as $feature) {
                $centroids[$index][$feature] = $mhs->nilaiKuesioner->$feature ?? 0;
            }
        }

        $history = [];
        $maxIterations = 10;
        $converged = false;

        for ($iter = 1; $iter <= $maxIterations; $iter++) {
            $clusters = [[], [], [], []];
            $iterResults = [];

            // Ambil info cluster dari iterasi sebelumnya jika ada
            $prevClusters = [];
            if ($iter > 1) {
                foreach ($history[$iter - 2]['results'] as $prevRes) {
                    $prevClusters[$prevRes['mahasiswa']->id_mahasiswa] = $prevRes['cluster'];
                }
            }

            // 4. Hitung Jarak Euclidean & Assign Cluster
            foreach ($allMahasiswa as $mhs) {
                $distances = [];
                foreach ($centroids as $cIndex => $centroid) {
                    $sum = 0;
                    foreach ($features as $feature) {
                        $val = $mhs->nilaiKuesioner->$feature ?? 0;
                        $sum += pow($val - $centroid[$feature], 2);
                    }
                    $distances[$cIndex] = sqrt($sum);
                }

                $minDistance = min($distances);
                $assignedCluster = array_search($minDistance, $distances);
                
                $clusters[$assignedCluster][] = $mhs;
                
                $currentCluster = $assignedCluster + 1;
                $moved = false;
                if ($iter > 1 && isset($prevClusters[$mhs->id_mahasiswa])) {
                    if ($prevClusters[$mhs->id_mahasiswa] !== $currentCluster) {
                        $moved = true;
                    }
                }
                
                $iterResults[] = [
                    'mahasiswa' => $mhs,
                    'distances' => $distances,
                    'min_distance' => $minDistance,
                    'cluster' => $currentCluster,
                    'moved' => $moved
                ];
            }

            // 📊 HITUNG PCA UNTUK ITERASI INI
            $pcaInput = [];
            foreach ($iterResults as $res) {
                $pcaInput[] = $res['distances'];
            }

            $pcaResult = $this->calculatePCA($pcaInput);
            $projection = $pcaResult['projection'];

            // Penyelarasan letak sumbu PC1 (Horizontal Anchor Flip)
            $sumPC1_C1 = 0.0;
            $countC1 = 0;
            foreach ($iterResults as $idx => $res) {
                if ($res['cluster'] === 1) {
                    $sumPC1_C1 += $projection[$idx]['PC1'];
                    $countC1++;
                }
            }
            
            $flipPC1 = 1.0;
            if ($countC1 > 0) {
                $meanPC1_C1 = $sumPC1_C1 / $countC1;
                if ($meanPC1_C1 < 0.0) $flipPC1 = -1.0;
            }

            foreach ($projection as &$p) {
                $p['PC1'] *= $flipPC1;
            }
            unset($p);

            foreach ($iterResults as $idx => &$res) {
                $res['PC1'] = $projection[$idx]['PC1'];
                $res['PC2'] = $projection[$idx]['PC2'];
                $res['mahasiswa']->PC1 = $projection[$idx]['PC1'];
                $res['mahasiswa']->PC2 = $projection[$idx]['PC2'];
            }
            unset($res);

            $centroidsPca = [];
            $clusterPoints = [[], [], [], []];

            foreach ($iterResults as $res) {
                $cIdx = $res['cluster'] - 1;
                $clusterPoints[$cIdx][] = [
                    'nama' => $res['mahasiswa']->nama,
                    'x' => $res['PC1'],
                    'y' => $res['PC2']
                ];
            }

            $hulls = [];
            foreach ($clusterPoints as $cIdx => $points) {
                if (count($points) > 0) {
                    $sumPC1 = 0.0;
                    $sumPC2 = 0.0;
                    foreach ($points as $pt) {
                        $sumPC1 += $pt['x'];
                        $sumPC2 += $pt['y'];
                    }
                    $centroidsPca[$cIdx] = [
                        'PC1' => $sumPC1 / count($points),
                        'PC2' => $sumPC2 / count($points)
                    ];
                } else {
                    $centroidsPca[$cIdx] = [
                        'PC1' => 0.0,
                        'PC2' => 0.0
                    ];
                }

                if (count($points) >= 3) {
                    $hulls[$cIdx] = $this->getConvexHull($points);
                } else {
                    $hulls[$cIdx] = [];
                }
            }

            $history[] = [
                'iterasi' => $iter,
                'centroids' => $centroids,
                'results' => $iterResults,
                'clusters' => $clusters,
                'centroids_pca' => $centroidsPca,
                'hulls' => $hulls,
                'explained_variance_ratio' => $pcaResult['explained_variance_ratio']
            ];

            // Hitung Centroid Baru
            $newCentroids = [];
            foreach ($clusters as $cIndex => $members) {
                if (count($members) > 0) {
                    foreach ($features as $feature) {
                        $sum = 0;
                        foreach ($members as $m) {
                            $sum += $m->nilaiKuesioner->$feature ?? 0;
                        }
                        $newCentroids[$cIndex][$feature] = $sum / count($members);
                    }
                } else {
                    $newCentroids[$cIndex] = $centroids[$cIndex];
                }
            }

            if ($centroids === $newCentroids) {
                $converged = true;
                break;
            }

            $centroids = $newCentroids;
        }

        // Simpan ke database
        $finalIteration = end($history);

        $riwayat = RiwayatClustering::create([
            'nama_riwayat' => $request->nama_riwayat,
            'tanggal' => Carbon::now(),
            'jumlah_mahasiswa' => $allMahasiswa->count(),
            'iterasi_total' => $finalIteration['iterasi'],
            'centroid_awal' => $request->centroids,
            'explained_variance_ratio' => $finalIteration['explained_variance_ratio']
        ]);

        foreach ($finalIteration['results'] as $res) {
            $mhs = $res['mahasiswa'];
            $cIdx = $res['cluster'] - 1;
            
            HasilClustering::create([
                'id_riwayat' => $riwayat->id_riwayat,
                'id_nilai' => $mhs->nilaiKuesioner->id_nilai,
                'id_centroid' => $dbCentroids[$cIdx]->id_centroid,
                'jarak_ke_c1' => $res['distances'][0],
                'jarak_ke_c2' => $res['distances'][1],
                'jarak_ke_c3' => $res['distances'][2],
                'jarak_ke_c4' => $res['distances'][3],
                'jarak_minimum' => $res['min_distance'],
                'pc1' => $res['PC1'],
                'pc2' => $res['PC2'],
                'iterasi' => $finalIteration['iterasi'],
            ]);
        }

        return redirect()->route('kmeans.riwayat.index')->with('success', 'Hasil perhitungan K-Means berhasil disimpan ke riwayat.');
    }

    public function riwayatIndex()
    {
        $riwayat = RiwayatClustering::orderBy('tanggal', 'desc')->get();
        return view('kmeans.riwayat_index', compact('riwayat'));
    }

    public function riwayatShow($id)
    {
        $riwayat = RiwayatClustering::with(['hasilClustering.nilaiKuesioner.mahasiswa', 'hasilClustering.centroid'])->findOrFail($id);
        
        $topics = [
            'Application Developer',
            'Data Analyst',
            'System Analyst',
            'IT Auditor & Governance'
        ];

        $results = [];
        $clusters = [[], [], [], []];
        $centroidsPca = [[], [], [], []];

        foreach ($riwayat->hasilClustering as $hc) {
            $mhs = $hc->nilaiKuesioner->mahasiswa;
            $mhs->PC1 = $hc->pc1;
            $mhs->PC2 = $hc->pc2;

            $cIdx = $hc->centroid->kode == 'C1' ? 0 : ($hc->centroid->kode == 'C2' ? 1 : ($hc->centroid->kode == 'C3' ? 2 : 3));
            
            $results[] = [
                'mahasiswa' => $mhs,
                'distances' => [
                    $hc->jarak_ke_c1,
                    $hc->jarak_ke_c2,
                    $hc->jarak_ke_c3,
                    $hc->jarak_ke_c4,
                ],
                'min_distance' => $hc->jarak_minimum,
                'cluster' => $cIdx + 1,
                'moved' => false,
                'PC1' => $hc->pc1,
                'PC2' => $hc->pc2,
            ];

            $clusters[$cIdx][] = $mhs;
        }

        $hulls = [];
        for ($c = 0; $c < 4; $c++) {
            $points = [];
            $sumPC1 = 0;
            $sumPC2 = 0;
            foreach ($clusters[$c] as $mhs) {
                $points[] = [
                    'nama' => $mhs->nama,
                    'x' => $mhs->PC1,
                    'y' => $mhs->PC2
                ];
                $sumPC1 += $mhs->PC1;
                $sumPC2 += $mhs->PC2;
            }

            if (count($clusters[$c]) > 0) {
                $centroidsPca[$c] = [
                    'PC1' => $sumPC1 / count($clusters[$c]),
                    'PC2' => $sumPC2 / count($clusters[$c])
                ];
            } else {
                $centroidsPca[$c] = [
                    'PC1' => 0,
                    'PC2' => 0
                ];
            }

            if (count($points) >= 3) {
                $hulls[$c] = $this->getConvexHull($points);
            } else {
                $hulls[$c] = [];
            }
        }

        $selectedCentroidsNames = [];
        foreach ($riwayat->centroid_awal as $cId) {
            $mhsCentroid = Mahasiswa::find($cId);
            $selectedCentroidsNames[] = $mhsCentroid ? $mhsCentroid->nama : 'N/A';
        }

        return view('kmeans.riwayat_show', compact('riwayat', 'results', 'clusters', 'centroidsPca', 'hulls', 'topics', 'selectedCentroidsNames'));
    }

    public function riwayatExport($id)
    {
        $riwayat = RiwayatClustering::with(['hasilClustering.nilaiKuesioner.mahasiswa', 'hasilClustering.centroid'])->findOrFail($id);

        $topics = [
            'Application Developer',
            'Data Analyst',
            'System Analyst',
            'IT Auditor & Governance'
        ];

        $clustersData = [[], [], [], []];
        $centroidsPca = [];

        foreach ($riwayat->hasilClustering as $hc) {
            $mhs = $hc->nilaiKuesioner->mahasiswa;
            $cIdx = $hc->centroid->kode == 'C1' ? 0 : ($hc->centroid->kode == 'C2' ? 1 : ($hc->centroid->kode == 'C3' ? 2 : 3));
            
            $clustersData[$cIdx][] = [
                'nama' => $mhs->nama,
                'PC1' => $hc->pc1,
                'PC2' => $hc->pc2
            ];
        }

        for ($c = 0; $c < 4; $c++) {
            $sumPC1 = 0;
            $sumPC2 = 0;
            $count = count($clustersData[$c]);
            foreach ($clustersData[$c] as $item) {
                $sumPC1 += $item['PC1'];
                $sumPC2 += $item['PC2'];
            }
            $centroidsPca[$c] = [
                'PC1' => $count > 0 ? $sumPC1 / $count : 0.0,
                'PC2' => $count > 0 ? $sumPC2 / $count : 0.0
            ];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        
        for ($cIdx = 0; $cIdx < 4; $cIdx++) {
            $members = $clustersData[$cIdx] ?? [];
            $centroidRow = $centroidsPca[$cIdx];
            $topicName = $topics[$cIdx];
            
            $ws = $spreadsheet->createSheet();
            $sheetTitle = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $topicName), 0, 30);
            $ws->setTitle($sheetTitle ?: "Cluster " . ($cIdx + 1));
            
            $ws->setCellValue('A1', "Cluster " . ($cIdx + 1) . " (" . $topicName . ")");
            $ws->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            
            $ws->setCellValue('A2', "Jumlah Mahasiswa");
            $ws->setCellValue('B2', count($members));
            
            $ws->setCellValue('A3', "Centroid PC1");
            $ws->setCellValue('B3', round($centroidRow['PC1'], 3));
            
            $ws->setCellValue('A4', "Centroid PC2");
            $ws->setCellValue('B4', round($centroidRow['PC2'], 3));
            
            $ws->setCellValue('A6', "Nama");
            $ws->setCellValue('B6', "PC1");
            $ws->setCellValue('C6', "PC2");
            $ws->getStyle('A6:C6')->getFont()->setBold(true);
            
            $rowNum = 7;
            foreach ($members as $m) {
                $ws->setCellValue('A' . $rowNum, $m['nama']);
                $ws->setCellValue('B' . $rowNum, round($m['PC1'], 3));
                $ws->setCellValue('C' . $rowNum, round($m['PC2'], 3));
                $rowNum++;
            }
            
            foreach (range('A', 'C') as $col) {
                $ws->getColumnDimension($col)->setAutoSize(true);
            }
        }
        
        $fileName = "hasil_cluster_riwayat_" . Str::slug($riwayat->nama_riwayat) . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function riwayatDestroy($id)
    {
        $riwayat = RiwayatClustering::findOrFail($id);
        $riwayat->delete();

        return redirect()->route('kmeans.riwayat.index')->with('success', 'Riwayat hasil cluster berhasil dihapus.');
    }
}
