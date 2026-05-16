@extends('layouts.master')

@section('title', 'Proses K-Means')
@section('breadcrumb', 'K-Means')

@section('content')
<div class="row">
    <!-- Form Persiapan -->
    <div class="col-md-4">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Parameter Clustering</h6>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('kmeans.hitung') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <h6 class="text-sm font-weight-bold">Pilih Centroid Awal (4 Mahasiswa)</h6>
                        <p class="text-xs text-secondary">Tentukan mahasiswa yang akan menjadi pusat cluster pertama.</p>
                        
                        @php
                            $topics = [
                                'Application Developer',
                                'IT Auditor & Governance',
                                'System Analyst',
                                'Data Analyst'
                            ];
                        @endphp

                        @for($i = 1; $i <= 4; $i++)
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold">
                                Mahasiswa {{ $i }} ({{ $topics[$i-1] }})
                            </label>
                            <div class="input-group input-group-outline">
                                <select name="centroids[]" class="form-control" required>
                                    <option value="">-- Pilih Mahasiswa --</option>
                                    @foreach($mahasiswa as $mhs)
                                        <option value="{{ $mhs->id_mahasiswa }}" 
                                            {{ (isset($selectedCentroids) && in_array($mhs->id_mahasiswa, $selectedCentroids)) || old('centroids.'.$i-1) == $mhs->id_mahasiswa ? 'selected' : '' }}>
                                            {{ $mhs->nama }} ({{ $mhs->nim }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="material-icons text-sm me-1">calculate</i> Mulai Perhitungan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h6 class="font-weight-bold">Info:</h6>
                <p class="text-sm text-secondary mb-0">
                    Sistem akan mengambil data kuesioner dari <b>{{ $mahasiswa->count() }}</b> mahasiswa yang telah terdaftar.
                </p>
            </div>
        </div>
    </div>

    <!-- Data Teknis (Nilai) -->
    <div class="col-md-8">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3 mb-0">Data Teknis Kuesioner (A1 - D4)</h6>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0" style="max-height: 500px">
                    <table class="table align-items-center mb-0 text-xs">
                        <thead class="position-sticky top-0 bg-white z-index-2">
                            <tr>
                                <th class="text-uppercase text-secondary font-weight-bolder opacity-7">Nama</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">A1-A4</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">B1-B4</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">D1-D4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswa as $mhs)
                            <tr>
                                <td class="px-4">
                                    <h6 class="mb-0 text-xs">{{ $mhs->nama }}</h6>
                                </td>
                                <td class="align-middle text-center">
                                    {{ $mhs->nilaiKuesioner->a1 ?? 0 }}|{{ $mhs->nilaiKuesioner->a2 ?? 0 }}|{{ $mhs->nilaiKuesioner->a3 ?? 0 }}|{{ $mhs->nilaiKuesioner->a4 ?? 0 }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ $mhs->nilaiKuesioner->b1 ?? 0 }}|{{ $mhs->nilaiKuesioner->b2 ?? 0 }}|{{ $mhs->nilaiKuesioner->b3 ?? 0 }}|{{ $mhs->nilaiKuesioner->b4 ?? 0 }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ $mhs->nilaiKuesioner->d1 ?? 0 }}|{{ $mhs->nilaiKuesioner->d2 ?? 0 }}|{{ $mhs->nilaiKuesioner->d3 ?? 0 }}|{{ $mhs->nilaiKuesioner->d4 ?? 0 }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Belum ada data untuk diolah.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@if(isset($history))
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header p-3">
                <h5 class="mb-0">Hasil Perhitungan K-Means</h5>
                <p class="text-sm mb-0">
                    Status: {!! $converged ? '<span class="badge badge-sm bg-gradient-success">Konvergen</span>' : '<span class="badge badge-sm bg-gradient-warning">Mencapai Batas Iterasi</span>' !!}
                </p>
            </div>
            <div class="card-body p-3">
                <ul class="nav nav-tabs mb-4" id="kmeansTabs" role="tablist">
                    @foreach($history as $h)
                    <li class="nav-item">
                        <a class="nav-link {{ $loop->last ? 'active' : '' }}" id="iter-{{ $h['iterasi'] }}-tab" data-bs-toggle="tab" href="#iter-{{ $h['iterasi'] }}" role="tab">
                            Iterasi {{ $h['iterasi'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="kmeansTabsContent">
                    @foreach($history as $h)
                    <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="iter-{{ $h['iterasi'] }}" role="tabpanel">
                        <h6 class="font-weight-bold px-2">Centroid Iterasi {{ $h['iterasi'] }}</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered text-center text-xs">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Centroid</th>
                                        @foreach(['a1','a2','a3','a4','b1','b2','b3','b4','d1','d2','d3','d4'] as $f)
                                        <th class="text-uppercase">{{ $f }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($h['centroids'] as $cIdx => $cVals)
                                    <tr>
                                        <td class="font-weight-bold text-start">{{ $topics[$cIdx] }}</td>
                                        @foreach($cVals as $v)
                                        <td>{{ number_format($v, 2) }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h6 class="font-weight-bold px-2">Jarak ke Centroid & Cluster</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-items-center mb-0 text-xs">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary font-weight-bolder opacity-7">Mahasiswa</th>
                                        @foreach($topics as $t)
                                        <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" title="{{ $t }}">C{{ $loop->iteration }}</th>
                                        @endforeach
                                        <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Jarak Min</th>
                                        <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Rekomendasi Topik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($h['results'] as $res)
                                    <tr>
                                        <td class="px-4">{{ $res['mahasiswa']->nama }}</td>
                                        @foreach($res['distances'] as $dist)
                                        <td class="text-center">{{ number_format($dist, 4) }}</td>
                                        @endforeach
                                        <td class="text-center font-weight-bold">{{ number_format($res['min_distance'], 4) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-sm bg-gradient-{{ ['primary', 'info', 'success', 'warning'][$res['cluster']-1] }}">
                                                {{ $topics[$res['cluster']-1] }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <h6 class="font-weight-bold px-2 mt-4">Anggota Kelompok (Cluster)</h6>
                        <div class="row">
                            @foreach($h['clusters'] as $cIdx => $members)
                            <div class="col-md-3">
                                <div class="card shadow-none border">
                                    <div class="card-header p-2 bg-gradient-{{ ['primary', 'info', 'success', 'warning'][$cIdx] }} text-white text-center">
                                        <h6 class="text-white mb-0 text-xs">{{ $topics[$cIdx] }}</h6>
                                        <small class="text-xs">({{ count($members) }} Orang)</small>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm mb-0 text-xs">
                                            <tbody>
                                                @forelse($members as $m)
                                                <tr>
                                                    <td>{{ $m->nama }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td class="text-center text-secondary">Kosong</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
