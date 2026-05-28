<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\NilaiKuesioner;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class MahasiswaImport implements ToCollection
{
    protected $id_file;
    protected $report = [];

    public function __construct($id_file)
    {
        $this->id_file = $id_file;
    }

    /**
     * Ambil hasil laporan pembersihan data (Data Cleaning)
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Baca data dalam bentuk Collection untuk diproses secara fleksibel
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->report = [
                'original_count' => 0,
                'valid_count' => 0,
                'deleted_count' => 0,
                'breakdown' => [
                    'nama_invalid' => 0,
                    'npm_invalid' => 0,
                    'ipk_invalid' => 0,
                    'rps_invalid' => 0,
                ],
                'details' => []
            ];
            return;
        }

        // 1. Deteksi Kolom Secara Dinamis (Flexible Column Mapping / Renaming)
        $header = $rows->first()->toArray();
        
        $namaIdx = -1;
        $npmIdx = -1;
        $ipkIdx = -1;
        $semesterIdx = -1;
        $rpsIdx = -1;

        // Default indices untuk kuesioner (A1-A4, B1-B4, D1-D4) sesuai format standar
        $a1Idx = 5; $a2Idx = 6; $a3Idx = 7; $a4Idx = 8;
        $b1Idx = 9; $b2Idx = 10; $b3Idx = 11; $b4Idx = 12;
        $d1Idx = 16; $d2Idx = 14; $d3Idx = 15; $d4Idx = 17;

        foreach ($header as $idx => $col) {
            if ($col === null || $col === '') continue;
            $colLower = strtolower(trim((string)$col));

            if (strpos($colLower, 'nama lengkap') !== false || $colLower === 'nama') {
                $namaIdx = $idx;
            } elseif (strpos($colLower, 'npm') !== false) {
                $npmIdx = $idx;
            } elseif (strpos($colLower, 'ipk') !== false) {
                $ipkIdx = $idx;
            } elseif (strpos($colLower, 'semester') !== false) {
                $semesterIdx = $idx;
            } elseif (strpos($colLower, 'rancangan penelitian skripsi') !== false || strpos($colLower, 'rps') !== false) {
                $rpsIdx = $idx;
            }

            // Pemetaan kuesioner dinamis berdasarkan kata kunci/deskripsi lengkap atau nama langsung (a1, a2, dll.)
            if ($colLower === 'a1' || (strpos($colLower, 'berminat') !== false && strpos($colLower, 'pemrograman') !== false)) {
                $a1Idx = $idx;
            }
            if ($colLower === 'a2' || (strpos($colLower, 'berminat') !== false && strpos($colLower, 'analisis data') !== false)) {
                $a2Idx = $idx;
            }
            if ($colLower === 'a3' || (strpos($colLower, 'berminat') !== false && (strpos($colLower, 'analisis sistem') !== false || strpos($colLower, 'analisis system') !== false))) {
                $a3Idx = $idx;
            }
            if ($colLower === 'a4' || (strpos($colLower, 'berminat') !== false && strpos($colLower, 'manajemen it') !== false)) {
                $a4Idx = $idx;
            }
            
            if ($colLower === 'b1' || (strpos($colLower, 'keterampilan') !== false && strpos($colLower, 'pemrograman') !== false)) {
                $b1Idx = $idx;
            }
            if ($colLower === 'b2' || (strpos($colLower, 'keterampilan') !== false && strpos($colLower, 'manajemen it') !== false)) {
                $b2Idx = $idx;
            }
            if ($colLower === 'b3' || (strpos($colLower, 'keterampilan') !== false && strpos($colLower, 'analisis data') !== false)) {
                $b3Idx = $idx;
            }
            if ($colLower === 'b4' || (strpos($colLower, 'keterampilan') !== false && (strpos($colLower, 'analisis sistem') !== false || strpos($colLower, 'analisis system') !== false))) {
                $b4Idx = $idx;
            }
            
            if ($colLower === 'd1' || (strpos($colLower, 'nilai') !== false && (strpos($colLower, 'application developer') !== false || strpos($colLower, 'pemrograman') !== false || strpos($colLower, 'developer') !== false))) {
                $d1Idx = $idx;
            }
            if ($colLower === 'd2' || (strpos($colLower, 'nilai') !== false && (strpos($colLower, 'data analyst') !== false || strpos($colLower, 'analis data') !== false))) {
                $d2Idx = $idx;
            }
            if ($colLower === 'd3' || (strpos($colLower, 'nilai') !== false && (strpos($colLower, 'system analyst') !== false || strpos($colLower, 'analis sistem') !== false || strpos($colLower, 'analis system') !== false))) {
                $d3Idx = $idx;
            }
            if ($colLower === 'd4' || (strpos($colLower, 'nilai') !== false && (strpos($colLower, 'it auditor') !== false || strpos($colLower, 'manajemen it') !== false || strpos($colLower, 'tata kelola') !== false))) {
                $d4Idx = $idx;
            }
        }

        // Fallback jika ada kolom wajib yang tidak terdeteksi nama fiturnya
        if ($namaIdx === -1) $namaIdx = 1;      // Default kolom B
        if ($npmIdx === -1) $npmIdx = 2;        // Default kolom C
        if ($ipkIdx === -1) $ipkIdx = 3;        // Default kolom D
        if ($semesterIdx === -1) $semesterIdx = 4; // Default kolom E
        if ($rpsIdx === -1) $rpsIdx = 18;       // Default kolom S (jika tidak ditemukan)

        $originalCount = 0;
        $validRecords = [];
        
        $logDeleted = [
            'nama_invalid' => [],
            'ipk_invalid' => [],
            'npm_invalid' => [],
            'rps_invalid' => [],
        ];
        
        $seenNPMs = [];
        $seenNames = [];

        // 2. Loop Data Rows (Skip baris pertama karena itu header)
        $rowCount = count($rows);
        for ($r = 1; $r < $rowCount; $r++) {
            $row = $rows[$r]->toArray();
            
            // Lewati baris kosong total
            if (empty(array_filter($row, function($v) { return $v !== null && $v !== ''; }))) {
                continue;
            }
            
            $originalCount++;
            
            $nama = isset($row[$namaIdx]) ? trim((string)$row[$namaIdx]) : '';
            $npm = isset($row[$npmIdx]) ? trim((string)$row[$npmIdx]) : '';
            $ipkRaw = isset($row[$ipkIdx]) ? $row[$ipkIdx] : null;
            $semesterRaw = isset($row[$semesterIdx]) ? $row[$semesterIdx] : null;
            $rpsVal = isset($row[$rpsIdx]) ? $row[$rpsIdx] : null;

            // =====================================================
            // ❌ CLEANING NAMA MAHASISWA
            // =====================================================
            $namaClean = strtolower(trim($nama));
            if ($namaClean === '' || $namaClean === 'nan') {
                $logDeleted['nama_invalid'][] = [
                    'nama' => $nama ?: '[KOSONG]',
                    'npm' => $npm ?: '-',
                    'reason' => 'Nama tidak boleh kosong / tidak valid'
                ];
                continue;
            }
            if (in_array($namaClean, $seenNames)) {
                $logDeleted['nama_invalid'][] = [
                    'nama' => $nama,
                    'npm' => $npm,
                    'reason' => 'Nama duplikat / ganda'
                ];
                continue;
            }
            
            // =====================================================
            // ❌ CLEANING NPM MAHASISWA (Wajib diawali '13.')
            // =====================================================
            $npmClean = trim($npm);
            if ($npmClean === '' || $npmClean === 'nan') {
                $logDeleted['npm_invalid'][] = [
                    'nama' => $nama,
                    'npm' => '[KOSONG]',
                    'reason' => 'NPM tidak boleh kosong'
                ];
                continue;
            }
            if (strpos($npmClean, '13.') !== 0) {
                $logDeleted['npm_invalid'][] = [
                    'nama' => $nama,
                    'npm' => $npmClean,
                    'reason' => 'NPM tidak diawali dengan "13."'
                ];
                continue;
            }
            if (in_array($npmClean, $seenNPMs)) {
                $logDeleted['npm_invalid'][] = [
                    'nama' => $nama,
                    'npm' => $npmClean,
                    'reason' => 'NPM duplikat / ganda'
                ];
                continue;
            }

            // =====================================================
            // ❌ CLEANING IPK MAHASISWA (Harus di antara 0.0 - 4.0)
            // =====================================================
            $ipk = null;
            if ($ipkRaw !== null && $ipkRaw !== '') {
                // Ubah koma ke titik
                $ipkStr = str_replace(',', '.', (string)$ipkRaw);
                // Bersihkan karakter non-angka dan non-titik
                $ipkStr = preg_replace('/[^0-9.]/', '', $ipkStr);
                if (is_numeric($ipkStr)) {
                    $val = floatval($ipkStr);
                    if ($val >= 0.0 && $val <= 4.0) {
                        $ipk = $val;
                    }
                }
            }
            if ($ipk === null) {
                $logDeleted['ipk_invalid'][] = [
                    'nama' => $nama,
                    'npm' => $npmClean,
                    'reason' => 'IPK tidak valid (di luar rentang 0.00 - 4.00)'
                ];
                continue;
            }

            // =====================================================
            // ❌ NORMALISASI SEMESTER MAHASISWA
            // =====================================================
            $semester = null;
            if ($semesterRaw !== null && $semesterRaw !== '') {
                $semStr = strtolower(trim((string)$semesterRaw));
                
                $kamus = [
                    'satu' => 1, 'dua' => 2, 'tiga' => 3, 'empat' => 4,
                    'lima' => 5, 'enam' => 6, 'tujuh' => 7, 'delapan' => 8,
                    'sembilan' => 9, 'sepuluh' => 10,
                    'sebelas' => 11, 'dua belas' => 12
                ];
                
                foreach ($kamus as $k => $v) {
                    if (strpos($semStr, $k) !== false) {
                        $semester = $v;
                        break;
                    }
                }
                
                if ($semester === null) {
                    preg_match('/\d+/', $semStr, $matches);
                    if (isset($matches[0])) {
                        $semester = intval($matches[0]);
                    }
                }
            }
            if ($semester === null) {
                $semester = 0; // Fallback jika tidak valid
            }

            // =====================================================
            // ❌ FILTER RPS (Wajib mengambil proposal/penelitian)
            // =====================================================
            $rpsValid = false;
            if ($rpsVal !== null && $rpsVal !== '') {
                $rpsLower = strtolower(trim((string)$rpsVal));
                $keywords = ['ya', 'sudah', 'sedang'];
                foreach ($keywords as $kw) {
                    if (strpos($rpsLower, $kw) !== false) {
                        $rpsValid = true;
                        break;
                    }
                }
            }
            if (!$rpsValid) {
                $logDeleted['rps_invalid'][] = [
                    'nama' => $nama,
                    'npm' => $npmClean,
                    'reason' => 'Belum mengambil/menempuh mata kuliah proposal skripsi (RPS)'
                ];
                continue;
            }

            // Tandai sudah diproses untuk penanganan duplikasi
            $seenNames[] = $namaClean;
            $seenNPMs[] = $npmClean;

            // Masukkan ke array data valid
            $validRecords[] = [
                'nama' => $nama,
                'npm' => $npmClean,
                'ipk' => $ipk,
                'semester' => $semester,
                // Nilai Kuesioner (A1-D4)
                'a1' => intval($row[$a1Idx] ?? 0),
                'a2' => intval($row[$a2Idx] ?? 0),
                'a3' => intval($row[$a3Idx] ?? 0),
                'a4' => intval($row[$a4Idx] ?? 0),
                'b1' => intval($row[$b1Idx] ?? 0),
                'b2' => intval($row[$b2Idx] ?? 0),
                'b3' => intval($row[$b3Idx] ?? 0),
                'b4' => intval($row[$b4Idx] ?? 0),
                'd1' => intval($row[$d1Idx] ?? 0),
                'd2' => intval($row[$d2Idx] ?? 0),
                'd3' => intval($row[$d3Idx] ?? 0),
                'd4' => intval($row[$d4Idx] ?? 0),
            ];
        }

        // 3. Masukkan Seluruh Data Valid Ke Database (Create or Update)
        foreach ($validRecords as $rec) {
            $mahasiswa = Mahasiswa::updateOrCreate(
                ['npm' => $rec['npm']],
                [
                    'nama' => $rec['nama'],
                    'ipk' => $rec['ipk'],
                    'semester' => $rec['semester'],
                ]
            );

            NilaiKuesioner::updateOrCreate(
                ['id_mahasiswa' => $mahasiswa->id_mahasiswa],
                [
                    'id_file' => $this->id_file,
                    'a1' => $rec['a1'],
                    'a2' => $rec['a2'],
                    'a3' => $rec['a3'],
                    'a4' => $rec['a4'],
                    'b1' => $rec['b1'],
                    'b2' => $rec['b2'],
                    'b3' => $rec['b3'],
                    'b4' => $rec['b4'],
                    'd1' => $rec['d1'],
                    'd2' => $rec['d2'],
                    'd3' => $rec['d3'],
                    'd4' => $rec['d4'],
                ]
            );
        }

        // 4. Catat Laporan Pembersihan Data untuk Dikirim ke View
        $this->report = [
            'original_count' => $originalCount,
            'valid_count' => count($validRecords),
            'deleted_count' => $originalCount - count($validRecords),
            'breakdown' => [
                'nama_invalid' => count($logDeleted['nama_invalid']),
                'npm_invalid' => count($logDeleted['npm_invalid']),
                'ipk_invalid' => count($logDeleted['ipk_invalid']),
                'rps_invalid' => count($logDeleted['rps_invalid']),
            ],
            'details' => array_merge(
                $logDeleted['nama_invalid'],
                $logDeleted['npm_invalid'],
                $logDeleted['ipk_invalid'],
                $logDeleted['rps_invalid']
            )
        ];
    }
}
