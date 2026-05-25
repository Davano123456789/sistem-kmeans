@extends('layouts.master')

@section('title', 'Hasil Cluster (Riwayat)')
@section('breadcrumb', 'Hasil Cluster')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                    <h6 class="text-white text-capitalize mb-0" style="font-family: 'Outfit', sans-serif;">Daftar Riwayat Hasil Cluster</h6>
                    <span class="badge bg-light text-dark text-xs">{{ $riwayat->count() }} Riwayat Tersimpan</span>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0 px-4 pb-3">
                    <table class="table align-items-center mb-0 text-sm table-hover" id="riwayatTable">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary font-weight-bolder opacity-7 ps-2" style="width: 5%">No</th>
                                <th class="text-uppercase text-secondary font-weight-bolder opacity-7 ps-2">Nama Riwayat</th>
                                <th class="text-uppercase text-secondary font-weight-bolder opacity-7 ps-2">Tanggal Simpan</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Jumlah Data</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Total Iterasi</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                            <tr>
                                <td class="align-middle text-start font-weight-bold ps-2">{{ $loop->iteration }}</td>
                                <td class="align-middle text-start">
                                    <h6 class="mb-0 text-sm font-weight-bold text-dark">{{ $r->nama_riwayat }}</h6>
                                </td>
                                <td class="align-middle text-start">
                                    <span class="text-xs text-secondary font-weight-bold">
                                        {{ $r->tanggal->translatedFormat('d F Y, H:i') }}
                                    </span>
                                </td>
                                <td class="align-middle text-center font-weight-bold">
                                    <span class="badge badge-sm bg-gradient-info">{{ $r->jumlah_mahasiswa }} Mahasiswa</span>
                                </td>
                                <td class="align-middle text-center font-weight-bold">
                                    <span class="badge badge-sm bg-gradient-secondary">{{ $r->iterasi_total }} Iterasi</span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <a href="{{ route('kmeans.riwayat.show', $r->id_riwayat) }}" class="btn btn-sm btn-dark mb-0 d-flex align-items-center gap-1" title="Lihat Hasil Detail">
                                            <i class="material-icons text-xs">visibility</i> Lihat
                                        </a>
                                        
                                        @if(!str_contains(strtolower(auth()->user()->role), 'dosen'))
                                        <form action="{{ route('kmeans.riwayat.destroy', $r->id_riwayat) }}" method="POST" class="delete-form d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger mb-0 d-flex align-items-center gap-1 btn-delete" title="Hapus Riwayat">
                                                <i class="material-icons text-xs">delete</i> Hapus
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-secondary">
                                        <i class="material-icons" style="font-size: 48px;">assignment_late</i>
                                        <p class="mt-2 mb-0 font-weight-bold text-sm">Belum ada riwayat hasil clustering yang disimpan.</p>
                                        <small class="text-xs">Silakan lakukan perhitungan di menu <a href="{{ route('kmeans.index') }}" class="font-weight-bold text-dark text-decoration-underline">Proses K-Means</a> lalu klik Simpan.</small>
                                    </div>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle konfirmasi hapus riwayat
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function (e) {
                const form = this.closest('.delete-form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data riwayat clustering ini akan dihapus secara permanen beserta semua data anggotanya!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
