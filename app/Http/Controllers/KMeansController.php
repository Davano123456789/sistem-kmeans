<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;

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
                
                $iterResults[] = [
                    'mahasiswa' => $mhs,
                    'distances' => $distances,
                    'min_distance' => $minDistance,
                    'cluster' => $assignedCluster + 1
                ];
            }

            $history[] = [
                'iterasi' => $iter,
                'centroids' => $centroids,
                'results' => $iterResults,
                'clusters' => $clusters // Simpan anggota kelompok di sini
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

        return view('kmeans.index', [
            'mahasiswa' => $allMahasiswa,
            'history' => $history,
            'converged' => $converged,
            'selectedCentroids' => $request->centroids
        ]);
    }
}
