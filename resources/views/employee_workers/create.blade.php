@extends('tablar::page')

@section('content')
@php
    $backUrl = route('employees.show', $employee->id);
@endphp

<style>
    .work-form .section-icon {
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
    .work-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    @media (max-width: 575.98px) {
        .work-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Tambah Data Pekerjaan</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="d-flex">
                    <div class="me-2"><i class="ti ti-alert-circle fs-2"></i></div>
                    <div>
                        <h4 class="alert-title">Terdapat kesalahan pada isian form</h4>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('employee_workers.store') }}" method="POST" class="work-form">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-briefcase"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pekerjaan</h3>
                            <div class="text-muted small">Riwayat pekerjaan karyawan</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nama Karyawan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control" value="{{ $employee->fullname }}" disabled>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="company_name">Nama Lembaga</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-building"></i></span>
                                <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                            </div>
                            @error('company_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="last_position">Posisi</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-id-badge-2"></i></span>
                                <input type="text" id="last_position" name="last_position" class="form-control @error('last_position') is-invalid @enderror" value="{{ old('last_position') }}">
                            </div>
                            @error('last_position')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="last_salary">Gaji Terakhir</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="last_salary" name="last_salary" class="form-control @error('last_salary') is-invalid @enderror" value="{{ old('last_salary') }}" min="0" step="any" inputmode="numeric">
                            </div>
                            @error('last_salary')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="start_date">Tanggal Mulai Kerja</label>
                            <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="end_date">Tanggal Berakhir Kerja</label>
                            <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="reason_for_leaving">Alasan Keluar</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-door-exit"></i></span>
                                <input type="text" id="reason_for_leaving" name="reason_for_leaving" class="form-control @error('reason_for_leaving') is-invalid @enderror" value="{{ old('reason_for_leaving') }}">
                            </div>
                            @error('reason_for_leaving')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    // Tanggal berakhir tidak boleh lebih kecil dari tanggal mulai
    function syncDates() {
        const start = $('#start_date').val();
        $('#end_date').attr('min', start || '');
    }
    $('#start_date').on('input change', syncDates);
    syncDates();
});
</script>
@endpush