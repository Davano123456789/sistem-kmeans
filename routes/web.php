<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\KMeansController;
use App\Http\Controllers\AuthController;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'authenticate']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'check.role'])->group(function () {
    Route::get('/', function () {
        $totalMahasiswa = \App\Models\Mahasiswa::count();
        $totalUser = \App\Models\User::count();
        $totalRiwayat = \App\Models\RiwayatClustering::count();
        $totalFile = \App\Models\FileExcel::count();
        return view('dashboard', compact('totalMahasiswa', 'totalUser', 'totalRiwayat', 'totalFile'));
    });

    Route::resource('pengguna', PenggunaController::class);
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::get('import-excel/clear-report', [ImportExcelController::class, 'clearReport'])->name('import-excel.clear-report');
    Route::get('import-excel/{id}/report', [ImportExcelController::class, 'showReport'])->name('import-excel.show-report');
    Route::resource('import-excel', ImportExcelController::class);
    Route::get('kmeans', [KMeansController::class, 'index'])->name('kmeans.index');
    Route::post('kmeans/hitung', [KMeansController::class, 'hitung'])->name('kmeans.hitung');
    Route::get('kmeans/hitung', function () {
        return redirect()->route('kmeans.index')->with('warning', 'Silakan tentukan parameter clustering terlebih dahulu.');
    });
    Route::get('kmeans/export', [KMeansController::class, 'export'])->name('kmeans.export');
    Route::post('kmeans/simpan', [KMeansController::class, 'simpan'])->name('kmeans.simpan');
    Route::get('hasil-cluster', [KMeansController::class, 'riwayatIndex'])->name('kmeans.riwayat.index');
    Route::get('hasil-cluster/{id}', [KMeansController::class, 'riwayatShow'])->name('kmeans.riwayat.show');
    Route::get('hasil-cluster/{id}/export', [KMeansController::class, 'riwayatExport'])->name('kmeans.riwayat.export');
    Route::delete('hasil-cluster/{id}', [KMeansController::class, 'riwayatDestroy'])->name('kmeans.riwayat.destroy');
});
