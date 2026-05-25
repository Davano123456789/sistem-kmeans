<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0 d-block text-center" href="#">
      <span class="font-weight-bold text-dark">Sistem K-Means</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">
  <div class="collapse navbar-collapse w-auto max-height-vh-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      @if(!str_contains(strtolower(auth()->user()->role), 'dosen'))
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('/') ? 'active bg-gradient-dark text-white' : '' }}" href="/">
          <div class="{{ Request::is('/') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">dashboard</i>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('import-excel*') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('import-excel.index') }}">
          <div class="{{ Request::is('import-excel*') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">upload_file</i>
          </div>
          <span class="nav-link-text ms-1">Import Excel</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('mahasiswa*') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('mahasiswa.index') }}">
          <div class="{{ Request::is('mahasiswa*') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">school</i>
          </div>
          <span class="nav-link-text ms-1">Data Mahasiswa</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('kmeans*') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('kmeans.index') }}">
          <div class="{{ Request::is('kmeans*') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">analytics</i>
          </div>
          <span class="nav-link-text ms-1">Proses K-Means</span>
        </a>
      </li>
      @endif
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('hasil-cluster*') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('kmeans.riwayat.index') }}">
          <div class="{{ Request::is('hasil-cluster*') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">assignment</i>
          </div>
          <span class="nav-link-text ms-1">Hasil Cluster</span>
        </a>
      </li>
      @if(!str_contains(strtolower(auth()->user()->role), 'dosen'))
      <li class="nav-item">
        <a class="nav-link text-dark {{ Request::is('pengguna*') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('pengguna.index') }}">
          <div class="{{ Request::is('pengguna*') ? 'text-white' : 'text-dark' }} text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">people</i>
          </div>
          <span class="nav-link-text ms-1">Manajemen User</span>
        </a>
      </li>
      @endif
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-8">Account pages</h6>
      </li>
      <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
          @csrf
        </form>
        <a class="nav-link text-dark" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
            <i class="material-icons-round opacity-10">logout</i>
          </div>
          <span class="nav-link-text ms-1">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
