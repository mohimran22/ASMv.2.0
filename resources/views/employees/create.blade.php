@extends('tablar::page')

@section('content')
@php
    $positions   = ['Komisaris', 'Direktur', 'Manager', 'Supervisor', 'Staff'];
    $departments = ['Networking', 'Produksi', 'Keuangan', 'HR', 'Marketing'];
    // [value => label]
    $units = [
        'Lisensi'         => 'Lisensi',
        'Event'           => 'Event',
        'Training'        => 'Training',
        'Trainer Pusat'   => 'Trainer Pusat',
        'Trainer Wilayah' => 'Trainer Wilayah',
        'Pengadaan'       => 'Pengadaan',
        'Kursus'          => 'Kursus',
        'Instruktur'      => 'Instruktur',
    ];
    $employmentStatuses = ['Tetap', 'Kontrak', 'Harian', 'Honorer'];

    $salaryFields = [
        'basic_salary' => 'Gaji Pokok',
        'allowance'    => 'Tunjangan',
        'deduction'    => 'Potongan',
        'bonus'        => 'Bonus',
        'thr'          => 'THR',
    ];
@endphp

<style>
    .employee-form .form-section-title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .employee-form .section-icon {
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
    .employee-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .employee-form .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .employee-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .employee-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
    .employee-form .select2-container--default .select2-selection--multiple {
        min-height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
    }

    /* Kotak upload */
    .employee-form .upload-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 220px;
        margin: 0;
        border: 2px dashed var(--tblr-border-color, #c4cbd4);
        border-radius: .75rem;
        background: var(--tblr-bg-surface-secondary, #f6f8fb);
        overflow: hidden;
        cursor: pointer;
        text-align: center;
        transition: border-color .15s, background-color .15s;
    }
    .employee-form .upload-box:hover,
    .employee-form .upload-box.dragover {
        border-color: #206bc4;
        background: rgba(32, 107, 196, .05);
    }
    .employee-form .upload-box.is-invalid { border-color: #d63939; }
    .employee-form .upload-box input[type=file] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .employee-form .upload-placeholder {
        pointer-events: none;
        padding: 1rem;
    }
    .employee-form .upload-placeholder .ti {
        font-size: 2.5rem;
        color: #206bc4;
        margin-bottom: .25rem;
    }
    .employee-form .upload-preview {
        display: none;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .employee-form .upload-box.has-image .upload-preview { display: block; }
    .employee-form .upload-box.has-image .upload-placeholder { display: none; }
    .employee-form .upload-box.has-image { border-style: solid; }

    /* Avatar persegi untuk foto diri */
    .employee-form .upload-box.upload-avatar {
        width: 160px;
        height: 160px;
        border-radius: 1rem;
    }
    /* Rasio kartu identitas untuk KTP */
    .employee-form .upload-box.upload-ktp {
        height: auto;
        min-height: 130px;
        aspect-ratio: 85.6 / 54;
    }

    .employee-form .cert-box {
        border: 1px dashed var(--tblr-border-color, #c4cbd4);
        border-radius: .75rem;
        padding: 1rem;
        background: var(--tblr-bg-surface-secondary, #f6f8fb);
    }

    .employee-form .form-actions {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: var(--tblr-card-bg, #fff);
        border-top: 1px solid var(--tblr-border-color, #e6e7e9);
    }
    @media (max-width: 575.98px) {
        .employee-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    {{-- <a href="{{ route('employees.index') }}" class="btn btn-icon btn-primary me-2" aria-label="Kembali">
                        <i class="ti ti-arrow-left"></i>
                    </a> --}}
                    <h2>Tambah Data Karyawan</h2>
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

        <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data" class="employee-form">
            @csrf

            {{-- SECTION 1: Data Karyawan --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-user-circle"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Karyawan</h3>
                            <div class="text-muted small">Identitas dan kontak karyawan</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Foto Diri (avatar persegi) --}}
                        <div class="col-12">
                            <label class="form-label d-block" for="photo">Foto Diri</label>
                            <label class="upload-box upload-avatar mx-auto mx-md-0 @error('photo') is-invalid @enderror" for="photo">
                                <input type="file" id="photo" name="photo" accept="image/*" data-preview="#photo-preview">
                                <img id="photo-preview" class="upload-preview" alt="Preview foto diri">
                                <div class="upload-placeholder">
                                    <i class="ti ti-camera"></i>
                                    <div class="small text-muted">Klik atau seret foto</div>
                                </div>
                            </label>
                            @error('photo')
                                <div class="invalid-feedback d-block text-center text-md-start">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="licenses">Pilih Lisensi</label>
                            <select name="licenses[]" id="licenses" class="form-select select2 @error('licenses') is-invalid @enderror" multiple required>
                                @forelse($licenses as $license)
                                    <option value="{{ $license->id }}" @selected(in_array($license->id, (array) old('licenses', [])))>{{ $license->name }}</option>
                                @empty
                                    <option value="">Tidak ada lisensi tersedia</option>
                                @endforelse
                            </select>
                            @error('licenses')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="nik">NIK</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" required readonly>
                            </div>
                            <div class="form-hint">NIK terisi otomatis setelah memilih lisensi.</div>
                            @error('nik')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="fullname">Nama Lengkap</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname') }}" placeholder="Nama lengkap" required>
                            </div>
                            @error('fullname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="nickname">Nama Panggilan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user-heart"></i></span>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname') }}" required>
                            </div>
                            @error('nickname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="gender">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-select select2" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="1" @selected(old('gender') == '1')>Laki - Laki</option>
                                <option value="2" @selected(old('gender') == '2')>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_place">Tempat Lahir</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" required>
                            </div>
                            @error('birth_place')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="religion_id">Agama</label>
                            <select name="religion_id" id="religion_id" class="form-select select2" required>
                                <option value="">-- Pilih Agama --</option>
                                @foreach($religions as $religion)
                                    <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                        {{ $religion->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="email">Email</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="phone">Telepon</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" inputmode="tel" required>
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="identity_number">Nomor KTP</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-id"></i></span>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" id="identity_number" name="identity_number" maxlength="16" inputmode="numeric" pattern="\d{16}" title="Nomor KTP terdiri dari 16 digit angka" value="{{ old('identity_number') }}" placeholder="16 digit" required>
                            </div>
                            @error('identity_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Upload KTP --}}
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="identity_photo">Foto KTP</label>
                            <label class="upload-box upload-ktp @error('identity_photo') is-invalid @enderror" for="identity_photo">
                                <input type="file" id="identity_photo" name="identity_photo" accept="image/*" data-preview="#identity-preview">
                                <img id="identity-preview" class="upload-preview" alt="Preview KTP">
                                <div class="upload-placeholder">
                                    <i class="ti ti-id"></i>
                                    <div class="small text-muted">Klik atau seret foto KTP</div>
                                </div>
                            </label>
                            @error('identity_photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Alamat --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-map-2"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Alamat</h3>
                            <div class="text-muted small">Alamat domisili dan wilayah</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required" for="address">Alamat</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-home"></i></span>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" placeholder="Nama jalan, nomor, RT/RW" required>
                            </div>
                            @error('address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="province">Provinsi</label>
                            <select name="province_id" id="province" class="form-select select2" required>
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="city">Kabupaten/Kota</label>
                            <select name="city_id" id="city" class="form-select select2" required>
                                <option value="">-- Pilih Kota --</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="district">Kecamatan</label>
                            <select name="district_id" id="district" class="form-select select2" required>
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="sub_district">Desa</label>
                            <select name="sub_district_id" id="sub_district" class="form-select select2" required>
                                <option value="">-- Pilih Desa --</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="postal_code">Kode Pos</label>
                            <select name="postal_code_id" id="postal_code" class="form-select select2" required>
                                <option value="">-- Pilih Kode Pos --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Kepegawaian --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-briefcase"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Kepegawaian</h3>
                            <div class="text-muted small">Jabatan, unit kerja, dan dokumen perjanjian</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="position">Jabatan</label>
                            <select name="position[]" id="position" class="form-select select2" multiple required>
                                @foreach($positions as $position)
                                    <option value="{{ $position }}" @selected(in_array($position, (array) old('position', [])))>{{ $position }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="department">Departemen</label>
                            <select name="department[]" id="department" class="form-select select2" multiple required>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}" @selected(in_array($department, (array) old('department', [])))>{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="unit">Unit Kerja</label>
                            <select id="unit" name="unit[]" class="form-select select2" multiple required>
                                @foreach($units as $value => $label)
                                    <option value="{{ $value }}" @selected(in_array($value, (array) old('unit', [])))>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="employment_status">Status Karyawan</label>
                            <select name="employment_status" id="employment_status" class="form-select select2" required>
                                <option value="">-- Pilih Status --</option>
                                @foreach($employmentStatuses as $status)
                                    <option value="{{ $status }}" @selected(old('employment_status') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="role">Role</label>

                            @if(auth()->user()->hasAnyRole(['Pemilik Lisensi', 'Akuntan']))
                                {{-- Hidden input supaya tetap kirim value --}}
                                <input type="hidden" name="role" value="Karyawan">

                                {{-- Select ditampilkan tapi disabled --}}
                                <select id="role" class="form-select" disabled>
                                    <option value="Karyawan" selected>Karyawan</option>
                                </select>
                            @else
                                {{-- Selain Pemilik Lisensi/Akuntan, bisa pilih bebas --}}
                                <select name="role" id="role" class="form-select select2" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="start_date">Tanggal Mulai Kerja</label>
                            <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="contract_letter_file">Surat Perjanjian Kerja (PDF)</label>
                            <input type="file" id="contract_letter_file" name="contract_letter_file" class="form-control @error('contract_letter_file') is-invalid @enderror" accept="application/pdf" data-pdf required>
                            @error('contract_letter_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Sertifikat instruktur (muncul jika unit kerja Instruktur dipilih) --}}
                        <div class="col-12 d-none" id="upload-sertifikat-container">
                            <div class="cert-box">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ti ti-certificate fs-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">Sertifikat Instruktur</div>
                                        <div class="text-muted small">Wajib diisi untuk unit kerja Instruktur</div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="instructure_certificate">Upload Sertifikat Instruktur (PDF)</label>
                                        <input type="file" id="instructure_certificate" name="instructure_certificate" class="form-control @error('instructure_certificate') is-invalid @enderror" accept="application/pdf" data-pdf>
                                        @error('instructure_certificate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="expired_date_certificate">Tanggal Expired Sertifikat</label>
                                        <input type="date" id="expired_date_certificate" name="expired_date_certificate" class="form-control @error('expired_date_certificate') is-invalid @enderror" value="{{ old('expired_date_certificate') }}">
                                        @error('expired_date_certificate')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Data Pernikahan --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-heart-handshake"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pernikahan</h3>
                            <div class="text-muted small">Status pernikahan karyawan</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="marital_status">Status Pernikahan</label>
                            <select name="marital_status" id="marital_status" class="form-select select2" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="1" @selected(old('marital_status') == '1')>Lajang</option>
                                <option value="2" @selected(old('marital_status') == '2')>Menikah</option>
                                <option value="3" @selected(old('marital_status') == '3')>Duda</option>
                                <option value="4" @selected(old('marital_status') == '4')>Janda</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 5: Gaji & Tunjangan --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-cash"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Gaji & Tunjangan</h3>
                            <div class="text-muted small">Komponen penghasilan karyawan (dalam Rupiah)</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($salaryFields as $field => $label)
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label required" for="{{ $field }}">{{ $label }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" min="0" step="any" inputmode="numeric" class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" required>
                                </div>
                                @error($field)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card form-actions">
                <div class="card-body py-3">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Batal</a>
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
    $('.select2').select2({ placeholder: '-- Pilih --', width: '100%' });

    // ===== Sertifikat instruktur: tampil jika unit kerja "Instruktur" dipilih =====
    function toggleSertifikatInstruktur() {
        const selectedUnits = $('#unit').val() || [];
        const show = selectedUnits.includes('Instruktur');
        $('#upload-sertifikat-container').toggleClass('d-none', !show);
        $('#instructure_certificate').prop('required', show);
    }
    toggleSertifikatInstruktur();
    $('#unit').on('change', toggleSertifikatInstruktur);

    // ===== NIK otomatis berdasarkan lisensi pertama yang dipilih =====
    const $licenses = $('#licenses');
    const $nikInput = $('#nik');

    function generateNik(licenseId) {
        $.ajax({
            url: '/employees/generate-nik/' + licenseId,
            type: 'GET',
            success: function (response) {
                $nikInput.val(response.nik ? response.nik : '');
            },
            error: function () {
                console.error('Gagal mengambil NIK.');
                $nikInput.val('');
            }
        });
    }

    $licenses.on('change', function () {
        const selected = $(this).val();
        if (Array.isArray(selected) && selected.length > 0 && selected[0]) {
            generateNik(selected[0]);
        } else {
            $nikInput.val('');
        }
    });

    // Jalankan saat halaman dimuat (mis. setelah validasi gagal)
    const initialLicenses = $licenses.val();
    if (Array.isArray(initialLicenses) && initialLicenses.length > 0 && initialLicenses[0]) {
        generateNik(initialLicenses[0]);
    }

    // ===== Dropdown wilayah bertingkat =====
    // Reset select & refresh tampilan select2 tanpa memicu cascade
    function resetSelect(id, placeholder) {
        $('#' + id).html('<option value="">' + placeholder + '</option>').trigger('change.select2');
    }

    function loadOptions(url, targetId, placeholder, labelKey) {
        const $target = $('#' + targetId);
        $target.html('<option value="">Memuat...</option>').trigger('change.select2');

        $.get(url, function (data) {
            $target.empty().append('<option value="">' + placeholder + '</option>');
            $.each(data, function (i, item) {
                $target.append($('<option>', { value: item.id, text: item[labelKey] }));
            });
            $target.trigger('change.select2');
        }).fail(function () {
            resetSelect(targetId, placeholder);
        });
    }

    $('#province').on('change', function () {
        const id = $(this).val();
        resetSelect('city', '-- Pilih Kota --');
        resetSelect('district', '-- Pilih Kecamatan --');
        resetSelect('sub_district', '-- Pilih Desa --');
        resetSelect('postal_code', '-- Pilih Kode Pos --');
        if (id) loadOptions('/api/cities/' + id, 'city', '-- Pilih Kota --', 'name');
    });

    $('#city').on('change', function () {
        const id = $(this).val();
        resetSelect('district', '-- Pilih Kecamatan --');
        resetSelect('sub_district', '-- Pilih Desa --');
        resetSelect('postal_code', '-- Pilih Kode Pos --');
        if (id) loadOptions('/api/districts/' + id, 'district', '-- Pilih Kecamatan --', 'name');
    });

    $('#district').on('change', function () {
        const id = $(this).val();
        resetSelect('sub_district', '-- Pilih Desa --');
        resetSelect('postal_code', '-- Pilih Kode Pos --');
        if (id) loadOptions('/api/sub_districts/' + id, 'sub_district', '-- Pilih Desa --', 'name');
    });

    $('#sub_district').on('change', function () {
        const id = $(this).val();
        resetSelect('postal_code', '-- Pilih Kode Pos --');
        if (id) loadOptions('/api/postal_codes/' + id, 'postal_code', '-- Pilih Kode Pos --', 'postal_code');
    });

    // ===== Nomor KTP hanya angka =====
    $('#identity_number').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 16);
    });

    // ===== Preview gambar + validasi tipe & ukuran (maks 5 MB) =====
    $('input[type=file][data-preview]').on('change', function () {
        const $box = $(this).closest('.upload-box');
        const $preview = $($(this).data('preview'));
        const file = this.files[0];
        if (!file) { $box.removeClass('has-image'); return; }

        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar.');
            this.value = '';
            $box.removeClass('has-image');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file maksimal 5 MB.');
            this.value = '';
            $box.removeClass('has-image');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $preview.attr('src', e.target.result);
            $box.addClass('has-image');
        };
        reader.readAsDataURL(file);
    });

    // ===== Validasi file PDF (maks 5 MB) =====
    $('input[type=file][data-pdf]').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.type !== 'application/pdf') {
            alert('File harus berformat PDF.');
            this.value = '';
        } else if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file maksimal 5 MB.');
            this.value = '';
        }
    });

    // Efek highlight saat file diseret ke kotak upload
    $('.upload-box').on('dragenter dragover', function () {
        $(this).addClass('dragover');
    }).on('dragleave drop', function () {
        $(this).removeClass('dragover');
    });
});
</script>
@endpush