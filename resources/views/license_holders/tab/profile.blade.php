@php
    // Closure (bukan function) agar aman dimuat berulang lewat AJAX
    $badgeColor = fn ($value) => $value === 'Lancar' ? 'green' : 'yellow';
    $licenseNames = $license_holder->user->licenses->pluck('name');
@endphp

<style>
    .holder-profile .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 1rem;
        flex-shrink: 0;
        font-size: 2.25rem;
    }
    .holder-profile .section-icon {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        background: rgba(32, 107, 196, .1);
        color: #206bc4;
        flex-shrink: 0;
    }
    .holder-profile .ktp-frame {
        display: block;
        width: 100%;
        border-radius: .75rem;
        overflow: hidden;
        border: 1px solid var(--tblr-border-color, #dadfe5);
    }
    .holder-profile .ktp-frame img {
        display: block;
        width: 100%;
        height: auto;
    }
    .holder-profile .lang-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .6rem .85rem;
        border: 1px solid var(--tblr-border-color, #e6e7e9);
        border-radius: .5rem;
    }
    @media (max-width: 575.98px) {
        .holder-profile .card-body { padding: 1rem; }
        .holder-profile .profile-avatar { width: 72px; height: 72px; font-size: 1.75rem; }
    }
</style>

<div class="holder-profile">

    {{-- Header profil --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        @if ($license_holder->photo)
                            <span class="avatar profile-avatar" style="background-image: url('{{ asset('storage/photos/' . $license_holder->photo) }}')"></span>
                        @else
                            <span class="avatar profile-avatar bg-secondary-lt">{{ mb_strtoupper(mb_substr($license_holder->fullname ?? '?', 0, 1)) }}</span>
                        @endif

                        <div class="min-w-0">
                            <h3 class="card-title fs-2 mb-1 text-break">{{ $license_holder->fullname }}</h3>
                            @if(!empty($license_holder->nickname))
                                <div class="text-muted mb-2">{{ $license_holder->nickname }}</div>
                            @endif
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($licenseNames as $licenseName)
                                    <span class="badge bg-blue-lt">{{ $licenseName }}</span>
                                @empty
                                    <span class="text-muted small">Belum ada lisensi</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="col-12 col-md-auto">
                    <div class="d-grid gap-2 d-sm-flex">
                        <a href="{{ route('license_holders.edit', $license_holder->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i> Edit Profil
                        </a>
                        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Informasi Pribadi --}}
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-id-badge-2"></i></span>
                        <h3 class="card-title mb-0">Informasi Pribadi</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Telepon</div>
                            <div class="datagrid-content">{{ $license_holder->phone ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Agama</div>
                            <div class="datagrid-content">{{ $license_holder->religion->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor KTP</div>
                            <div class="datagrid-content">{{ $license_holder->identity_number ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor SIM</div>
                            <div class="datagrid-content">{{ $license_holder->driver_license_number ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tempat Lahir</div>
                            <div class="datagrid-content">{{ $license_holder->birth_place ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Lahir</div>
                            <div class="datagrid-content">{{ $license_holder->birth_date_formatted ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Hobi</div>
                            <div class="datagrid-content">{{ $license_holder->hobby ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item" style="grid-column: 1 / -1;">
                            <div class="datagrid-title">Alamat</div>
                            <div class="datagrid-content">{{ $license_holder->address ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Foto KTP --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-id"></i></span>
                        <h3 class="card-title mb-0">Foto KTP</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if ($license_holder->identity_photo)
                        <a href="{{ asset('storage/photos/' . $license_holder->identity_photo) }}" target="_blank" class="ktp-frame" title="Buka ukuran penuh">
                            <img src="{{ asset('storage/photos/' . $license_holder->identity_photo) }}" alt="Foto KTP" loading="lazy">
                        </a>
                        <div class="form-hint mt-2">Klik gambar untuk membuka ukuran penuh.</div>
                    @else
                        <div class="text-muted text-center py-4">
                            <i class="ti ti-file-off fs-1 d-block mb-1"></i>
                            Belum ada foto KTP
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pernikahan --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-heart-handshake"></i></span>
                        <h3 class="card-title mb-0">Pernikahan</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">{{ $license_holder->readable_marital_status ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Nikah</div>
                            <div class="datagrid-content">{{ $license_holder->married_date_formatted ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kemampuan Bahasa --}}
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-language"></i></span>
                        <h3 class="card-title mb-0">Kemampuan Bahasa</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($license_holder->readable_languages as $label => $value)
                            <div class="col-12 col-md-6">
                                <div class="lang-item">
                                    <span class="text-muted">{{ $label }}</span>
                                    <span class="badge bg-{{ $badgeColor($value) }}-lt text-{{ $badgeColor($value) }}">
                                        {{ $value ?: '-' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>