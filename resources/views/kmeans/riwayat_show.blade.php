@extends('layouts.master')

@section('title', 'Detail Riwayat Cluster')
@section('breadcrumb', 'Detail Riwayat')

@section('content')
<div class="row">
    <!-- Header Ringkasan Riwayat -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header p-3 bg-gradient-dark text-white border-radius-lg mx-3 mt-n4 position-relative z-index-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h5 class="text-white mb-0" style="font-family: 'Outfit', sans-serif;">{{ preg_replace('/\s+\d{2}:\d{2}$/', '', $riwayat->nama_riwayat) }}</h5>
                    <p class="text-xs opacity-8 mb-0">
                        Disimpan pada: {{ $riwayat->tanggal->translatedFormat('d F Y') }} | Total Iterasi: {{ $riwayat->iterasi_total }} | Jumlah Data: {{ $riwayat->jumlah_mahasiswa }} Mahasiswa
                    </p>
                </div>
                {{--
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('kmeans.riwayat.index') }}" class="btn btn-sm btn-outline-light mb-0 d-flex align-items-center gap-1">
                        <i class="material-icons text-sm">arrow_back</i> Kembali ke Riwayat
                    </a>
                    <a href="{{ route('kmeans.riwayat.export', $riwayat->id_riwayat) }}" class="btn btn-sm btn-success mb-0 d-flex align-items-center gap-1">
                        <i class="material-icons text-sm">file_download</i> Ekspor Excel
                    </a>
                </div>
                --}}
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-7">
                        <h6 class="font-weight-bold text-dark">Centroid Awal yang Dipilih:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-xs text-center mb-0">
                                <thead class="bg-gray-100 font-weight-bold">
                                    <tr>
                                        @foreach($topics as $index => $t)
                                        <th>Centroid {{ $index+1 }} ({{ $t }})</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach($selectedCentroidsNames as $name)
                                        <td class="py-2 text-dark font-weight-bold">{{ $name }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="bg-light p-3 border-radius-lg border h-100 d-flex flex-column justify-content-center">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-xs font-weight-bold text-dark">PCA PC1 (Variance)</span>
                                <span class="text-xs font-weight-bold text-dark">{{ number_format($riwayat->explained_variance_ratio[0] * 100, 2) }}%</span>
                            </div>
                            <div class="progress progress-xs mb-2 bg-gray-300">
                                <div class="progress-bar bg-dark" style="width: {{ $riwayat->explained_variance_ratio[0] * 100 }}%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-xs font-weight-bold text-dark">PCA PC2 (Variance)</span>
                                <span class="text-xs font-weight-bold text-dark">{{ number_format($riwayat->explained_variance_ratio[1] * 100, 2) }}%</span>
                            </div>
                            <div class="progress progress-xs mb-2 bg-gray-300">
                                <div class="progress-bar bg-dark" style="width: {{ $riwayat->explained_variance_ratio[1] * 100 }}%"></div>
                            </div>
                            <span class="text-xxs font-weight-bold text-success">
                                <i class="material-icons text-xs align-middle me-1">check_circle</i>
                                Total Informasi Terwakili: {{ number_format(array_sum($riwayat->explained_variance_ratio) * 100, 2) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 VISUALISASI PCA & CONVEX HULL -->
    <div class="col-12 mb-4">
        <div class="card p-3">
            <h6 class="font-weight-bold px-2 mb-3">Visualisasi PCA & Convex Hull Terbimbing</h6>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card p-3 border shadow-none bg-white">
                        <div style="position: relative; height: 480px;">
                            <canvas id="pcaHistoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card p-3 border shadow-none bg-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-gradient-dark text-xxs font-weight-bold uppercase mb-2">PCA Analysis</span>
                            <h6 class="font-weight-bold text-dark mb-2">Interpretasi Visualisasi</h6>
                            <p class="text-xs text-secondary mb-3">
                                Plot ini menggambarkan pembagian 4 kelompok rekomendasi topik tugas akhir berdasarkan kemiripan nilai kuisioner yang tereduksi ke dalam 2 dimensi (PC1 & PC2).
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
                            <a href="{{ route('kmeans.riwayat.export', $riwayat->id_riwayat) }}" class="btn btn-success w-100 mb-0 d-flex align-items-center justify-content-center">
                                <i class="material-icons text-md me-2">file_download</i> Ekspor Hasil Cluster ke Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Anggota Kelompok (Cluster) -->
    <div class="col-12 mb-4">
        <div class="card p-3">
            <h6 class="font-weight-bold px-2 mb-3">Anggota Kelompok Rekomendasi (Cluster)</h6>
            <div class="row">
                @foreach($clusters as $cIdx => $members)
                <div class="col-md-3 mb-3">
                    <div class="card shadow-none border h-100">
                        <div class="card-header p-2 bg-gradient-{{ ['primary', 'info', 'success', 'warning'][$cIdx] }} text-white text-center">
                            <h6 class="text-white mb-0 text-xs">{{ $topics[$cIdx] }}</h6>
                            <small class="text-xs">({{ count($members) }} Orang)</small>
                            <div class="text-xxs opacity-9 mt-1" style="font-size: 0.65rem;">
                                Centroid PCA:<br>
                                PC1: {{ number_format($centroidsPca[$cIdx]['PC1'], 3) }} | 
                                PC2: {{ number_format($centroidsPca[$cIdx]['PC2'], 3) }}
                            </div>
                        </div>
                        <div class="card-body p-2" style="max-height: 350px; overflow-y: auto;">
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
        </div>
    </div>

    <!-- Data Jarak Detail ke Centroid -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header p-3 bg-light border-bottom">
                <h6 class="mb-0 font-weight-bold text-dark">Tabel Hasil Perhitungan Jarak Euclidean & PCA</h6>
                <p class="text-xs text-secondary mb-0">Tabel final penentuan cluster berdasarkan jarak minimum ke setiap centroid.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-0" style="max-height: 500px">
                    <table class="table table-hover align-items-center mb-0 text-xs">
                        <thead class="position-sticky top-0 bg-white z-index-2">
                            <tr>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" style="width: 5%">No</th>
                                <th class="text-uppercase text-secondary font-weight-bolder opacity-7">Mahasiswa</th>
                                @foreach($topics as $t)
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" title="{{ $t }}">C{{ $loop->iteration }}</th>
                                @endforeach
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Jarak Min</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC1</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC2</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Cluster</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $res)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                <td class="px-4 font-weight-bold text-dark">{{ $res['mahasiswa']->nama }}</td>
                                @foreach($res['distances'] as $dist)
                                <td class="text-center">{{ number_format($dist, 4) }}</td>
                                @endforeach
                                <td class="text-center font-weight-bold text-dark">{{ number_format($res['min_distance'], 4) }}</td>
                                <td class="text-center text-secondary font-weight-bold">{{ number_format($res['PC1'], 3) }}</td>
                                <td class="text-center text-secondary font-weight-bold">{{ number_format($res['PC2'], 3) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-sm bg-gradient-{{ ['primary', 'info', 'success', 'warning'][$res['cluster']-1] }}">
                                        Cluster {{ $res['cluster'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Register the datalabels plugin
        Chart.register(ChartDataLabels);

        const colors = [
            'rgba(233, 30, 99, 1)',   // Primary
            'rgba(3, 169, 244, 1)',  // Info
            'rgba(76, 175, 80, 1)',   // Success
            'rgba(251, 140, 0, 1)'   // Warning
        ];
        const bgColors = [
            'rgba(233, 30, 99, 0.08)',
            'rgba(3, 169, 244, 0.08)',
            'rgba(76, 175, 80, 0.08)',
            'rgba(251, 140, 0, 0.08)'
        ];
        const topicNames = [
            'Application Developer',
            'Data Analyst',
            'System Analyst',
            'IT Auditor & Governance'
        ];

        const ctx = document.getElementById('pcaHistoryChart').getContext('2d');
        const datasets = [];

        // 1. Group points by cluster
        const clusterPoints = [[], [], [], []];
        @foreach($results as $res)
            clusterPoints[{{ $res['cluster'] - 1 }}].push({
                x: {{ $res['PC1'] }},
                y: {{ $res['PC2'] }},
                nama: "{{ addslashes($res['mahasiswa']->nama) }}"
            });
        @endforeach

        // 2. Prepare hulls bounding
        const hulls = [
            @foreach($hulls as $cIdx => $hullPoints)
                [
                    @foreach($hullPoints as $hp)
                        { x: {{ $hp['x'] }}, y: {{ $hp['y'] }} },
                    @endforeach
                ],
            @endforeach
        ];

        // 3. PCA Centroids
        const centroids = [
            @foreach($centroidsPca as $cIdx => $c)
                { x: {{ $c['PC1'] }}, y: {{ $c['PC2'] }} },
            @endforeach
        ];

        for (let c = 0; c < 4; c++) {
            // A. Convex Hull boundary
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
                    datalabels: { display: false }
                });
            }

            // B. Scatter Points of Students
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

            // C. PCA Centroid (Square Marker)
            if (centroids[c] && (centroids[c].x !== 0 || centroids[c].y !== 0)) {
                datasets.push({
                    type: 'scatter',
                    label: 'Centroid ' + topicNames[c],
                    data: [centroids[c]],
                    backgroundColor: 'rgba(0, 0, 0, 0.12)',
                    borderColor: '#000000',
                    borderWidth: 2,
                    pointStyle: 'rect',
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
                        labels: {
                            font: { size: 10 },
                            padding: 15,
                            filter: function(item) {
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
    });
</script>
@endpush
