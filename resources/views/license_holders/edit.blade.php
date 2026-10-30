@extends('tablar::page')

@section('content')
<style>
    .holder-form .form-section-title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .holder-form .section-icon {
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
    .holder-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .holder-form .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .holder-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .holder-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
    .holder-form .select2-container--default .select2-selection--multiple {
        min-height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
    }
    .holder-form .select2-container--default.select2-container--disabled .select2-selection--single,
    .holder-form .select2-container--default.select2-container--disabled .select2-selection--multiple {
        background-color: var(--tblr-bg-surface-secondary, #f6f8fb);
    }

    /* Kotak upload */
    .holder-form .upload-box {
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
    .holder-form .upload-box:hover,
    .holder-form .upload-box.dragover {
        border-color: #206bc4;
        background: rgba(32, 107, 196, .05);
    }
    .holder-form .upload-box.is-invalid { border-color: #d63939; }
    .holder-form .upload-box input[type=file] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .holder-form .upload-placeholder {
        pointer-events: none;
        padding: 1rem;
    }
    .holder-form .upload-placeholder .ti {
        font-size: 2.5rem;
        color: #206bc4;
        margin-bottom: .25rem;
    }
    .holder-form .upload-preview {
        display: none;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .holder-form .upload-box.has-image .upload-preview { display: block; }
    .holder-form .upload-box.has-image .upload-placeholder { display: none; }
    .holder-form .upload-box.has-image { border-style: solid; }

    /* Avatar persegi untuk foto diri */
    .holder-form .upload-box.upload-avatar {
        width: 160px;
        height: 160px;
        border-radius: 1rem;
    }
    /* Rasio kartu identitas untuk KTP */
    .holder-form .upload-box.upload-ktp {
        height: auto;
        min-height: 130px;
        aspect-ratio: 85.6 / 54;
    }

    .holder-form .form-actions {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: var(--tblr-card-bg, #fff);
        border-top: 1px solid var(--tblr-border-color, #e6e7e9);
    }
    @media (max-width: 575.98px) {
        .holder-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Ubah Data Pemilik Lisensi</h2>
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

        <form action="{{ route('license_holders.update', $license_holder->id) }}" method="POST" enctype="multipart/form-data" class="holder-form">
            @csrf
            @method('PUT')

            {{-- SECTION 1: Data Pemilik --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-user-circle"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pemilik</h3>
                            <div class="text-muted small">Identitas dan kontak pemilik lisensi</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Foto Diri (avatar persegi) --}}
                        <div class="col-12">
                            <label class="form-label d-block" for="photo">Foto Diri</label>
                            <label class="upload-box upload-avatar mx-auto mx-md-0 {{ $license_holder->photo ? 'has-image' : '' }} @error('photo') is-invalid @enderror" for="photo">
                                <input type="file" id="photo" name="photo" accept="image/*" data-preview="#photo-preview">
                                <img id="photo-preview" class="upload-preview"
                                     @if($license_holder->photo)
                                        src="{{ asset('storage/photos/' . $license_holder->photo) }}"
                                        data-original="{{ asset('storage/photos/' . $license_holder->photo) }}"
                                     @endif
                                     alt="Foto diri">
                                <div class="upload-placeholder">
                                    <i class="ti ti-camera"></i>
                                    <div class="small text-muted">Klik atau seret foto</div>
                                </div>
                            </label>
                            <div class="form-hint text-center text-md-start">
                                {{ $license_holder->photo ? 'Klik foto untuk menggantinya.' : 'Belum ada foto.' }}
                            </div>
                            @error('photo')
                                <div class="invalid-feedback d-block text-center text-md-start">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="licenses">Pilih Lisensi</label>
                            <select name="licenses[]" id="licenses" class="form-select select2 @error('licenses') is-invalid @enderror" multiple required
                                @role('Pemilik Lisensi') disabled @endrole>
                                @foreach($licenses as $license)
                                    <option value="{{ $license->id }}"
                                        {{ collect(old('licenses', $license_holder->user->licenses->pluck('id')->toArray()))->contains($license->id) ? 'selected' : '' }}>
                                        {{ $license->name }}
                                    </option>
                                @endforeach
                            </select>

                            @role('Pemilik Lisensi')
                                @foreach($license_holder->user->licenses as $license)
                                    <input type="hidden" name="licenses[]" value="{{ $license->id }}">
                                @endforeach
                            @endrole
                            @error('licenses')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="fullname">Nama Pemilik</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname', $license_holder->fullname) }}" required>
                            </div>
                            @error('fullname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="nickname">Nama Panggilan</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user-heart"></i></span>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname', $license_holder->nickname) }}" required>
                            </div>
                            @error('nickname')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="gender">Jenis Kelamin</label>
                            <select name="gender" id="gender" class="form-select select2" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="1" {{ old('gender', $license_holder->gender) == 1 ? 'selected' : '' }}>Laki - Laki</option>
                                <option value="2" {{ old('gender', $license_holder->gender) == 2 ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="email">Email</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $license_holder->user->email) }}"
                                    @if(auth()->user()->hasRole('Pemilik Lisensi')) readonly @endif>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="phone">Telepon</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $license_holder->phone) }}" inputmode="tel" required>
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="religion_id">Agama</label>
                            <select name="religion_id" id="religion_id" class="form-select select2" required>
                                <option value="">-- Pilih Agama --</option>
                                @foreach($religions as $religion)
                                    <option value="{{ $religion->id }}" {{ old('religion_id', $license_holder->religion_id) == $religion->id ? 'selected' : '' }}>
                                        {{ $religion->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="identity_number">Nomor KTP</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-id"></i></span>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" id="identity_number" name="identity_number" maxlength="16" inputmode="numeric" value="{{ old('identity_number', $license_holder->identity_number) }}" placeholder="16 digit" required>
                            </div>
                            @error('identity_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="driver_license_number">Nomor SIM</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-license"></i></span>
                                <input type="number" class="form-control @error('driver_license_number') is-invalid @enderror" id="driver_license_number" name="driver_license_number" value="{{ old('driver_license_number', $license_holder->driver_license_number) }}" inputmode="numeric">
                            </div>
                            @error('driver_license_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_place">Tempat Lahir</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place', $license_holder->birth_place) }}" required>
                            </div>
                            @error('birth_place')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $license_holder->birth_date) }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="hobby">Hobi</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-heart"></i></span>
                                <input type="text" class="form-control @error('hobby') is-invalid @enderror" id="hobby" name="hobby" value="{{ old('hobby', $license_holder->hobby) }}" required>
                            </div>
                            @error('hobby')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Upload KTP --}}
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="identity_photo">Foto KTP</label>
                            <label class="upload-box upload-ktp {{ $license_holder->identity_photo ? 'has-image' : '' }} @error('identity_photo') is-invalid @enderror" for="identity_photo">
                                <input type="file" id="identity_photo" name="identity_photo" accept="image/*" data-preview="#identity-preview">
                                <img id="identity-preview" class="upload-preview"
                                     @if($license_holder->identity_photo)
                                        src="{{ asset('storage/photos/' . $license_holder->identity_photo) }}"
                                        data-original="{{ asset('storage/photos/' . $license_holder->identity_photo) }}"
                                     @endif
                                     alt="Foto KTP">
                                <div class="upload-placeholder">
                                    <i class="ti ti-id"></i>
                                    <div class="small text-muted">Klik atau seret foto KTP</div>
                                </div>
                            </label>
                            <div class="form-hint">
                                {{ $license_holder->identity_photo ? 'Klik gambar untuk menggantinya.' : 'Belum ada foto KTP.' }}
                            </div>
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
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $license_holder->address) }}" placeholder="Nama jalan, nomor, RT/RW" required>
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
                                    <option value="{{ $province->id }}" {{ $license_holder->province_id == $province->id ? 'selected' : '' }}>
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
                                    <option value="{{ $city->id }}" {{ $license_holder->city_id == $city->id ? 'selected' : '' }}>
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
                                    <option value="{{ $district->id }}" {{ $license_holder->district_id == $district->id ? 'selected' : '' }}>
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
                                    <option value="{{ $sub_district->id }}" {{ $license_holder->sub_district_id == $sub_district->id ? 'selected' : '' }}>
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
                                    <option value="{{ $postal_code->id }}" {{ $license_holder->postal_code_id == $postal_code->id ? 'selected' : '' }}>
                                        {{ $postal_code->postal_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Data Pernikahan --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-heart-handshake"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Pernikahan</h3>
                            <div class="text-muted small">Status dan tanggal pernikahan</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="marital_status">Status Pernikahan</label>
                            <select name="marital_status" id="marital_status" class="form-select select2" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="1" {{ old('marital_status', $license_holder->marital_status) == 1 ? 'selected' : '' }}>Lajang</option>
                                <option value="2" {{ old('marital_status', $license_holder->marital_status) == 2 ? 'selected' : '' }}>Menikah</option>
                                <option value="3" {{ old('marital_status', $license_holder->marital_status) == 3 ? 'selected' : '' }}>Duda</option>
                                <option value="4" {{ old('marital_status', $license_holder->marital_status) == 4 ? 'selected' : '' }}>Janda</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="married_date">Tanggal Pernikahan</label>
                            <input type="date" id="married_date" name="married_date" class="form-control" value="{{ old('married_date', $license_holder->married_date) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Kemampuan Bahasa --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-language"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Kemampuan Bahasa</h3>
                            <div class="text-muted small">Kemampuan baca/tulis dan bicara</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $languages = [
                            'Bahasa Indonesia' => ['indonesian_literacy', 'indonesian_proficiency'],
                            'Bahasa Arab'      => ['arabic_literacy', 'arabic_proficiency'],
                            'Bahasa Inggris'   => ['english_literacy', 'english_proficiency'],
                        ];
                    @endphp
                    <div class="row g-3">
                        @foreach($languages as $langLabel => [$literacy, $proficiency])
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="{{ $literacy }}">{{ $langLabel }} (Baca/Tulis)</label>
                                <select name="{{ $literacy }}" id="{{ $literacy }}" class="form-select select2">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old($literacy, $license_holder->$literacy) == 1 ? 'selected' : '' }}>Lancar</option>
                                    <option value="2" {{ old($literacy, $license_holder->$literacy) == 2 ? 'selected' : '' }}>Tidak Lancar</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="{{ $proficiency }}">{{ $langLabel }} (Bicara)</label>
                                <select name="{{ $proficiency }}" id="{{ $proficiency }}" class="form-select select2">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old($proficiency, $license_holder->$proficiency) == 1 ? 'selected' : '' }}>Lancar</option>
                                    <option value="2" {{ old($proficiency, $license_holder->$proficiency) == 2 ? 'selected' : '' }}>Tidak Lancar</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card form-actions">
                <div class="card-body py-3">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">Batal</a>
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
    $('.select2').select2({ placeholder: '-- Pilih --', width: '100%' });

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

    // Nomor KTP hanya angka
    $('#identity_number').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 16);
    });

    // Preview gambar + validasi tipe & ukuran (maks 5 MB).
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