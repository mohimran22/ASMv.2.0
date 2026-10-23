<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Tambahkan di header.blade.php atau di layout master -->
        

        <div class="navbar-nav flex-row order-md-last">
            <div class="d-none d-md-flex">
                @include('tablar::partials.header.notifications')
                {{-- @include('tablar::partials.header.theme-mode') --}}
                 {{-- Tambahkan dropdown switch license di sini --}}
                @role('Pemilik Lisensi|Karyawan|Akuntan') {{-- selain Super Admin --}}
                    @php
                        $user = Auth::user();

                        if ($user->hasRole('Pemilik Lisensi')) {
                            $licenses = $user->licenses;
                        } elseif ($user->hasRole(['Karyawan', 'Akuntan'])) {
                            $licenses = $user->employee?->licenses ?? collect();
                        } else {
                            $licenses = collect();
                        }

                        $activeLicense = session('active_license_name', $licenses->first()->name ?? 'Pilih Lisensi');
                    @endphp

                    <div class="nav-item dropdown me-2">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-2" data-bs-toggle="dropdown" aria-label="Switch License">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-building"></i>
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ $activeLicense }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            @forelse($licenses as $license)
                                <a class="dropdown-item {{ session('active_license_id') == $license->id ? 'active fw-normal text-white' : '' }}" 
                                href="{{ route('switch.license', $license->id) }}">
                                    @if(session('active_license_id') == $license->id)
                                        <i class="ti ti-check me-1"></i>
                                    @endif
                                    {{ $license->name }}
                                </a>
                            @empty
                                <span class="dropdown-item text-muted">Tidak ada lisensi</span>
                            @endforelse
                        </div>
                    </div>
                @endrole
                @include('tablar::partials.header.top-right')      
            </div>
            
        </div>
        <div class="collapse navbar-collapse" id="navbar-menu">
            
        </div>
    </div>
</header>
