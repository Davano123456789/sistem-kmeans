@extends('layouts.master')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="row">
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-header p-3 pt-2">
        <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
          <i class="material-icons-round opacity-10">school</i>
        </div>
        <div class="text-end pt-1">
          <p class="text-sm mb-0 text-capitalize">Total Mahasiswa</p>
          <h4 class="mb-0">{{ $totalMahasiswa }}</h4>
        </div>
      </div>
      <hr class="dark horizontal my-0">
      <div class="card-footer p-3">
        <p class="mb-0"><span class="text-dark text-sm font-weight-bolder">Data terbaru </span>yang diimport</p>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-header p-3 pt-2">
        <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
          <i class="material-icons-round opacity-10">people</i>
        </div>
        <div class="text-end pt-1">
          <p class="text-sm mb-0 text-capitalize">Total User</p>
          <h4 class="mb-0">{{ $totalUser }}</h4>
        </div>
      </div>
      <hr class="dark horizontal my-0">
      <div class="card-footer p-3">
        <p class="mb-0"><span class="text-dark text-sm font-weight-bolder">Koordinator </span>aktif</p>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card">
      <div class="card-header p-3 pt-2">
        <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
          <i class="material-icons-round opacity-10">assignment</i>
        </div>
        <div class="text-end pt-1">
          <p class="text-sm mb-0 text-capitalize">Riwayat Cluster</p>
          <h4 class="mb-0">{{ $totalRiwayat }}</h4>
        </div>
      </div>
      <hr class="dark horizontal my-0">
      <div class="card-footer p-3">
        <p class="mb-0"><span class="text-dark text-sm font-weight-bolder">Sesi K-Means </span>tersimpan</p>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="card">
      <div class="card-header p-3 pt-2">
        <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
          <i class="material-icons-round opacity-10">upload_file</i>
        </div>
        <div class="text-end pt-1">
          <p class="text-sm mb-0 text-capitalize">File Excel</p>
          <h4 class="mb-0">{{ $totalFile }}</h4>
        </div>
      </div>
      <hr class="dark horizontal my-0">
      <div class="card-footer p-3">
        <p class="mb-0"><span class="text-dark text-sm font-weight-bolder">Google Form </span>Result</p>
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-12">
    <div class="card my-4">
      <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
          <h6 class="text-white text-capitalize ps-3">Selamat Datang</h6>
        </div>
      </div>
      <div class="card-body px-0 pb-2">
        <div class="p-4">
            <h5>Halo, {{ auth()->user()->nama }}!</h5>
            <p>Selamat datang di Sistem K-Means untuk Penentuan Topik Skripsi. Anda masuk sebagai <strong>{{ ucwords(auth()->user()->role) }}</strong>. Silakan gunakan menu di samping untuk mulai mengelola data dan melihat hasil clustering.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
