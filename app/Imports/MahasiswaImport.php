<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\NilaiKuesioner;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MahasiswaImport implements ToModel, WithStartRow, SkipsEmptyRows
{
    protected $id_file;

    public function __construct($id_file)
    {
        $this->id_file = $id_file;
    }

    /**
     * Kita mulai baca dari baris ke-2 (melewati header)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        /**
         * Mapping Berdasarkan Indeks Kolom (0-based)
         * B (1): nama
         * C (2): npm
         * D (3): ipk
         * E (4): semester
         * F (5): A1
         * G (6): A2
         * H (7): A3
         * I (8): A4
         * J (9): B1
         * K (10): B2
         * L (11): B3
         * M (12): B4
         * O (14): D1  <-- Kolom N (13) dilewati (SKS)
         * P (15): D2
         * Q (16): D3
         * R (17): D4
         */

        if (empty($row[2])) return null; // Skip jika NPM kosong

        // 1. Create or Update Mahasiswa
        $mahasiswa = Mahasiswa::updateOrCreate(
            ['npm' => $row[2]],
            [
                'nama' => $row[1],
                'ipk' => $row[3] ?? 0,
                'semester' => $row[4] ?? 0,
            ]
        );

        // 2. Create or Update Nilai Kuesioner
        NilaiKuesioner::updateOrCreate(
            ['id_mahasiswa' => $mahasiswa->id_mahasiswa],
            [
                'id_file' => $this->id_file,
                // A - Minat
                'a1' => $row[5] ?? 0,
                'a2' => $row[6] ?? 0,
                'a3' => $row[7] ?? 0,
                'a4' => $row[8] ?? 0,
                
                // B - Keterampilan
                'b1' => $row[9] ?? 0,
                'b2' => $row[10] ?? 0,
                'b3' => $row[11] ?? 0,
                'b4' => $row[12] ?? 0,

                // D - Nilai Matkul (Indeks 14, 15, 16, 17)
                'd1' => $row[14] ?? 0,
                'd2' => $row[15] ?? 0,
                'd3' => $row[16] ?? 0,
                'd4' => $row[17] ?? 0,
            ]
        );

        return $mahasiswa;
    }
}
