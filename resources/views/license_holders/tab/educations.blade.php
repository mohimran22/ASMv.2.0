@php
    $addUrl = route('license_holder_educations.create') . '?license_holder_id=' . $license_holder->id;

    // Hijau jika lulus, kuning jika belum/tidak lulus
    $graduatedColor = function ($text) {
        $t = strtolower((string) $text);
        return (str_contains($t, 'belum') || str_contains($t, 'tidak')) ? 'yellow' : 'green';
    };
@endphp

<style>
    .holder-educations .section-icon {
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
    .holder-educations .edu-number {
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
    @media (max-width: 575.98px) {
        .holder-educations .card-body { padding: 1rem; }
    }
</style>

<div class="holder-educations">
    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 me-auto">
            <span class="section-icon"><i class="ti ti-school"></i></span>
            <div>
                <h3 class="mb-0">Riwayat Pendidikan</h3>
                <div class="text-muted small">{{ $license_holder->fullname }}</div>
            </div>
        </div>
        <a href="{{ $addUrl }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Data
        </a>
    </div>

    @if ($license_holder->educations && $license_holder->educations->count())
        <div class="row g-3">
            @foreach ($license_holder->educations as $index => $edu)
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="edu-number">{{ $index + 1 }}</span>

                                <div class="flex-fill min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-blue-lt">{{ $edu->education_level ?: '-' }}</span>
                                        <span class="badge bg-{{ $graduatedColor($edu->readable_is_graduated) }}-lt text-{{ $graduatedColor($edu->readable_is_graduated) }}">
                                            {{ $edu->readable_is_graduated ?: '-' }}
                                        </span>
                                    </div>

                                    <h4 class="mb-1 text-break">{{ $edu->institution_name ?: '-' }}</h4>
                                    <div class="text-muted mb-3">
                                        <i class="ti ti-book-2 me-1"></i>{{ $edu->major ?: 'Tanpa jurusan' }}
                                    </div>

                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Tahun Masuk</div>
                                            <div class="datagrid-content">{{ $edu->start_year ?: '-' }}</div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Tahun Lulus</div>
                                            <div class="datagrid-content">{{ $edu->end_year ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('license_holder_educations.edit', $edu->id) }}" class="btn btn-warning" title="Ubah">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </a>
                                <form action="{{ route('license_holder_educations.destroy', $edu->id) }}" method="POST" class="d-grid d-sm-block" onsubmit="return confirmDelete(event)">
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
                <i class="ti ti-school-off fs-1 d-block mb-2"></i>
                <div class="mb-3">Belum ada data pendidikan.</div>
                <a href="{{ $addUrl }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah Data
                </a>
            </div>
        </div>
    @endif

    <div class="text-end mt-3">
        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>