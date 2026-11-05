{{-- Penting --}}
@extends('tablar::page')

@section('content')
@php
    $levels = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'];
    $backUrl = route('license_holders.show', $license_holder->id);
@endphp

<style>
    .education-form .section-icon {
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
    .education-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    @media (max-width: 575.98px) {
        .education-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Ubah Data Pendidikan</h2>
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

        <form action="{{ route('license_holder_educations.update', $education->id) }}" method="POST" class="education-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="license_holder_id" value="{{ $license_holder->id }}">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-school"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pendidikan</h3>
                            <div class="text-muted small">Riwayat pendidikan pemilik lisensi</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nama Pemilik Lisensi</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control" value="{{ $license_holder->fullname }}" disabled>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="education_level">Jenjang Pendidikan</label>
                            <select name="education_level" id="education_level" class="form-select select2 @error('education_level') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}" {{ old('education_level', $education->education_level) === $level ? 'selected' : '' }}>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('education_level')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="institution_name">Nama Sekolah</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-building"></i></span>
                                <input type="text" id="institution_name" name="institution_name" class="form-control @error('institution_name') is-invalid @enderror" value="{{ old('institution_name', $education->institution_name) }}" required>
                            </div>
                            @error('institution_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="major">Jurusan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-book-2"></i></span>
                                <input type="text" id="major" name="major" class="form-control @error('major') is-invalid @enderror" value="{{ old('major', $education->major) }}">
                            </div>
                            @error('major')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="start_year">Tahun Masuk</label>
                            <input type="number" id="start_year" name="start_year" class="form-control @error('start_year') is-invalid @enderror" value="{{ old('start_year', $education->start_year) }}" min="1900" max="{{ date('Y') + 10 }}" inputmode="numeric" required>
                            @error('start_year')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="end_year">Tahun Lulus</label>
                            <input type="number" id="end_year" name="end_year" class="form-control @error('end_year') is-invalid @enderror" value="{{ old('end_year', $education->end_year) }}" min="1900" max="{{ date('Y') + 10 }}" inputmode="numeric">
                            @error('end_year')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="is_graduated">Status</label>
                            <select name="is_graduated" id="is_graduated" class="form-select select2">
                                <option value="1" {{ (string) old('is_graduated', $education->is_graduated ? '1' : '0') === '1' ? 'selected' : '' }}>Lulus</option>
                                <option value="0" {{ (string) old('is_graduated', $education->is_graduated ? '1' : '0') === '0' ? 'selected' : '' }}>Belum Lulus</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
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
    function syncYears() {
        const start = $('#start_year').val();
        $('#end_year').attr('min', start || 1900);
    }
    $('#start_year').on('input change', syncYears);
    syncYears();
});
</script>
@endpush