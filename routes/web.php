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
});
