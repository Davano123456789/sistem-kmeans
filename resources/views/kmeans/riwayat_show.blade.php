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
                                        @foreach($nama_clusters as $index => $t)
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

                             <details class="mt-3 border-top pt-2" style="outline: none;">
                                 <summary class="text-xs font-weight-bold text-dark cursor-pointer" style="outline: none; list-style: none; display: flex; align-items: center; gap: 4px;">
                                     <i class="material-icons text-sm align-middle">help_outline</i> Bagaimana koordinat PC1 & PC2 dihitung?
                                 </summary>
                                 <div class="text-xxs text-secondary mt-2 ps-3">
                                     <p class="mb-1">Koordinat 2D (PC1 & PC2) diperoleh menggunakan <b>Principal Component Analysis (PCA)</b> untuk mereduksi 12 dimensi kuesioner:</p>
                                     <ol class="ps-3 mb-2" style="list-style-type: decimal;">
                                         <li><b>Standardisasi:</b> Nilai kuesioner disetarakan berdasarkan rata-rata & deviasi nilai sekelas agar adil.</li>
                                         <li><b>PC1 (Sumbu Horizontal):</b> Arah variansi data terbesar. Dipengaruhi kuat oleh nilai kuesioner <i>IT Auditor</i> (bobot positif) vs <i>Data Analyst</i> (bobot negatif).</li>
                                         <li><b>PC2 (Sumbu Vertikal):</b> Arah variansi terbesar kedua. Dipengaruhi kuat oleh nilai kuesioner <i>App Dev</i> & <i>System Analyst</i> (bobot positif) vs <i>Data Analyst</i> (bobot negatif).</li>
                                     </ol>
                                     <p class="mb-0"><b>Rumus Proyeksi:</b><br><code class="text-dark">PC = (Nilai_Scaled_1 &times; Bobot_1) + ... + (Nilai_Scaled_12 &times; Bobot_12)</code></p>
                                 </div>
                             </details>
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
                        @php $colorClasses = ['primary', 'info', 'success', 'warning', 'danger', 'secondary', 'dark', 'light', 'primary', 'info']; @endphp
                        <div class="card-header p-2 bg-gradient-{{ $colorClasses[$cIdx % 10] }} text-white text-center">
                            <h6 class="text-white mb-0 text-xs">{{ $nama_clusters[$cIdx] ?? ('Cluster ' . ($cIdx + 1)) }}</h6>
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
                                @foreach($nama_clusters as $t)
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" title="{{ $t }}">C{{ $loop->iteration }}</th>
                                @endforeach
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Jarak Min</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC1</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">PC2</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Cluster</th>
                                <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7">Aksi</th>
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
                                    @php $colorClasses = ['primary', 'info', 'success', 'warning', 'danger', 'secondary', 'dark', 'light', 'primary', 'info']; @endphp
                                    <span class="badge badge-sm bg-gradient-{{ $colorClasses[($res['cluster']-1) % 10] }}">
                                        Cluster {{ $res['cluster'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-info p-0 mb-0 btn-detail-hitung" 
                                        data-nama="{{ $res['mahasiswa']->nama }}"
                                        data-kuesioner="{{ json_encode($res['mahasiswa']->nilaiKuesioner) }}"
                                        data-distances="{{ json_encode($res['distances']) }}"
                                        data-cluster="{{ $res['cluster'] }}"
                                        data-pc1="{{ number_format($res['PC1'], 3) }}"
                                        data-pc2="{{ number_format($res['PC2'], 3) }}"
                                        data-centroids="{{ json_encode($finalCentroids) }}"
                                        data-pca-params="{{ json_encode($pcaParameters) }}"
                                        title="Lihat Detail Perhitungan">
                                        <i class="material-icons text-md">calculate</i>
                                    </button>
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

<!-- Modal Detail Perhitungan -->
<div class="modal fade" id="modalDetailPerhitungan" tabindex="-1" role="dialog" aria-labelledby="modalDetailPerhitunganLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title text-white" id="modalDetailPerhitunganLabel" style="font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 4px;">
                    <i class="material-icons align-middle me-1">calculate</i> Detail Perhitungan K-Means & PCA
                </h5>
                <button type="button" class="btn-close text-white font-weight-bold" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1); border: none; background: none; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                <h5 class="font-weight-bold text-dark mb-1" id="detail-nama-mhs">Nama Mahasiswa</h5>
                <div class="mb-4" id="detail-summary-cluster">Terpilih Cluster X | PC1: X | PC2: X</div>
                
                <!-- Section 1: Nilai Kuesioner -->
                <h6 class="font-weight-bold text-dark border-bottom pb-1"><i class="material-icons text-sm align-middle me-1">assignment</i> 1. Nilai Kuesioner Asli</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm text-center text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Kategori</th>
                                <th>Variabel 1 (App Dev)</th>
                                <th>Variabel 2 (Data Analyst)</th>
                                <th>Variabel 3 (System Analyst)</th>
                                <th>Variabel 4 (IT Auditor)</th>
                            </tr>
                        </thead>
                        <tbody id="detail-table-kuesioner">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Section 2: Jarak Euclidean -->
                <h6 class="font-weight-bold text-dark border-bottom pb-1 mt-4"><i class="material-icons text-sm align-middle me-1">square_foot</i> 2. Perhitungan Jarak Euclidean (12 Dimensi)</h6>
                <p class="text-xs text-secondary mb-3">Rumus Jarak Euclidean ke Centroid: <strong>Jarak = &radic;&Sigma; (X<sub>Mhs</sub> - Centroid)&sup2;</strong></p>
                <div id="detail-euclidean-steps" class="mb-4">
                    <!-- Populated by JS -->
                </div>

                <!-- Section 3: Proyeksi PCA -->
                <h6 class="font-weight-bold text-dark border-bottom pb-1 mt-4"><i class="material-icons text-sm align-middle me-1">insights</i> 3. Perhitungan Proyeksi PCA (Reduksi Dimensi)</h6>
                <p class="text-xs text-secondary mb-2">
                    Proses PCA mereduksi data kuesioner dari dimensi tinggi ke 2 dimensi (PC1 & PC2) menggunakan standardisasi <strong>Z = (X - &mu;) / &sigma;</strong> dan perkalian dengan Eigenvector (Loadings).
                </p>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm text-center text-xs align-middle">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Variabel</th>
                                <th title="Nilai jawaban kuesioner asli mahasiswa" style="text-decoration: underline dotted; cursor: help;">Nilai (X)</th>
                                <th title="Rata-rata nilai variabel ini dari seluruh mahasiswa" style="text-decoration: underline dotted; cursor: help;">Mean (&mu;)</th>
                                <th title="Deviasi Standar (standar penyimpangan) nilai variabel ini dari seluruh mahasiswa" style="text-decoration: underline dotted; cursor: help;">Std Dev (&sigma;)</th>
                                <th title="Nilai Z-score (terstandarisasi). Rumus: Z = (Nilai - Mean) / Std Dev" style="text-decoration: underline dotted; cursor: help;">Standar (Z)</th>
                                <th title="Bobot pengaruh variabel ini terhadap sumbu utama PC1 (diperoleh dari Eigenvector ke-1)" style="text-decoration: underline dotted; cursor: help;">Loading PC1 (V<sub>1</sub>)</th>
                                <th title="Kontribusi variabel ini terhadap koordinat PC1. Rumus: Z * Loading PC1" style="text-decoration: underline dotted; cursor: help;">Z &times; V<sub>1</sub></th>
                                <th title="Bobot pengaruh variabel ini terhadap sumbu utama PC2 (diperoleh dari Eigenvector ke-2)" style="text-decoration: underline dotted; cursor: help;">Loading PC2 (V<sub>2</sub>)</th>
                                <th title="Kontribusi variabel ini terhadap koordinat PC2. Rumus: Z * Loading PC2" style="text-decoration: underline dotted; cursor: help;">Z &times; V<sub>2</sub></th>
                            </tr>
                        </thead>
                        <tbody id="detail-table-pca-steps">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div id="detail-pca-summary" class="bg-gray-50 border p-3 border-radius-md text-xs text-dark mt-2">
                    <!-- Populated by JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary mb-0" data-bs-dismiss="modal">Tutup</button>
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
            'rgba(251, 140, 0, 1)',   // Warning
            'rgba(244, 67, 54, 1)',   // Danger
            'rgba(158, 158, 158, 1)', // Secondary
            'rgba(52, 71, 103, 1)',   // Dark
            'rgba(173, 181, 189, 1)', // Light
            'rgba(233, 30, 99, 1)',
            'rgba(3, 169, 244, 1)'
        ];
        const bgColors = [
            'rgba(233, 30, 99, 0.08)',
            'rgba(3, 169, 244, 0.08)',
            'rgba(76, 175, 80, 0.08)',
            'rgba(251, 140, 0, 0.08)',
            'rgba(244, 67, 54, 0.08)',
            'rgba(158, 158, 158, 0.08)',
            'rgba(52, 71, 103, 0.08)',
            'rgba(173, 181, 189, 0.08)',
            'rgba(233, 30, 99, 0.08)',
            'rgba(3, 169, 244, 0.08)'
        ];
        const topicNames = {!! json_encode($nama_clusters ?? []) !!};
        const k = {{ $k }};

        const ctx = document.getElementById('pcaHistoryChart').getContext('2d');
        const datasets = [];

        // 1. Group points by cluster
        const clusterPoints = Array.from({length: k}, () => []);
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

        for (let c = 0; c < k; c++) {
            let colorIndex = c % 10;
            // A. Convex Hull boundary
            if (hulls[c] && hulls[c].length > 0) {
                datasets.push({
                    type: 'line',
                    label: 'Batas ' + (topicNames[c] || 'Cluster ' + (c+1)),
                    data: hulls[c],
                    borderColor: colors[colorIndex],
                    backgroundColor: bgColors[colorIndex],
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 0,
                    showLine: true,
                    tension: 0.1,
                    datalabels: { display: false }
                });
            }

            // B. Scatter Points of Students
            if (clusterPoints[c] && clusterPoints[c].length > 0) {
                datasets.push({
                    type: 'scatter',
                    label: 'Anggota ' + (topicNames[c] || 'Cluster ' + (c+1)),
                    data: clusterPoints[c],
                    backgroundColor: colors[colorIndex],
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
                    label: 'Centroid ' + (c + 1),
                    data: [centroids[c]],
                    backgroundColor: '#1a1a1a',
                    borderColor: colors[colorIndex],
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

        // Setup detail modal listener
        document.querySelectorAll('.btn-detail-hitung').forEach(btn => {
            btn.addEventListener('click', function() {
                const nama = this.getAttribute('data-nama');
                const kuesioner = JSON.parse(this.getAttribute('data-kuesioner'));
                const distances = JSON.parse(this.getAttribute('data-distances'));
                const cluster = parseInt(this.getAttribute('data-cluster'));
                const pc1 = this.getAttribute('data-pc1');
                const pc2 = this.getAttribute('data-pc2');
                const centroids = JSON.parse(this.getAttribute('data-centroids'));
                
                // Get topic names
                const topicsList = {!! json_encode($nama_clusters ?? $topics ?? []) !!};

                document.getElementById('detail-nama-mhs').innerText = nama;
                document.getElementById('detail-summary-cluster').innerHTML = `
                    <span class="badge bg-gradient-info text-white">Cluster ${cluster} (${topicsList[cluster-1] || 'Cluster ' + cluster})</span>
                    <span class="badge bg-gradient-secondary text-white ms-1">PC1: ${pc1}</span>
                    <span class="badge bg-gradient-secondary text-white ms-1">PC2: ${pc2}</span>
                `;

                // Render Nilai Kuesioner
                const tbodyKuesioner = document.getElementById('detail-table-kuesioner');
                tbodyKuesioner.innerHTML = `
                    <tr>
                        <td class="font-weight-bold text-start">Minat (A)</td>
                        <td>A1 = ${kuesioner.a1 ?? 0}</td>
                        <td>A2 = ${kuesioner.a2 ?? 0}</td>
                        <td>A3 = ${kuesioner.a3 ?? 0}</td>
                        <td>A4 = ${kuesioner.a4 ?? 0}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-start">Keterampilan (B)</td>
                        <td>B1 = ${kuesioner.b1 ?? 0}</td>
                        <td>B3 = ${kuesioner.b3 ?? 0} <small class="text-secondary">(Data Analyst)</small></td>
                        <td>B4 = ${kuesioner.b4 ?? 0} <small class="text-secondary">(System Analyst)</small></td>
                        <td>B2 = ${kuesioner.b2 ?? 0} <small class="text-secondary">(IT Auditor)</small></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-start">Nilai Akademik (D)</td>
                        <td>D3 = ${kuesioner.d3 ?? 0} <small class="text-secondary">(App Dev)</small></td>
                        <td>D1 = ${kuesioner.d1 ?? 0} <small class="text-secondary">(Data Analyst)</small></td>
                        <td>D2 = ${kuesioner.d2 ?? 0} <small class="text-secondary">(System Analyst)</small></td>
                        <td>D4 = ${kuesioner.d4 ?? 0} <small class="text-secondary">(IT Auditor)</small></td>
                    </tr>
                `;

                // Render Euclidean distance calculation steps
                const euclideanContainer = document.getElementById('detail-euclidean-steps');
                euclideanContainer.innerHTML = '';

                // Features map to display calculation per role
                const rolesMap = [
                    { name: "Application Developer", features: [{f: 'a1', label: 'a1 (Minat)'}, {f: 'b1', label: 'b1 (Skill)'}, {f: 'd3', label: 'd3 (Nilai)'}] },
                    { name: "Data Analyst", features: [{f: 'a2', label: 'a2 (Minat)'}, {f: 'b3', label: 'b3 (Skill)'}, {f: 'd1', label: 'd1 (Nilai)'}] },
                    { name: "System Analyst", features: [{f: 'a3', label: 'a3 (Minat)'}, {f: 'b4', label: 'b4 (Skill)'}, {f: 'd2', label: 'd2 (Nilai)'}] },
                    { name: "IT Auditor & Governance", features: [{f: 'a4', label: 'a4 (Minat)'}, {f: 'b2', label: 'b2 (Skill)'}, {f: 'd4', label: 'd4 (Nilai)'}] }
                ];

                centroids.forEach((centroid, cIdx) => {
                    const cNum = cIdx + 1;
                    const isMin = cNum === cluster;
                    const distanceValue = distances[cIdx] !== undefined ? parseFloat(distances[cIdx]).toFixed(4) : 'N/A';
                    
                    let html = `
                        <div class="card shadow-none border mb-3 p-3" ${isMin ? 'style="background-color: rgba(76, 175, 80, 0.05); border-color: #4caf50 !important;"' : ''}>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <h6 class="text-xs font-weight-bold mb-0 text-dark">
                                    Ke Centroid ${cNum} (${topicsList[cIdx] || 'Cluster ' + cNum})
                                </h6>
                                <span class="badge ${isMin ? 'bg-success' : 'bg-secondary'} text-white text-xxs">
                                    Jarak: ${distanceValue} ${isMin ? '(TERKECIL - CLUSTER TERPILIH)' : ''}
                                </span>
                            </div>
                            <div class="row text-xxs">
                    `;

                    let sumSq = 0;
                    rolesMap.forEach(role => {
                        html += `<div class="col-md-3"><strong>Topik ${role.name}:</strong><br>`;
                        role.features.forEach(item => {
                            const val = kuesioner[item.f] !== undefined ? kuesioner[item.f] : 0;
                            const cVal = centroid[item.f] !== undefined ? parseFloat(centroid[item.f]) : 0;
                            const diff = val - cVal;
                            const diffSq = diff * diff;
                            sumSq += diffSq;
                            
                            html += `&bull; ${item.label}: (${val} - ${cVal.toFixed(3)})&sup2; = ${diffSq.toFixed(4)}<br>`;
                        });
                        html += `</div>`;
                    });

                    html += `
                            </div>
                            <div class="border-top pt-2 mt-2 text-xxs font-weight-bold text-dark d-flex justify-content-between">
                                <span>Jumlah Kuadrat Selisih (Sum of Squares): ${sumSq.toFixed(4)}</span>
                                <span>Akar Kuadrat (Jarak Euclidean): &radic;${sumSq.toFixed(4)} = ${Math.sqrt(sumSq).toFixed(4)}</span>
                            </div>
                        </div>
                    `;

                    euclideanContainer.innerHTML += html;
                });

                // Render PCA steps
                const tbodyPca = document.getElementById('detail-table-pca-steps');
                tbodyPca.innerHTML = '';

                const pcaParams = JSON.parse(this.getAttribute('data-pca-params'));
                if (pcaParams) {
                    const featuresList = pcaParams.features;
                    const means = pcaParams.means;
                    const stds = pcaParams.stds;
                    const v1 = pcaParams.v1;
                    const v2 = pcaParams.v2;
                    const flipPC1 = pcaParams.flipPC1;

                    const featureLabels = {
                        'a1': 'a1 (Minat - App Dev)',
                        'a2': 'a2 (Minat - Data Analyst)',
                        'a3': 'a3 (Minat - System Analyst)',
                        'a4': 'a4 (Minat - IT Auditor & Gov)',
                        'b1': 'b1 (Skill - App Dev)',
                        'b2': 'b2 (Skill - IT Auditor & Gov)',
                        'b3': 'b3 (Skill - Data Analyst)',
                        'b4': 'b4 (Skill - System Analyst)',
                        'd1': 'd1 (Nilai - Data Analyst)',
                        'd2': 'd2 (Nilai - System Analyst)',
                        'd3': 'd3 (Nilai - App Dev)',
                        'd4': 'd4 (Nilai - IT Auditor & Gov)'
                    };

                    let sumPC1Contribution = 0;
                    let sumPC2Contribution = 0;

                    featuresList.forEach((f, index) => {
                        const val = kuesioner[f] !== undefined ? kuesioner[f] : 0;
                        const mean = means[index] !== undefined ? parseFloat(means[index]) : 0;
                        const std = stds[index] !== undefined ? parseFloat(stds[index]) : 1;
                        const z = (val - mean) / std;
                        const loading1 = v1[index] !== undefined ? parseFloat(v1[index]) : 0;
                        const loading2 = v2[index] !== undefined ? parseFloat(v2[index]) : 0;
                        const cont1 = z * loading1;
                        const cont2 = z * loading2;

                        sumPC1Contribution += cont1;
                        sumPC2Contribution += cont2;

                        tbodyPca.innerHTML += `
                            <tr>
                                <td class="text-start font-weight-bold">${featureLabels[f] || f}</td>
                                <td>${val}</td>
                                <td>${mean.toFixed(3)}</td>
                                <td>${std.toFixed(3)}</td>
                                <td>${z.toFixed(3)}</td>
                                <td>${loading1.toFixed(3)}</td>
                                <td class="font-weight-bold text-primary">${cont1.toFixed(4)}</td>
                                <td>${loading2.toFixed(3)}</td>
                                <td class="font-weight-bold text-success">${cont2.toFixed(4)}</td>
                            </tr>
                        `;
                    });

                    const finalPC1 = sumPC1Contribution * flipPC1;

                    document.getElementById('detail-pca-summary').innerHTML = `
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="text-xs font-weight-bold text-primary mb-1">Perhitungan Sumbu PC1 (Horizontal)</h6>
                                <div>&bull; Total Kontribusi PC1 (Raw): <strong>${sumPC1Contribution.toFixed(4)}</strong></div>
                                <div>&bull; Sumbu Flip PC1 Factor: <strong>${flipPC1}</strong></div>
                                <div class="mt-1 text-xs">
                                    <strong>Hasil Akhir PC1:</strong> ${sumPC1Contribution.toFixed(4)} &times; (${flipPC1}) = <span class="badge bg-primary text-white font-weight-bold" style="font-size: 0.8rem;">${finalPC1.toFixed(3)}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-xs font-weight-bold text-success mb-1">Perhitungan Sumbu PC2 (Vertikal)</h6>
                                <div>&bull; Total Kontribusi PC2: <strong>${sumPC2Contribution.toFixed(4)}</strong></div>
                                <div class="mt-2 text-xs">
                                    <strong>Hasil Akhir PC2:</strong> <span class="badge bg-success text-white font-weight-bold" style="font-size: 0.8rem;">${sumPC2Contribution.toFixed(3)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    tbodyPca.innerHTML = '<tr><td colspan="9" class="text-center text-secondary">Data PCA tidak tersedia</td></tr>';
                    document.getElementById('detail-pca-summary').innerHTML = '';
                }

                // Show modal
                const myModal = new bootstrap.Modal(document.getElementById('modalDetailPerhitungan'));
                myModal.show();
            });
        });
    });
</script>
@endpush
