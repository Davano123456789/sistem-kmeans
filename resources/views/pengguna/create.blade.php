@extends('layouts.master')

@section('title', 'Tambah User')
@section('breadcrumb', 'Tambah User')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Form Tambah Pengguna</h6>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('pengguna.store') }}" method="POST">
                    @csrf
                    <div class="row mt-4">
                        <div class="col-md-6 mb-4">
                            <div class="input-group input-group-outline @error('nama') is-invalid is-filled @enderror">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}">
                            </div>
                            @error('nama')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="input-group input-group-outline @error('email') is-invalid is-filled @enderror">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="input-group input-group-outline @error('password') is-invalid is-filled @enderror">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="input-group input-group-static @error('role') is-invalid @enderror">
                                <label for="role" class="ms-0">Role Pengguna</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="">Pilih Role</option>
                                    <option value="koordinator" {{ old('role') == 'koordinator' ? 'selected' : '' }}>Koordinator Skripsi</option>
                                    <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen Pembimbing</option>
                                </select>
                            </div>
                            @error('role')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark">Simpan User</button>
                        <a href="{{ route('pengguna.index') }}" class="btn btn-outline-dark ms-2">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
