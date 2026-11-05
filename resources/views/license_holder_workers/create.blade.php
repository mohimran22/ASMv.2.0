{{-- Penting --}}
@extends('tablar::page')

@section('content')
@php
    $employmentTypes = [1 => 'Penuh Waktu', 2 => 'Sampingan', 3 => 'Magang', 4 => 'Pekerja Lepas'];
    $backUrl = route('license_holders.show', $license_holder->id);
@endphp

<style>
    .worker-form .section-icon {
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
    .worker-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .worker-form .subheader-line {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--tblr-secondary, #667382);
        margin: .5rem 0 0;
    }
    @media (max-width: 575.98px) {
        .worker-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <a href="{{ $backUrl }}" class="btn btn-icon btn-primary me-2" aria-label="Kembali">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                    <h2 class="page-title mb-0">Tambah Data Pekerjaan</h2>
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

        <form action="{{ route('license_holder_workers.store') }}" method="POST" class="worker-form">
            @csrf
            <input type="hidden" name="license_holder_id" value="{{ $license_holder->id }}">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-briefcase"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pekerjaan</h3>
                            <div class="text-muted small">Riwayat pekerjaan pemilik lisensi</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Pemilik Lisensi</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control" value="{{ $license_holder->fullname }}" disabled>
                            </div>
                        </div>

                        {{-- Perusahaan --}}
                        <div class="col-12"><div class="subheader-line">Perusahaan / Lembaga</div></div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="company_name">Nama Lembaga</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-building"></i></span>
                                <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                            </div>
                            @error('company_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="city">Kota</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}">
                            </div>
                            @error('city')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="phone">Telepon Kantor</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" inputmode="tel">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Posisi --}}
                        <div class="col-12"><div class="subheader-line">Posisi & Masa Kerja</div></div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="position">Posisi</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-id-badge-2"></i></span>
                                <input type="text" id="position" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position') }}">
                            </div>
                            @error('position')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="employment_type">Status Karyawan</label>
                            <select name="employment_type" id="employment_type" class="form-select @error('employment_type') is-invalid @enderror">
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($employmentTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('employment_type') == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="is_current">Masih Bekerja?</label>
                            <select name="is_current" id="is_current" class="form-select @error('is_current') is-invalid @enderror">
                                <option value="">-- Pilih --</option>
                                <option value="1" @selected(old('is_current') === '1')>Masih Bekerja</option>
                                <option value="0" @selected(old('is_current') === '0')>Sudah Keluar</option>
                            </select>
                            @error('is_current')
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
                            <div class="form-hint" id="end-date-hint" style="display:none;">Tidak perlu diisi jika masih bekerja.</div>
                            @error('end_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kompetensi --}}
                        <div class="col-12"><div class="subheader-line">Kompetensi & Tanggung Jawab</div></div>

                        <div class="col-12">
                            <label class="form-label" for="skills_used">Kompetensi Utama</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-bulb"></i></span>
                                <input type="text" id="skills_used" name="skills_used" class="form-control @error('skills_used') is-invalid @enderror" value="{{ old('skills_used') }}">
                            </div>
                            @error('skills_used')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="job_description">Deskripsi Tanggung Jawab</label>
                            <textarea id="job_description" name="job_description" rows="3" class="form-control @error('job_description') is-invalid @enderror">{{ old('job_description') }}</textarea>
                            @error('job_description')
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
    const $isCurrent = $('#is_current');
    const $endDate = $('#end_date');
    const $startDate = $('#start_date');

    // Jika masih bekerja, tanggal berakhir dikosongkan dan dinonaktifkan
    function toggleEndDate() {
        const stillWorking = $isCurrent.val() === '1';
        $endDate.prop('disabled', stillWorking);
        if (stillWorking) $endDate.val('');
        $('#end-date-hint').toggle(stillWorking);
    }

    // Tanggal berakhir tidak boleh sebelum tanggal mulai
    function syncDates() {
        $endDate.attr('min', $startDate.val() || '');
    }

    $isCurrent.on('change', toggleEndDate);
    $startDate.on('input change', syncDates);
    toggleEndDate();
    syncDates();
});
</script>
@endpush