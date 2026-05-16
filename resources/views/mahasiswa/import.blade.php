@extends('layouts.master')

@section('title', 'Import Excel')
@section('breadcrumb', 'Import Excel')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Form Upload Data Mahasiswa</h6>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="alert alert-info text-white border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <i class="material-icons me-2">info</i>
                        <span class="text-sm">Silakan pilih file Excel (.xlsx atau .xls) yang berisi data mahasiswa dan nilai kuesioner.</span>
                    </div>
                </div>

                <form action="{{ route('mahasiswa.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">File Excel Mahasiswa</label>
                        <div class="input-group input-group-outline">
                            <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <small class="text-secondary">Header yang dibutuhkan: nim, nama, prodi, angkatan, c1, c2, c3, c4, c5, c6, c7, c8</small>
                        @error('file')
                            <div class="mt-2">
                                <small class="text-danger">{{ $message }}</small>
                            </div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="material-icons text-sm me-1">upload</i> Proses Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="font-weight-bold">Petunjuk:</h6>
                <p class="text-sm text-secondary">Setelah proses import selesai, Anda dapat melihat hasilnya pada menu <b>Data Mahasiswa</b>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
