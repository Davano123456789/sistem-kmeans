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
                        <h6 class="text-sm font-weight-bold">Pengaturan Cluster</h6>
                        <p class="text-xs text-secondary">Tentukan jumlah cluster dan pusat awalnya.</p>
                        
                        @php
                            $k = $k_jumlah ?? 4;
                            $topics = $nama_clusters ?? [
                                'Application Developer',
                                'Data Analyst',
                                'System Analyst',
                                'IT Auditor & Governance'
                            ];
                        @endphp

                        <div class="mb-4">
                            <label class="form-label text-xs font-weight-bold">Jumlah Cluster (K)</label>
                            <select name="jumlah_cluster" id="jumlah_cluster" class="form-select border px-3" required>
                                <option value="2" {{ $k == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $k == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $k == 4 ? 'selected' : '' }}>4</option>
                            </select>
                        </div>

                        <div id="centroid-container">
                            @for($i = 1; $i <= $k; $i++)
                            <div class="centroid-item mb-3 p-3 border border-radius-md bg-gray-50">
                                <h6 class="text-xs font-weight-bold mb-2">Cluster {{ $i }}</h6>
                                
                                <div class="mb-2">
                                    <label class="form-label text-xs">Nama Cluster / Topik</label>
                                    <select name="nama_clusters[]" class="form-select border px-2 py-1 text-sm" required>
                                        @php
                                            $defaultOptions = [
                                                'Application Developer',
                                                'Data Analyst',
                                                'System Analyst',
                                                'IT Auditor & Governance'
                                            ];
                                            $currentTopic = $topics[$i-1] ?? $defaultOptions[$i-1] ?? 'Application Developer';
                                        @endphp
                                        @foreach($defaultOptions as $opt)
                                            <option value="{{ $opt }}" {{ $currentTopic == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label text-xs">Centroid Awal</label>
                                    <select name="centroids[]" class="select-search" required>
                                        <option value="">-- Pilih Mahasiswa --</option>
                                        @foreach($mahasiswa as $mhs)
                                            <option value="{{ $mhs->id_mahasiswa }}" 
                                                {{ (isset($selectedCentroids) && ($selectedCentroids[$i-1] ?? null) == $mhs->id_mahasiswa) || old('centroids.' . ($i-1)) == $mhs->id_mahasiswa ? 'selected' : '' }}>
                                                {{ $mhs->nama }}{{ $mhs->npm ? ' (' . $mhs->npm . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @endfor
                        </div>

                        <template id="mahasiswa-options">
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach($mahasiswa as $mhs)
                                <option value="{{ $mhs->id_mahasiswa }}">{{ $mhs->nama }}{{ $mhs->npm ? ' (' . $mhs->npm . ')' : '' }}</option>
                            @endforeach
                        </template>
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
</div>

@if(isset($history))
<div class="row mt-4" id="hasil-cluster">
    <div class="col-12">
        <div class="card">
            <div class="card-header p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h5 class="mb-0">Hasil Perhitungan K-Means</h5>
                    <p class="text-sm mb-0">
                        Status: {!! $converged ? '<span class="badge badge-sm bg-gradient-success">Konvergen</span>' : '<span class="badge badge-sm bg-gradient-warning">Mencapai Batas Iterasi</span>' !!}
                    </p>
                </div>
                <div class="mt-3 mt-md-0" style="max-width: 450px; width: 100%;">
                    <form action="{{ route('kmeans.simpan') }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="jumlah_cluster" value="{{ $k_jumlah }}">
                        @foreach($selectedCentroids as $cId)
                            <input type="hidden" name="centroids[]" value="{{ $cId }}">
                        @endforeach
                        @foreach($nama_clusters ?? [] as $clusterName)
                            <input type="hidden" name="nama_clusters[]" value="{{ $clusterName }}">
                        @endforeach
                        <div class="input-group input-group-outline is-filled my-0" style="flex-grow: 1;">
                            <label class="form-label">Nama Riwayat</label>
                            <input type="text" name="nama_riwayat" class="form-control" value="Hasil Cluster - {{ date('d-m-Y') }}" required style="height: 38px;">
                        </div>
                        <button type="submit" class="btn btn-success mb-0 d-flex align-items-center gap-1 text-nowrap" style="height: 38px;">
                            <i class="material-icons text-md">save</i> Simpan Riwayat
                        </button>
                    </form>
                </div>
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
                                        @php
                                            $displayFeatures = isset($features) ? $features : ['a1','a2','a3','a4','b1','b2','b3','b4','d1','d2','d3','d4'];
                                        @endphp
                                        @foreach($displayFeatures as $f)
                                        <th class="text-uppercase">{{ substr($f, 0, 1) . '-' . substr($f, 1) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($h['centroids'] as $cIdx => $cVals)
                                    <tr>
                                        <td class="font-weight-bold text-start">
                                            {{ $h['iterasi'] == 1 
                                                ? (isset($selectedCentroids[$cIdx]) && ($m = $mahasiswa->firstWhere('id_mahasiswa', $selectedCentroids[$cIdx])) ? $m->nama : $topics[$cIdx]) 
                                                : 'C' . ($cIdx + 1) }}
                                        </td>
                                        @foreach($displayFeatures as $f)
                                        <td>{{ number_format($cVals[$f] ?? 0, 2) }}</td>
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
                                          <th class="text-center text-uppercase text-secondary font-weight-bolder opacity-7" style="width: 5%">No</th>
                                          <th class="text-uppercase text-secondary font-weight-bolder opacity-7">Mahasiswa</th>
                                          @foreach($topics as $t)
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
                                      @foreach($h['results'] as $res)
                                      <tr {!! ($res['moved'] ?? false) ? 'style="background-color: rgba(251, 140, 0, 0.08) !important;"' : '' !!}>
                                          <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                          <td class="px-4">
                                              {{ $res['mahasiswa']->nama }}
                                              @if($res['moved'] ?? false)
                                                  <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem; padding: 2px 5px;" title="Pindah cluster dari iterasi sebelumnya">Pindah</span>
                                              @endif
                                          </td>
                                         @foreach($res['distances'] as $dist)
                                         <td class="text-center">{{ number_format($dist, 4) }}</td>
                                         @endforeach
                                         <td class="text-center font-weight-bold">{{ number_format($res['min_distance'], 4) }}</td>
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
                                                 data-centroids="{{ json_encode($h['centroids']) }}"
                                                 data-pca-params="{{ json_encode($h['pca_parameters']) }}"
                                                 title="Lihat Detail Perhitungan">
                                                 <i class="material-icons text-md">calculate</i>
                                             </button>
                                         </td>
                                     </tr>
                                     @endforeach
                                 </tbody>
                            </table>
                        </div>
                        <h6 class="font-weight-bold px-2 mt-4">Anggota Kelompok (Cluster)</h6>
                        <div class="row mb-4">
                            @foreach($h['clusters'] as $cIdx => $members)
                            <div class="col-md-4 col-lg-3">
                                <div class="card shadow-none border">
                                    @php $colorClasses = ['primary', 'info', 'success', 'warning', 'danger', 'secondary', 'dark', 'light', 'primary', 'info']; @endphp
                                    <div class="card-header p-2 bg-gradient-{{ $colorClasses[$cIdx % 10] }} text-white text-center">
                                        <h6 class="text-white mb-0 text-xs">{{ $topics[$cIdx] ?? ('Cluster ' . ($cIdx + 1)) }}</h6>
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
            'rgba(251, 140, 0, 1)',   // Warning - Orange
            'rgba(244, 67, 54, 1)',   // Danger - Red
            'rgba(158, 158, 158, 1)', // Secondary - Grey
            'rgba(52, 71, 103, 1)',   // Dark - Blueish Dark
            'rgba(173, 181, 189, 1)', // Light Grey
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

        @foreach($history as $h)
        (function() {
            const ctx = document.getElementById('pcaChart-{{ $h['iterasi'] }}').getContext('2d');
            
            const datasets = [];

            // 1. Kelompokkan titik mahasiswa per cluster
            const clusterPoints = Array.from({length: k}, () => []);
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
            for (let c = 0; c < k; c++) {
                let colorIndex = c % 10;
                // A. Garis Batas Convex Hull (jika minimal 3 titik)
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
                        datalabels: { display: false } // Sembunyikan label nama pada garis hull
                    });
                }

                // B. Scatter Points Mahasiswa
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

                // C. Titik Centroid PCA (KOTAK BESAR)
                if (centroids[c]) {
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
@endif
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Styling adjustment for Tom Select to match Material Dashboard */
    .ts-wrapper {
        width: 100% !important;
    }
    .ts-control {
        border: 1px solid #d2d2d2 !important;
        border-radius: 0.375rem !important;
        padding: 8px 12px !important;
        background-color: #fff !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        font-size: 0.875rem !important;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #000 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.15) !important;
    }
    .ts-dropdown {
        border-radius: 0.375rem !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06) !important;
        font-size: 0.875rem !important;
        z-index: 1050 !important;
    }
    .ts-dropdown .active {
        background-color: #262626 !important;
        color: #fff !important;
    }
    .ts-dropdown .option {
        padding: 8px 12px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.select-search').forEach(el => {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });

        // Handle Jumlah Cluster change
        document.getElementById('jumlah_cluster').addEventListener('change', function() {
            let k = parseInt(this.value);
            if (k < 2) return;
            
            let container = document.getElementById('centroid-container');
            let currentItems = container.querySelectorAll('.centroid-item').length;
            let optionsTemplate = document.getElementById('mahasiswa-options').innerHTML;
            
            const defaultOptions = [
                'Application Developer',
                'Data Analyst',
                'System Analyst',
                'IT Auditor & Governance'
            ];
            
            if (k > currentItems) {
                // Add new items
                for (let i = currentItems + 1; i <= k; i++) {
                    let div = document.createElement('div');
                    div.className = 'centroid-item mb-3 p-3 border border-radius-md bg-gray-50';
                    
                    let selectTopicOptions = '';
                    defaultOptions.forEach((opt, idx) => {
                        let selected = (idx === i - 1) ? 'selected' : '';
                        selectTopicOptions += `<option value="${opt}" ${selected}>${opt}</option>`;
                    });

                    div.innerHTML = `
                        <h6 class="text-xs font-weight-bold mb-2">Cluster ${i}</h6>
                        <div class="mb-2">
                            <label class="form-label text-xs">Nama Cluster / Topik</label>
                            <select name="nama_clusters[]" class="form-select border px-2 py-1 text-sm" required>
                                ${selectTopicOptions}
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-xs">Centroid Awal</label>
                            <select name="centroids[]" class="select-search-dynamic" required>
                                ${optionsTemplate}
                            </select>
                        </div>
                    `;
                    container.appendChild(div);
                    new TomSelect(div.querySelector('.select-search-dynamic'), {
                        create: false,
                        sortField: {
                            field: "text",
                            direction: "asc"
                        }
                    });
                }
            } else if (k < currentItems) {
                // Remove items
                let items = container.querySelectorAll('.centroid-item');
                for (let i = currentItems - 1; i >= k; i--) {
                    items[i].remove();
                }
            }
        });
    });
</script>
@endpush
