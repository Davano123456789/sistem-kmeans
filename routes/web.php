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

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });

    Route::resource('pengguna', PenggunaController::class);
    Route::resource('mahasiswa', MahasiswaController::class);
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
