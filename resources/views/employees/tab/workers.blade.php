@php
    $addUrl  = route('employee_workers.create') . '?employee_id=' . $employee->id;
    $canAdd  = auth()->user()->can('pekerjaan-karyawan.tambah');
    $canEdit = auth()->user()->can('pekerjaan-karyawan.ubah');

    // Format gaji ke Rupiah jika berupa angka
    $rupiah = function ($value) {
        if ($value === null || $value === '') return '-';
        return is_numeric($value) ? 'Rp ' . number_format((float) $value, 0, ',', '.') : $value;
    };
@endphp

<style>
    .employee-workers .section-icon {
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
    .employee-workers .work-number {
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
    .employee-workers .subheader-line {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--tblr-secondary, #667382);
        margin: 1rem 0 .5rem;
    }
    .employee-workers .work-text {
        white-space: pre-line;
        word-break: break-word;
    }
    @media (max-width: 575.98px) {
        .employee-workers .card-body { padding: 1rem; }
    }
</style>

<div class="employee-workers">
    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 me-auto">
            <span class="section-icon"><i class="ti ti-briefcase"></i></span>
            <div>
                <h3 class="mb-0">Riwayat Pekerjaan</h3>
                <div class="text-muted small">{{ $employee->fullname }}</div>
            </div>
        </div>
        @if ($canAdd)
            <a href="{{ $addUrl }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Data
            </a>
        @endif
    </div>

    @if ($employee->workers && $employee->workers->count())
        <div class="row g-3">
            @foreach ($employee->workers as $index => $work)
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <span class="work-number">{{ $index + 1 }}</span>

                                <div class="flex-fill min-w-0">
                                    <h4 class="mb-1 text-break">{{ $work->last_position ?: '-' }}</h4>
                                    <div class="text-muted mb-2">
                                        <i class="ti ti-building me-1"></i>{{ $work->company_name ?: '-' }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="ti ti-calendar me-1"></i>
                                        {{ $work->start_date_formatted ?: '-' }} &ndash; {{ $work->end_date_formatted ?: '-' }}
                                    </div>

                                    <div class="subheader-line">Gaji Terakhir</div>
                                    <div class="fw-bold">{{ $rupiah($work->last_salary) }}</div>

                                    <div class="subheader-line">Alasan Keluar</div>
                                    <div class="work-text">{{ $work->reason_for_leaving ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                @if ($canEdit)
                                    <a href="{{ route('employee_workers.edit', $work->id) }}" class="btn btn-warning" title="Ubah">
                                        <i class="ti ti-edit me-1"></i> Ubah
                                    </a>
                                @endif
                                <form action="{{ route('employee_workers.destroy', $work->id) }}" method="POST" class="d-grid d-sm-block" onsubmit="return confirmDelete(event)">
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
                <div class="{{ $canAdd ? 'mb-3' : '' }}">Belum ada data pekerjaan.</div>
                @if ($canAdd)
                    <a href="{{ $addUrl }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Tambah Data
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="text-end mt-3">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
        </a>
    </div>
</div>