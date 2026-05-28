<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileExcel;
use App\Models\User;
use App\Imports\MahasiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelController extends Controller
{
    public function index()
    {
        $files = FileExcel::latest()->get();
        return view('import_excel.index', compact('files'));
    }

    public function create()
    {
        return view('import_excel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $file = $request->file('file');
        
        // Save File Info
        $user = User::first(); // Placeholder
        $file_entry = FileExcel::create([
            'nama' => $file->getClientOriginalName(),
            'tanggal_upload' => now(),
            'id_pengguna' => $user->id_pengguna ?? 1,
        ]);

        $import = new MahasiswaImport($file_entry->id_file);
        Excel::import($import, $file);
        $report = $import->getReport();
        
        // Tambahkan nama file ke laporan
        $report['file_name'] = $file_entry->nama;

        // Simpan laporan ke database di riwayat file tersebut
        $file_entry->update([
            'laporan_cleaning' => $report
        ]);

        // Simpan laporan ke persistent session agar tidak hilang saat di-refresh
        session(['cleaning_report' => $report]);

        return redirect()->route('import-excel.index')
            ->with('success', 'Data berhasil diimport dan dibersihkan.');
    }

    public function showReport($id)
    {
        $file = FileExcel::findOrFail($id);
        
        if ($file->laporan_cleaning) {
            $report = $file->laporan_cleaning;
            // Inject nama file secara dinamis untuk kompatibilitas riwayat lama
            if (!isset($report['file_name'])) {
                $report['file_name'] = $file->nama;
            }
            
            session(['cleaning_report' => $report]);
            return redirect()->route('import-excel.index')->with('success', 'Berhasil memuat laporan pembersihan data untuk file: ' . $file->nama);
        }

        return redirect()->route('import-excel.index')->with('error', 'Laporan pembersihan data tidak ditemukan untuk file ini.');
    }

    public function clearReport()
    {
        session()->forget('cleaning_report');
        return redirect()->route('import-excel.index');
    }

    public function destroy($id)
    {
        $file = FileExcel::findOrFail($id);
        
        // Ambil ID mahasiswa yang terhubung dengan file ini
        $mahasiswaIds = \App\Models\NilaiKuesioner::where('id_file', $id)->pluck('id_mahasiswa');
        
        // Hapus mahasiswa tersebut (otomatis nilai_kuesioner terhapus karena cascade)
        \App\Models\Mahasiswa::whereIn('id_mahasiswa', $mahasiswaIds)->delete();
        
        // Hapus record filenya
        $file->delete();

        // Hapus laporan dari session ketika file dihapus
        session()->forget('cleaning_report');

        return redirect()->route('import-excel.index')->with('success', 'Riwayat file dan semua data mahasiswa terkait berhasil dihapus total.');
    }
}
