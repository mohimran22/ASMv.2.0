@php
    $statusColor = match (strtolower((string) $student->status)) {
        'aktif'  => 'green',
        'alumni' => 'blue',
        default  => 'secondary',
    };
@endphp

<style>
    .student-profile .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 1rem;
        flex-shrink: 0;
        font-size: 2.25rem;
    }
    .student-profile .section-icon {
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
    @media (max-width: 575.98px) {
        .student-profile .card-body { padding: 1rem; }
        .student-profile .profile-avatar { width: 72px; height: 72px; font-size: 1.75rem; }
    }
</style>

<div class="student-profile">

    {{-- Header profil --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-3">
                        @if ($student->photo)
                            <span class="avatar profile-avatar" style="background-image: url('{{ asset('storage/photos/' . $student->photo) }}')"></span>
                        @else
                            <span class="avatar profile-avatar bg-secondary-lt">{{ mb_strtoupper(mb_substr($student->fullname ?? '?', 0, 1)) }}</span>
                        @endif

                        <div class="min-w-0">
                            <h3 class="card-title fs-2 mb-1 text-break">{{ $student->fullname }}</h3>
                            <div class="text-muted mb-2">
                                @if($student->nickname)
                                    {{ $student->nickname }}
                                    <span class="mx-1">&middot;</span>
                                @endif
                                {{ $student->readable_gender ?: '-' }}
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($student->license?->name)
                                    <span class="badge bg-blue-lt">{{ $student->license->name }}</span>
                                @endif
                                @if($student->status)
                                    <span class="badge bg-{{ $statusColor }}-lt text-{{ $statusColor }}">{{ $student->status }}</span>
                                @endif
                                @if($student->nis)
                                    <span class="badge bg-secondary-lt">NIS: {{ $student->nis }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="col-12 col-md-auto">
                    <div class="d-grid gap-2 d-sm-flex">
                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i> Edit Profil
                        </a>
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Informasi Pribadi --}}
        <div class="col-12 col-xl-6">
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
                            <div class="datagrid-title">Lisensi</div>
                            <div class="datagrid-content">{{ $student->license->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">NIS</div>
                            <div class="datagrid-content">{{ $student->nis ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Agama</div>
                            <div class="datagrid-content">{{ $student->religion->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tempat Lahir</div>
                            <div class="datagrid-content">{{ $student->birth_place ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Lahir</div>
                            <div class="datagrid-content">{{ $student->birth_date_formatted ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor HP Siswa</div>
                            <div class="datagrid-content">{{ $student->student_phone ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Orang Tua --}}
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-users"></i></span>
                        <h3 class="card-title mb-0">Orang Tua</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Ayah</div>
                            <div class="datagrid-content">{{ $student->father_name ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor HP Ayah</div>
                            <div class="datagrid-content">{{ $student->father_phone ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Bunda</div>
                            <div class="datagrid-content">{{ $student->mother_name ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor HP Bunda</div>
                            <div class="datagrid-content">{{ $student->mother_phone ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Domisili --}}
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-map-2"></i></span>
                        <h3 class="card-title mb-0">Keterangan Domisili</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item" style="grid-column: 1 / -1;">
                            <div class="datagrid-title">Alamat Lengkap</div>
                            <div class="datagrid-content">{{ $student->address ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Provinsi</div>
                            <div class="datagrid-content">{{ $student->province->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kabupaten/Kota</div>
                            <div class="datagrid-content">{{ $student->city->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kecamatan</div>
                            <div class="datagrid-content">{{ $student->district->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Desa</div>
                            <div class="datagrid-content">{{ $student->subDistrict->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kode Pos</div>
                            <div class="datagrid-content">{{ $student->postalCode->postal_code ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Keterangan Tambahan --}}
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-school"></i></span>
                        <h3 class="card-title mb-0">Keterangan Tambahan</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Asal Sekolah</div>
                            <div class="datagrid-content">{{ $student->previous_school ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kelas</div>
                            <div class="datagrid-content">{{ $student->grade ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">
                                @if($student->status)
                                    <span class="badge bg-{{ $statusColor }}-lt text-{{ $statusColor }}">{{ $student->status }}</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tahu Darimana</div>
                            <div class="datagrid-content">{{ $student->readable_where_know ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Pendaftaran</div>
                            <div class="datagrid-content">{{ $student->registered_date_formatted ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>