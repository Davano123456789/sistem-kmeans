@extends('layouts.master')

@section('title', 'Riwayat Import Excel')
@section('breadcrumb', 'Riwayat Import')

@section('content')
@if(session('cleaning_report'))
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff; box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05) !important;">
            <div class="card-header border-0 pb-0 pt-4 px-4 bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="bg-gradient-success shadow-success text-center border-radius-xl mt-n4 me-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: linear-gradient(195deg, #66bb6a, #43a047); border-radius: 0.75rem;">
                        <i class="material-icons opacity-10 text-white" style="font-size: 32px; line-height: 1;">cleaning_services</i>
                    </div>
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                            Laporan Hasil Pembersihan Data (Data Cleaning)
                            @if(isset(session('cleaning_report')['file_name']))
                                <span class="text-secondary text-sm font-weight-normal" style="font-size: 0.85rem;"> - File: <strong class="text-success">{{ session('cleaning_report')['file_name'] }}</strong></span>
                            @endif
                        </h5>
                        <p class="text-xs text-secondary mb-0">Proses penyaringan, pembatasan institusi NPM, validasi IPK, dan kelayakan mata kuliah proposal (RPS)</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-xs font-weight-bold px-3 py-2 border-radius-lg" style="background-color: rgba(76, 175, 80, 0.12); color: #2e7d32; border: 1px solid rgba(76, 175, 80, 0.2);">check_circle CLEANING SELESAI</span>
                    <a href="{{ route('import-excel.clear-report') }}" class="btn-close-custom text-secondary p-0 mb-0 d-flex align-items-center justify-content-center" title="Tutup Laporan Pembersihan Data" style="cursor: pointer; width: 32px; height: 32px; border-radius: 50%; background: #f0f2f5; transition: all 0.2s ease; border: none; text-decoration: none;" onmouseover="this.style.backgroundColor='#e4e6eb'; this.style.color='#000';" onmouseout="this.style.backgroundColor='#f0f2f5'; this.style.color='#7b809a';">
                        <i class="material-icons" style="font-size: 16px;">close</i>
                    </a>
                </div>
            </div>
            <div class="card-body px-4 py-3">
                <!-- Stat Cards -->
                <div class="row g-3 mb-4 mt-2">
                    <div class="col-md-4">
                        <div class="p-3 border border-radius-lg bg-light d-flex align-items-center justify-content-between" style="border-radius: 10px; background-color: #f8f9fa;">
                            <div>
                                <span class="text-xs font-weight-bold text-uppercase text-secondary" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Baris Data Mentah</span>
                                <h4 class="mb-0 font-weight-bolder text-dark">{{ session('cleaning_report')['original_count'] }} <span class="text-xs font-weight-normal text-secondary">mhs</span></h4>
                            </div>
                            <span class="material-icons text-secondary" style="font-size: 36px; opacity: 0.5;">grid_on</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-radius-lg bg-light d-flex align-items-center justify-content-between" style="border-radius: 10px; background-color: #f8f9fa; border-left: 5px solid #4caf50 !important;">
                            <div>
                                <span class="text-xs font-weight-bold text-uppercase text-success" style="font-size: 0.65rem; letter-spacing: 0.5px; color: #2e7d32 !important;">Total Data Lolos (Valid)</span>
                                <h4 class="mb-0 font-weight-bolder text-success" style="color: #2e7d32 !important;">{{ session('cleaning_report')['valid_count'] }} <span class="text-xs font-weight-normal text-success">mhs</span></h4>
                            </div>
                            <span class="material-icons text-success" style="font-size: 36px; opacity: 0.8;">check_circle</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border border-radius-lg bg-light d-flex align-items-center justify-content-between" style="border-radius: 10px; background-color: #f8f9fa; border-left: 5px solid #f44336 !important;">
                            <div>
                                <span class="text-xs font-weight-bold text-uppercase text-danger" style="font-size: 0.65rem; letter-spacing: 0.5px; color: #c62828 !important;">Total Dihapus (Dibersihkan)</span>
                                <h4 class="mb-0 font-weight-bolder text-danger" style="color: #c62828 !important;">{{ session('cleaning_report')['deleted_count'] }} <span class="text-xs font-weight-normal text-danger">mhs</span></h4>
                            </div>
                            <span class="material-icons text-danger" style="font-size: 36px; opacity: 0.8;">cancel</span>
                        </div>
                    </div>
                </div>

                <!-- Breakdown & Detail Accordion -->
                <div class="row pt-2">
                    <div class="col-md-6 mb-3 mb-md-0 border-end border-light">
                        <h6 class="font-weight-bold text-dark mb-3" style="font-size: 0.9rem;"><i class="material-icons text-sm align-middle me-1">pie_chart</i>Rincian Data yang Tidak Valid (Dihapus):</h6>
                        <ul class="list-group list-group-flush border-radius-lg">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 border-0 border-bottom">
                                <span class="text-sm text-secondary d-flex align-items-center"><i class="material-icons text-sm me-2 align-middle text-warning">person_off</i>Nama tidak valid / duplikat</span>
                                <span class="badge text-xs font-weight-bold px-2.5 py-1 border-radius-md" style="background-color: rgba(255, 152, 0, 0.1); color: #ef6c00;">{{ session('cleaning_report')['breakdown']['nama_invalid'] }} data</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 border-0 border-bottom">
                                <span class="text-sm text-secondary d-flex align-items-center"><i class="material-icons text-sm me-2 align-middle text-danger">badge</i>NPM tidak diawali "13." / duplikat</span>
                                <span class="badge text-xs font-weight-bold px-2.5 py-1 border-radius-md" style="background-color: rgba(244, 67, 54, 0.1); color: #c62828;">{{ session('cleaning_report')['breakdown']['npm_invalid'] }} data</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 border-0 border-bottom">
                                <span class="text-sm text-secondary d-flex align-items-center"><i class="material-icons text-sm me-2 align-middle text-info">star_half</i>IPK tidak valid (di luar 0 - 4)</span>
                                <span class="badge text-xs font-weight-bold px-2.5 py-1 border-radius-md" style="background-color: rgba(0, 188, 212, 0.1); color: #00838f;">{{ session('cleaning_report')['breakdown']['ipk_invalid'] }} data</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5 border-0">
                                <span class="text-sm text-secondary d-flex align-items-center"><i class="material-icons text-sm me-2 align-middle text-dark">find_in_page</i>Belum mengambil Proposal Skripsi (RPS)</span>
                                <span class="badge text-xs font-weight-bold px-2.5 py-1 border-radius-md" style="background-color: rgba(33, 33, 33, 0.1); color: #212121;">{{ session('cleaning_report')['breakdown']['rps_invalid'] }} data</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 ps-md-4">
                        <h6 class="font-weight-bold text-dark mb-3" style="font-size: 0.9rem;"><i class="material-icons text-sm align-middle me-1">rule</i>Ketentuan Parameter Data Cleaning (Python-Based):</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="material-icons text-xs text-secondary mt-1">lens</span>
                                <p class="text-xs text-secondary mb-0"><strong class="text-dark">Pembersihan Nama:</strong> Nama wajib terisi, spasi berlebih dibersihkan, huruf kecil disamakan, dan nama duplikat otomatis dieliminasi.</p>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="material-icons text-xs text-secondary mt-1">lens</span>
                                <p class="text-xs text-secondary mb-0"><strong class="text-dark">Saringan NPM:</strong> Hanya menerima mahasiswa yang memiliki NPM diawali dengan kode universitas <strong>"13."</strong>. NPM ganda dihapus.</p>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="material-icons text-xs text-secondary mt-1">lens</span>
                                <p class="text-xs text-secondary mb-0"><strong class="text-dark">Penyaringan Akademik IPK:</strong> Seluruh karakter non-numerik otomatis dibersihkan (koma dikonversi ke titik). Nilai wajib <strong>0.00 hingga 4.00</strong>.</p>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="material-icons text-xs text-secondary mt-1">lens</span>
                                <p class="text-xs text-secondary mb-0"><strong class="text-dark">Penyaringan RPS Skripsi:</strong> Hanya menerima mahasiswa yang sudah memprogram skripsi proposal (kolom RPS bertuliskan <strong>"Ya", "Sudah", atau "Sedang"</strong>).</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('cleaning_report')['deleted_count'] > 0)
                <!-- Collapsible Table for Deleted Students -->
                <div class="mt-4">
                    <button class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2 mb-0 border-radius-lg py-2.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDeletedList" aria-expanded="false" aria-controls="collapseDeletedList" style="font-weight: bold; text-transform: none; font-size: 0.75rem;">
                        <span class="material-icons text-sm">visibility</span> Klik di Sini untuk Melihat Daftar {{ session('cleaning_report')['deleted_count'] }} Mahasiswa yang Terhapus / Dibersihkan
                    </button>
                    <div class="collapse mt-3" id="collapseDeletedList">
                        <div class="table-responsive border border-radius-lg" style="border-radius: 8px; max-height: 250px; overflow-y: auto;">
                            <table class="table align-items-center mb-0">
                                <thead style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 5%">No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Mahasiswa</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NPM</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Alasan Dihapus / Tidak Valid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('cleaning_report')['details'] as $deletedMhs)
                                    <tr>
                                        <td class="align-middle ps-3">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-dark font-weight-bold text-capitalize">{{ $deletedMhs['nama'] }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-secondary font-weight-bold">{{ $deletedMhs['npm'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge text-xxs font-weight-bold px-2 py-1" style="background-color: rgba(244, 67, 54, 0.1); color: #c62828; border-radius: 4px;">{{ $deletedMhs['reason'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Riwayat Upload File Excel</h6>
                    <a href="{{ route('import-excel.create') }}" class="btn btn-sm btn-outline-white me-3 mb-0">Upload File Baru</a>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 5%">No</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama File</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal Upload</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Diupload Oleh</th>
                                <th class="text-secondary opacity-7 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($files as $file)
                            <tr>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $file->nama }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($file->tanggal_upload)->format('d/m/Y') }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold text-uppercase">{{ $file->pengguna->nama ?? 'Admin' }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-3">
                                        <a href="{{ route('mahasiswa.index') }}" class="btn btn-link text-info p-0 mb-0" title="Lihat Data Mahasiswa">
                                            <i class="material-icons text-lg">visibility</i>
                                        </a>
                                        @if($file->laporan_cleaning)
                                        <a href="{{ route('import-excel.show-report', $file->id_file) }}" class="btn btn-link text-success p-0 mb-0" title="Lihat Laporan Pembersihan Data">
                                            <i class="material-icons text-lg">cleaning_services</i>
                                        </a>
                                        @endif
                                        <form action="{{ route('import-excel.destroy', $file->id_file) }}" method="POST" id="delete-form-{{ $file->id_file }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-link text-danger p-0 mb-0" onclick="confirmDelete('{{ $file->id_file }}')" title="Hapus">
                                                <i class="material-icons text-lg">delete</i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-sm mb-0">Belum ada riwayat upload file.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Menghapus riwayat file akan menghapus semua nilai kuesioner yang terkait dengan file ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endsection
