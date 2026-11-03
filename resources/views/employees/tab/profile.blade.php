@php
    // Posisi bisa berupa array, JSON string, atau kosong (fallback ke role)
    $rawPosition = $employee->position;
    if (!is_array($rawPosition)) {
        $decoded = json_decode($rawPosition ?? '', true);
        $rawPosition = is_array($decoded) ? $decoded : ($rawPosition ? [$rawPosition] : []);
    }
    $positionText = count($rawPosition)
        ? implode(', ', $rawPosition)
        : (auth()->user()->getRoleNames()->first() ?? '');

    $licenseNames = $employee->licenses->pluck('name');
@endphp

<style>
    .employee-profile .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 1rem;
        flex-shrink: 0;
        font-size: 2.25rem;
    }
    .employee-profile .section-icon {
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
    .employee-profile .ktp-frame {
        display: block;
        width: 100%;
        border-radius: .75rem;
        overflow: hidden;
        border: 1px solid var(--tblr-border-color, #dadfe5);
    }
    .employee-profile .ktp-frame img {
        display: block;
        width: 100%;
        height: auto;
    }
    @media (max-width: 575.98px) {
        .employee-profile .card-body { padding: 1rem; }
        .employee-profile .profile-avatar { width: 72px; height: 72px; font-size: 1.75rem; }
    }
</style>

<div class="employee-profile">

    {{-- Header profil --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        @if ($employee->photo)
                            <span class="avatar profile-avatar" style="background-image: url('{{ asset('storage/' . $employee->photo) }}')"></span>
                        @else
                            <span class="avatar profile-avatar bg-secondary-lt">{{ mb_strtoupper(mb_substr($employee->fullname ?? '?', 0, 1)) }}</span>
                        @endif

                        <div class="min-w-0">
                            <h3 class="card-title fs-2 mb-1 text-break">{{ $employee->fullname }}</h3>
                            @if($positionText)
                                <div class="text-muted mb-2">{{ $positionText }}</div>
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
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i> Edit Profil
                        </a>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
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
                            <div class="datagrid-content">{{ $employee->phone ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Agama</div>
                            <div class="datagrid-content">{{ $employee->religion->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor KTP</div>
                            <div class="datagrid-content">{{ $employee->identity_number ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tempat Lahir</div>
                            <div class="datagrid-content">{{ $employee->birth_place ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Lahir</div>
                            <div class="datagrid-content">{{ $employee->birth_date_formatted ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item" style="grid-column: 1 / -1;">
                            <div class="datagrid-title">Alamat</div>
                            <div class="datagrid-content">{{ $employee->address ?: '-' }}</div>
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
                    @if ($employee->identity_photo)
                        <a href="{{ asset('storage/' . $employee->identity_photo) }}" target="_blank" class="ktp-frame" title="Buka ukuran penuh">
                            <img src="{{ asset('storage/' . $employee->identity_photo) }}" alt="Foto KTP" loading="lazy">
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
        <div class="col-12">
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
                            <div class="datagrid-content">{{ $employee->readable_marital_status ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Nikah</div>
                            <div class="datagrid-content">{{ $employee->married_date_formatted ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>