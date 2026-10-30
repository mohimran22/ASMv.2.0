@php
    $addUrl = route('license_holder_workers.create') . '?license_holder_id=' . $license_holder->id;

    // Hijau jika masih bekerja ("Ya"), abu-abu jika tidak
    $currentColor = function ($text) {
        return str_starts_with(strtolower(trim((string) $text)), 'ya') ? 'green' : 'secondary';
    };
@endphp

<style>
    .holder-workers .section-icon {
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
    .holder-workers .work-number {
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
    .holder-workers .subheader-line {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--tblr-secondary, #667382);
        margin: 1rem 0 .5rem;
    }
    .holder-workers .work-text {
        white-space: pre-line;
        word-break: break-word;
    }
    @media (max-width: 575.98px) {
        .holder-workers .card-body { padding: 1rem; }
    }
</style>

<div class="holder-workers">
    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 me-auto">
            <span class="section-icon"><i class="ti ti-briefcase"></i></span>
            <div>
                <h3 class="mb-0">Riwayat Pekerjaan</h3>
                <div class="text-muted small">{{ $license_holder->fullname }}</div>
            </div>
        </div>
        <a href="{{ $addUrl }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Data
        </a>
    </div>

    @if ($license_holder->workers && $license_holder->workers->count())
        <div class="row g-3">
            @foreach ($license_holder->workers as $index => $work)
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="work-number">{{ $index + 1 }}</span>

                                <div class="flex-fill min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-blue-lt">{{ $work->readable_employment_type ?: '-' }}</span>
                                        <span class="badge bg-{{ $currentColor($work->readable_is_current) }}-lt text-{{ $currentColor($work->readable_is_current) }}">
                                            Masih kerja: {{ $work->readable_is_current ?: '-' }}
                                        </span>
                                    </div>

                                    <h4 class="mb-1 text-break">{{ $work->position ?: '-' }}</h4>
                                    <div class="text-muted mb-2">
                                        <i class="ti ti-building me-1"></i>{{ $work->company_name ?: '-' }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="ti ti-calendar me-1"></i>
                                        {{ $work->start_date_formatted ?: '-' }} &ndash; {{ $work->end_date_formatted ?: '-' }}
                                    </div>

                                    {{-- Perusahaan --}}
                                    <div class="subheader-line">Perusahaan</div>
                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Kota</div>
                                            <div class="datagrid-content">{{ $work->city ?: '-' }}</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Telepon</div>
                                            <div class="datagrid-content">{{ $work->phone ?: '-' }}</div>
                                        </div>
                                    </div>

                                    {{-- Kompetensi & tanggung jawab --}}
                                    <div class="subheader-line">Kompetensi Utama</div>
                                    <div class="work-text">{{ $work->skills_used ?: '-' }}</div>

                                    <div class="subheader-line">Deskripsi Tanggung Jawab</div>
                                    <div class="work-text">{{ $work->job_description ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('license_holder_workers.edit', $work->id) }}" class="btn btn-warning" title="Ubah">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </a>
                                <form action="{{ route('license_holder_workers.destroy', $work->id) }}" method="POST" class="d-grid d-sm-block" onsubmit="return confirmDelete(event)">
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
                <i class="ti ti-briefcase-off fs-1 d-block mb-2"></i>
                <div class="mb-3">Belum ada data pekerjaan.</div>
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