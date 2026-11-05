@extends('tablar::page')

@section('content')
@php
    $levels = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'];
    $relationships = [
        '1' => 'Suami',
        '2' => 'Istri',
        '3' => 'Anak',
        '4' => 'Orang Tua',
        '5' => 'Famili Lain',
    ];
    $genders = ['1' => 'Laki - Laki', '2' => 'Perempuan'];
    $backUrl = route('employees.show', $employee->id);
@endphp

<style>
    .family-form .section-icon {
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
    .family-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    @media (max-width: 575.98px) {
        .family-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Tambah Data Keluarga</h2>
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

        <form action="{{ route('employee_families.store') }}" method="POST" class="family-form">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-users-group"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Keluarga</h3>
                            <div class="text-muted small">Anggota keluarga karyawan</div>
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
                            <label class="form-label required" for="name">Nama Lengkap</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user-heart"></i></span>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="relationship">Hubungan dengan Karyawan</label>
                            <select name="relationship" id="relationship" class="form-select select2 @error('relationship') is-invalid @enderror">
                                <option value="">-- Pilih salah satu --</option>
                                @foreach($relationships as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('relationship') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('relationship')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="gender">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-select select2 @error('gender') is-invalid @enderror">
                                <option value="">-- Pilih jenis kelamin --</option>
                                @foreach($genders as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('gender') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('gender')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="job">Pekerjaan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-briefcase"></i></span>
                                <input type="text" id="job" name="job" class="form-control @error('job') is-invalid @enderror" value="{{ old('job') }}">
                            </div>
                            @error('job')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="company_name">Nama Perusahaan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-building"></i></span>
                                <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}">
                            </div>
                            @error('company_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="job_phone">Telepon Kantor</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" id="job_phone" name="job_phone" class="form-control @error('job_phone') is-invalid @enderror" value="{{ old('job_phone') }}" inputmode="tel">
                            </div>
                            @error('job_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="last_education_level">Jenjang Pendidikan</label>
                            <select name="last_education_level" id="last_education_level" class="form-select select2 @error('last_education_level') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}" @selected(old('last_education_level') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('last_education_level')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="institution_name">Nama Sekolah</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-school"></i></span>
                                <input type="text" id="institution_name" name="institution_name" class="form-control @error('institution_name') is-invalid @enderror" value="{{ old('institution_name') }}">
                            </div>
                            @error('institution_name')
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
    $('.select2').select2({ width: '100%' });
});
</script>
@endpush