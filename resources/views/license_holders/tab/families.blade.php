@php
    $addUrl = route('license_holder_families.create') . '?license_holder_id=' . $license_holder->id;
@endphp

<style>
    .holder-families .section-icon {
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
    .holder-families .fam-number {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--tblr-bg-surface-secondary, #f1f3f5);
        color: var(--tblr-secondary, #667382);
        font-size: .8125rem;
        font-weight: 600;
        flex-shrink: 0;
    }
    .holder-families .subheader-line {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--tblr-secondary, #667382);
        margin: 1rem 0 .5rem;
    }
    @media (max-width: 575.98px) {
        .holder-families .card-body { padding: 1rem; }
    }
</style>

<div class="holder-families">
    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 me-auto">
            <span class="section-icon"><i class="ti ti-users"></i></span>
            <div>
                <h3 class="mb-0">Data Keluarga</h3>
                <div class="text-muted small">{{ $license_holder->fullname }}</div>
            </div>
        </div>
        <a href="{{ $addUrl }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Data
        </a>
    </div>

    @if ($license_holder->families && $license_holder->families->count())
        <div class="row g-3">
            @foreach ($license_holder->families as $index => $fam)
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="fam-number">{{ $index + 1 }}</span>

                                <div class="flex-fill min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-blue-lt">{{ $fam->readable_relationship ?: '-' }}</span>
                                        <span class="badge bg-secondary-lt">{{ $fam->readable_gender ?: '-' }}</span>
                                    </div>

                                    <h4 class="mb-1 text-break">{{ $fam->name ?: '-' }}</h4>
                                    <div class="text-muted">
                                        <i class="ti ti-cake me-1"></i>{{ $fam->birth_date_formatted ?: '-' }}
                                    </div>

                                    {{-- Pekerjaan --}}
                                    <div class="subheader-line">Pekerjaan</div>
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Pekerjaan</div>
                                            <div class="datagrid-content">{{ $fam->job ?: '-' }}</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Telepon Kantor</div>
                                            <div class="datagrid-content">{{ $fam->job_phone ?: '-' }}</div>
                                        </div>
                                    </div>

                                    {{-- Pendidikan --}}
                                    <div class="subheader-line">Pendidikan</div>
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Pendidikan Terakhir</div>
                                            <div class="datagrid-content">{{ $fam->last_education_level ?: '-' }}</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Nama Sekolah</div>
                                            <div class="datagrid-content text-break">{{ $fam->institution_name ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('license_holder_families.edit', $fam->id) }}" class="btn btn-warning" title="Ubah">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </a>
                                <form action="{{ route('license_holder_families.destroy', $fam->id) }}" method="POST" class="d-grid d-sm-block" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="ti ti-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="ti ti-users-minus fs-1 d-block mb-2"></i>
                <div class="mb-3">Belum ada data keluarga.</div>
                <a href="{{ $addUrl }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah Data
                </a>
            </div>
        </div>
    @endif

    <div class="text-end mt-3">
        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
        </a>
    </div>
</div>