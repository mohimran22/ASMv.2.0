@extends('tablar::page')

@section('content')
@php
    $statuses = ['Aktif', 'Alumni'];
    $whereKnow = [
        1 => 'Teman/Keluarga',
        2 => 'Website AHA',
        3 => 'Instagram',
        4 => 'Tiktok',
        5 => 'Facebook',
        6 => 'Youtube',
        7 => 'Whatsapp',
        8 => 'Banner',
        9 => 'Kantor AHA',
    ];
@endphp

<style>
    .student-form .form-section-title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .student-form .section-icon {
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
    .student-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .student-form .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .student-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .student-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    /* Kotak upload */
    .student-form .upload-box {
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
    .student-form .upload-box:hover,
    .student-form .upload-box.dragover {
        border-color: #206bc4;
        background: rgba(32, 107, 196, .05);
    }
    .student-form .upload-box.is-invalid { border-color: #d63939; }
    .student-form .upload-box input[type=file] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .student-form .upload-placeholder {
        pointer-events: none;
        padding: 1rem;
    }
    .student-form .upload-placeholder .ti {
        font-size: 2.5rem;
        color: #206bc4;
        margin-bottom: .25rem;
    }
    .student-form .upload-preview {
        display: none;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .student-form .upload-box.has-image .upload-preview { display: block; }
    .student-form .upload-box.has-image .upload-placeholder { display: none; }
    .student-form .upload-box.has-image { border-style: solid; }

    /* Avatar persegi untuk foto siswa */
    .student-form .upload-box.upload-avatar {
        width: 160px;
        height: 160px;
        border-radius: 1rem;
    }

    .student-form .form-actions {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: var(--tblr-card-bg, #fff);
        border-top: 1px solid var(--tblr-border-color, #e6e7e9);
    }
    @media (max-width: 575.98px) {
        .student-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Ubah Data Siswa</h2>
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

        <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="student-form">
            @csrf
            @method('PUT')

            {{-- SECTION 1: Data Siswa --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-user-circle"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Siswa</h3>
                            <div class="text-muted small">Identitas dan kontak siswa</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Foto Siswa (avatar persegi) --}}
                        <div class="col-12">
                            <label class="form-label d-block" for="photo">Foto Siswa</label>
                            <label class="upload-box upload-avatar mx-auto mx-md-0 {{ $student->photo ? 'has-image' : '' }} @error('photo') is-invalid @enderror" for="photo">
                                <input type="file" id="photo" name="photo" accept="image/*" data-preview="#photo-preview">
                                <img id="photo-preview" class="upload-preview"
                                     @if($student->photo)
                                        src="{{ asset('storage/photos/' . $student->photo) }}"
                                        data-original="{{ asset('storage/photos/' . $student->photo) }}"
                                     @endif
                                     alt="Foto siswa">
                                <div class="upload-placeholder">
                                    <i class="ti ti-camera"></i>
                                    <div class="small text-muted">Klik atau seret foto</div>
                                </div>
                            </label>
                            <div class="form-hint text-center text-md-start">
                                {{ $student->photo ? 'Klik foto untuk menggantinya.' : 'Belum ada foto.' }}
                            </div>
                            @error('photo')
                                <div class="invalid-feedback d-block text-center text-md-start">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="license_id">Pilih Lisensi</label>
                            <select name="license_id" id="license_id" class="form-select select2 @error('license_id') is-invalid @enderror" required>
                                @foreach($licenses as $license)
                                    <option value="{{ $license->id }}" {{ old('license_id', $student->license_id) == $license->id ? 'selected' : '' }}>
                                        {{ $license->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('license_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="nis">NIS</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                <input type="text" class="form-control @error('nis') is-invalid @enderror" id="nis" name="nis" value="{{ old('nis', $student->nis) }}" required readonly>
                            </div>
                            @error('nis')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="fullname">Nama Lengkap Siswa</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname', $student->fullname) }}" required>
                            </div>
                            @error('fullname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="nickname">Nama Panggilan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user-heart"></i></span>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname', $student->nickname) }}" required>
                            </div>
                            @error('nickname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="gender">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-select select2" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="1" {{ old('gender', $student->gender) == 1 ? 'selected' : '' }}>Laki - Laki</option>
                                <option value="2" {{ old('gender', $student->gender) == 2 ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_place">Tempat Lahir</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" required>
                            </div>
                            @error('birth_place')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $student->birth_date) }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="age">Umur</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('age') is-invalid @enderror" id="age" name="age" value="{{ old('age', $student->age) }}" inputmode="numeric">
                                <span class="input-group-text">tahun</span>
                            </div>
                            <div class="form-hint">Terisi otomatis jika tanggal lahir diubah, bisa diubah manual.</div>
                            @error('age')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="religion_id">Agama</label>
                            <select name="religion_id" id="religion_id" class="form-select select2" required>
                                <option value="">-- Pilih Agama --</option>
                                @foreach($religions as $religion)
                                    <option value="{{ $religion->id }}" {{ old('religion_id', $student->religion_id) == $religion->id ? 'selected' : '' }}>
                                        {{ $religion->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="email">Email</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $student->email) }}" placeholder="nama@email.com">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="student_phone">Nomor HP Siswa</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" class="form-control @error('student_phone') is-invalid @enderror" id="student_phone" name="student_phone" value="{{ old('student_phone', $student->student_phone) }}" placeholder="08xxxxxxxxxx" inputmode="tel">
                            </div>
                            @error('student_phone')
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
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $student->address) }}" placeholder="Nama jalan, nomor, RT/RW" required>
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
                                    <option value="{{ $province->id }}" {{ $student->province_id == $province->id ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="city">Kabupaten/Kota</label>
                            <select name="city_id" id="city" class="form-select select2" required>
                                <option value="">-- Pilih Kota --</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ $student->city_id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="district">Kecamatan</label>
                            <select name="district_id" id="district" class="form-select select2" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ $student->district_id == $district->id ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="sub_district">Desa</label>
                            <select name="sub_district_id" id="sub_district" class="form-select select2" required>
                                <option value="">-- Pilih Desa --</option>
                                @foreach($subDistricts as $sub_district)
                                    <option value="{{ $sub_district->id }}" {{ $student->sub_district_id == $sub_district->id ? 'selected' : '' }}>
                                        {{ $sub_district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="postal_code">Kode Pos</label>
                            <select name="postal_code_id" id="postal_code" class="form-select select2" required>
                                <option value="">-- Pilih Kode Pos --</option>
                                @foreach($postalCodes as $postal_code)
                                    <option value="{{ $postal_code->id }}" {{ $student->postal_code_id == $postal_code->id ? 'selected' : '' }}>
                                        {{ $postal_code->postal_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Data Orang Tua --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-users"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Orang Tua</h3>
                            <div class="text-muted small">Nama dan nomor HP ayah dan bunda</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="father_name">Nama Ayah</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('father_name') is-invalid @enderror" id="father_name" name="father_name" value="{{ old('father_name', $student->father_name) }}" required>
                            </div>
                            @error('father_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="father_phone">Nomor HP Ayah</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" class="form-control @error('father_phone') is-invalid @enderror" id="father_phone" name="father_phone" value="{{ old('father_phone', $student->father_phone) }}" inputmode="tel" required>
                            </div>
                            @error('father_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="mother_name">Nama Bunda</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('mother_name') is-invalid @enderror" id="mother_name" name="mother_name" value="{{ old('mother_name', $student->mother_name) }}" required>
                            </div>
                            @error('mother_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="mother_phone">Nomor HP Bunda</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" class="form-control @error('mother_phone') is-invalid @enderror" id="mother_phone" name="mother_phone" value="{{ old('mother_phone', $student->mother_phone) }}" inputmode="tel" required>
                            </div>
                            @error('mother_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Data Sekolah --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-school"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Sekolah</h3>
                            <div class="text-muted small">Sekolah asal, kelas, dan status pendaftaran</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="previous_school">Nama Sekolah Asal</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-building"></i></span>
                                <input type="text" class="form-control @error('previous_school') is-invalid @enderror" id="previous_school" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" required>
                            </div>
                            @error('previous_school')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="grade">Kelas</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-chalkboard"></i></span>
                                <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade" name="grade" value="{{ old('grade', $student->grade) }}" required>
                            </div>
                            @error('grade')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="status">Status</label>
                            <select name="status" id="status" class="form-select select2 @error('status') is-invalid @enderror">
                                <option value="">-- Pilih Status --</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $student->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="registered_date">Tanggal Pendaftaran</label>
                            <input type="date" id="registered_date" name="registered_date" class="form-control @error('registered_date') is-invalid @enderror" value="{{ old('registered_date', $student->registered_date) }}" required>
                            @error('registered_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="where_know">Tahu Darimana</label>
                            <select name="where_know" id="where_know" class="form-select select2" required>
                                <option value="">-- Pilih Info --</option>
                                @foreach($whereKnow as $value => $label)
                                    <option value="{{ $value }}" {{ old('where_know', $student->where_know) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card form-actions">
                <div class="card-body py-3">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Update
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

    // ===== NIS otomatis saat lisensi diganti =====
    const $licenseSelect = $('#license_id');
    const $nisInput = $('#nis');
    const activeLicenseId = "{{ session('active_license_id') }}";

    function generateNis(licenseId) {
        $.ajax({
            url: '/students/generate-nis/' + licenseId,
            type: 'GET',
            success: function (response) {
                $nisInput.val(response.nis ? response.nis : '');
            },
            error: function (xhr) {
                console.error('Gagal mengambil NIS:', xhr.status);
                $nisInput.val('');
            }
        });
    }

    // Saat user mengganti lisensi -> generate ulang NIS
    $licenseSelect.on('change', function () {
        const selected = $(this).val();
        if (selected) {
            generateNis(selected);
        } else {
            $nisInput.val('');
        }
    });

    // Jika ada lisensi aktif dari navbar -> pilih dan generate otomatis
    if (activeLicenseId) {
        $licenseSelect.val(activeLicenseId).trigger('change');
    }

    // ===== Umur otomatis jika tanggal lahir diubah (tetap bisa diubah manual) =====
    $('#birth_date').on('change', function () {
        const value = $(this).val();
        if (!value) return;
        const birth = new Date(value);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        if (age >= 0) $('#age').val(age);
    });

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

    // ===== Preview foto + validasi tipe & ukuran (maks 5 MB) =====
    // Jika dibatalkan/tidak valid, kembali menampilkan foto yang sudah tersimpan.
    function restoreOriginal($box, $preview) {
        const original = $preview.data('original');
        if (original) {
            $preview.attr('src', original);
            $box.addClass('has-image');
        } else {
            $preview.removeAttr('src');
            $box.removeClass('has-image');
        }
    }

    $('input[type=file][data-preview]').on('change', function () {
        const $box = $(this).closest('.upload-box');
        const $preview = $($(this).data('preview'));
        const file = this.files[0];

        if (!file) { restoreOriginal($box, $preview); return; }

        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar.');
            this.value = '';
            restoreOriginal($box, $preview);
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file maksimal 5 MB.');
            this.value = '';
            restoreOriginal($box, $preview);
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $preview.attr('src', e.target.result);
            $box.addClass('has-image');
        };
        reader.readAsDataURL(file);
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