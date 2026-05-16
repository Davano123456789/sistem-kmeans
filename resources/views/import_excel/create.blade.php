@extends('layouts.master')

@section('title', 'Upload Excel Baru')
@section('breadcrumb', 'Upload Excel')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Form Import Data</h6>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('import-excel.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4 mt-3">
                        <label class="form-label font-weight-bold">Pilih File Excel (.xlsx / .xls)</label>
                        <div class="input-group input-group-outline">
                            <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="mt-2 text-xs text-secondary">
                            <i class="material-icons text-xs">info</i> Header wajib: <b>nim, nama, prodi, angkatan, c1, c2, c3, c4, c5, c6, c7, c8</b>
                        </div>
                        @error('file')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('import-excel.index') }}" class="btn btn-outline-dark">Kembali</a>
                        <button type="submit" class="btn btn-dark">
                            <i class="material-icons text-sm me-1">upload</i> Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
