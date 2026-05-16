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

        Excel::import(new MahasiswaImport($file_entry->id_file), $file);

        return redirect()->route('import-excel.index')->with('success', 'Data berhasil diimport.');
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

        return redirect()->route('import-excel.index')->with('success', 'Riwayat file dan semua data mahasiswa terkait berhasil dihapus total.');
    }
}
