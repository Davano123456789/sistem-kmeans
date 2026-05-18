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
                                        <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC1</th>
                                        <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC2</th>
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
                                        <td class="text-center text-secondary font-weight-bold">{{ number_format($res['PC1'], 3) }}</td>
                                        <td class="text-center text-secondary font-weight-bold">{{ number_format($res['PC2'], 3) }}</td>
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
                        <div class="row mb-4">
                            @foreach($h['clusters'] as $cIdx => $members)
                            <div class="col-md-3">
                                <div class="card shadow-none border">
                                    <div class="card-header p-2 bg-gradient-{{ ['primary', 'info', 'success', 'warning'][$cIdx] }} text-white text-center">
                                        <h6 class="text-white mb-0 text-xs">{{ $topics[$cIdx] }}</h6>
                                        <small class="text-xs">({{ count($members) }} Orang)</small>
                                        <div class="text-xxs opacity-9 mt-1" style="font-size: 0.65rem;">
                                            Centroid PCA:<br>
                                            PC1: {{ number_format($h['centroids_pca'][$cIdx]['PC1'], 3) }} | 
                                            PC2: {{ number_format($h['centroids_pca'][$cIdx]['PC2'], 3) }}
                                        </div>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm mb-0 text-xxs">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th class="text-center">PC1</th>
                                                    <th class="text-center">PC2</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($members as $m)
                                                <tr>
                                                    <td class="font-weight-bold text-dark">{{ $m->nama }}</td>
                                                    <td class="text-center text-secondary">{{ number_format($m->PC1 ?? 0, 3) }}</td>
                                                    <td class="text-center text-secondary">{{ number_format($m->PC2 ?? 0, 3) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-secondary py-2">Kosong</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- 📊 VISUALISASI PCA & CONVEX HULL -->
                        <h6 class="font-weight-bold px-2 mt-4">Visualisasi PCA & Convex Hull (Iterasi {{ $h['iterasi'] }})</h6>
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <div class="card p-3 border shadow-none bg-white">
                                    <div style="position: relative; height: 480px;">
                                        <canvas id="pcaChart-{{ $h['iterasi'] }}"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <div class="card p-3 border shadow-none bg-white h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <span class="badge bg-gradient-dark text-xxs font-weight-bold uppercase mb-2">PCA Analysis</span>
                                        <h6 class="font-weight-bold text-dark mb-1">Explained Variance Ratio</h6>
                                        <p class="text-xs text-secondary mb-3">Rasio informasi yang berhasil dirangkum dari dimensi asli (A1-D4):</p>
                                        
                                        <div class="bg-gray-100 p-3 border-radius-lg mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-xs font-weight-bold text-dark">PC1 (Principal Component 1)</span>
                                                <span class="text-xs font-weight-bold text-dark">{{ number_format($h['explained_variance_ratio'][0] * 100, 2) }}%</span>
                                            </div>
                                            <div class="progress progress-xs mb-3">
                                                <div class="progress-bar bg-dark" style="width: {{ $h['explained_variance_ratio'][0] * 100 }}%"></div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-xs font-weight-bold text-dark">PC2 (Principal Component 2)</span>
                                                <span class="text-xs font-weight-bold text-dark">{{ number_format($h['explained_variance_ratio'][1] * 100, 2) }}%</span>
                                            </div>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-dark" style="width: {{ $h['explained_variance_ratio'][1] * 100 }}%"></div>
                                            </div>
                                        </div>

                                        <p class="text-xs font-weight-bold text-success mb-3">
                                            <i class="material-icons text-xs me-1 align-middle">check_circle</i>
                                            Total Informasi Terwakili: {{ number_format(array_sum($h['explained_variance_ratio']) * 100, 2) }}%
                                        </p>
                                        
                                        <div class="text-xs text-secondary border-top pt-3">
                                            <p class="mb-1"><b class="text-dark">Petunjuk Legenda Plot:</b></p>
                                            <ul class="ps-3 mb-0" style="list-style-type: square;">
                                                <li>Setiap <b>lingkaran bulat</b> mewakili satu mahasiswa.</li>
                                                <li>Warna lingkaran menandakan <b>Cluster Rekomendasi Topik</b> saat ini.</li>
                                                <li>Garis solid sewarna yang mengelilingi adalah batas terluar kelompok (<b>Convex Hull</b>).</li>
                                                <li>Kotak besar hitam berlabel <b>1 - 4</b> di tengah mewakili lokasi <b>Centroid PCA</b>.</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-3 border-top">
                                        <a href="{{ route('kmeans.export', ['iterasi' => $h['iterasi']]) }}" class="btn btn-success w-100 mb-0 d-flex align-items-center justify-content-center">
                                            <i class="material-icons text-md me-2">file_download</i> Ekspor Excel Iterasi {{ $h['iterasi'] }}
                                        </a>
                                    </div>
                                </div>
                            </div>
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

@push('scripts')
@if(isset($history))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Daftarkan plugin datalabels secara global
        Chart.register(ChartDataLabels);

        const colors = [
            'rgba(233, 30, 99, 1)',   // Primary - Pinkish Red
            'rgba(3, 169, 244, 1)',  // Info - Cyan/Blue
            'rgba(76, 175, 80, 1)',   // Success - Green
            'rgba(251, 140, 0, 1)'   // Warning - Orange
        ];
        const bgColors = [
            'rgba(233, 30, 99, 0.08)',
            'rgba(3, 169, 244, 0.08)',
            'rgba(76, 175, 80, 0.08)',
            'rgba(251, 140, 0, 0.08)'
        ];
        const topicNames = [
            'Application Developer',
            'IT Auditor & Governance',
            'System Analyst',
            'Data Analyst'
        ];

        @foreach($history as $h)
        (function() {
            const ctx = document.getElementById('pcaChart-{{ $h['iterasi'] }}').getContext('2d');
            
            const datasets = [];

            // 1. Kelompokkan titik mahasiswa per cluster
            const clusterPoints = [[], [], [], []];
            @foreach($h['results'] as $res)
                clusterPoints[{{ $res['cluster'] - 1 }}].push({
                    x: {{ $res['PC1'] }},
                    y: {{ $res['PC2'] }},
                    nama: "{{ addslashes($res['mahasiswa']->nama) }}"
                });
            @endforeach

            // 2. Siapkan data Convex Hull pembatas
            const hulls = [
                @foreach($h['hulls'] as $cIdx => $hullPoints)
                    [
                        @foreach($hullPoints as $hp)
                            { x: {{ $hp['x'] }}, y: {{ $hp['y'] }} },
                        @endforeach
                    ],
                @endforeach
            ];

            // 3. Siapkan data Centroid PCA
            const centroids = [
                @foreach($h['centroids_pca'] as $cIdx => $c)
                    { x: {{ $c['PC1'] }}, y: {{ $c['PC2'] }} },
                @endforeach
            ];

            // Masukkan data ke dataset Chart.js secara berurutan
            for (let c = 0; c < 4; c++) {
                // A. Garis Batas Convex Hull (jika minimal 3 titik)
                if (hulls[c] && hulls[c].length > 0) {
                    datasets.push({
                        type: 'line',
                        label: 'Batas ' + topicNames[c],
                        data: hulls[c],
                        borderColor: colors[c],
                        backgroundColor: bgColors[c],
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 0,
                        showLine: true,
                        tension: 0.1,
                        datalabels: { display: false } // Sembunyikan label nama pada garis hull
                    });
                }

                // B. Scatter Points Mahasiswa
                if (clusterPoints[c].length > 0) {
                    datasets.push({
                        type: 'scatter',
                        label: topicNames[c],
                        data: clusterPoints[c],
                        backgroundColor: colors[c],
                        borderColor: '#ffffff',
                        borderWidth: 1.5,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        datalabels: {
                            display: true,
                            align: 'top',
                            offset: 4,
                            font: { size: 9, weight: 'bold' },
                            color: '#444444',
                            formatter: function(value) {
                                return value.nama;
                            }
                        }
                    });
                }

                // C. Centroid PCA (Square Marker)
                if (centroids[c] && (centroids[c].x !== 0 || centroids[c].y !== 0)) {
                    datasets.push({
                        type: 'scatter',
                        label: 'Centroid ' + topicNames[c],
                        data: [centroids[c]],
                        backgroundColor: 'rgba(0, 0, 0, 0.12)',
                        borderColor: '#000000',
                        borderWidth: 2,
                        pointStyle: 'rect', // Kotak/Square
                        pointRadius: 16,
                        pointHoverRadius: 18,
                        z: 10,
                        datalabels: {
                            display: true,
                            align: 'center',
                            anchor: 'center',
                            font: { size: 10, weight: 'bold' },
                            color: '#000000',
                            formatter: function() {
                                return (c + 1).toString();
                            }
                        }
                    });
                }
            }

            new Chart(ctx, {
                data: {
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: {
                                display: true,
                                text: 'Principal Component 1 (PC1)',
                                font: { weight: 'bold', size: 11 }
                            },
                            grid: {
                                color: '#f0f0f0'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Principal Component 2 (PC2)',
                                font: { weight: 'bold', size: 11 }
                            },
                            grid: {
                                color: '#f0f0f0'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'center',
                            labels: {
                                font: { size: 10 },
                                padding: 15,
                                filter: function(item) {
                                    // Hanya tampilkan label scatter points mahasiswa di legenda (hindari duplikat batas & centroid)
                                    return item.text && topicNames.includes(item.text);
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            callbacks: {
                                label: function(context) {
                                    const raw = context.raw;
                                    if (raw.nama) {
                                        return ' ' + raw.nama + ' (PC1: ' + raw.x.toFixed(3) + ', PC2: ' + raw.y.toFixed(3) + ')';
                                    }
                                    return ' ' + context.dataset.label + ' (PC1: ' + raw.x.toFixed(3) + ', PC2: ' + raw.y.toFixed(3) + ')';
                                }
                            }
                        }
                    }
                }
            });
        })();
        @endforeach
    });
</script>
@endif
@endpush
