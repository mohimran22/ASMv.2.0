@extends('tablar::page')

@section('content')
<style>
    .license-form .form-section-title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .license-form .section-icon {
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
    .license-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .license-form .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .license-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .license-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
    .license-form .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: var(--tblr-bg-surface-secondary, #f6f8fb);
    }
    .license-form .file-name {
        font-size: .8125rem;
        color: #667382;
        margin-top: .25rem;
        word-break: break-all;
    }
    .license-form .form-actions {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: var(--tblr-card-bg, #fff);
        border-top: 1px solid var(--tblr-border-color, #e6e7e9);
    }
    @media (max-width: 575.98px) {
        .license-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Tambah Data Lisensi</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <form action="{{ route('licenses.store') }}" method="POST" enctype="multipart/form-data" class="license-form" novalidate="false">
            @csrf

            {{-- SECTION 1: Data Lisensi --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-license"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Lisensi</h3>
                            <div class="text-muted small">Informasi utama pemegang lisensi</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-none align-items-center gap-2" id="fo-notice" role="alert">
                        <i class="ti ti-info-circle fs-2"></i>
                        <div>Tipe <strong>FO</strong> dipilih: data wilayah, tanggal, dan dokumen kontrak tidak perlu diisi.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="license_type">Tipe Lisensi</label>
                            <select name="license_type" id="license_type" class="form-select select2 @error('license_type') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach(['FO', 'SO', 'LO', 'LC'] as $type)
                                    <option value="{{ $type }}" @selected(old('license_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('license_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="name">Nama</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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

                        <div class="col-12 col-md-6 col-xl-8">
                            <label class="form-label required" for="address">Alamat</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
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
                            <select name="district_id" id="district" class="form-select select2 @error('district_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                            @error('district_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="join_date">Tanggal Bergabung</label>
                            <input type="date" id="join_date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date') }}" required>
                            @error('join_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="expired_date">Tanggal Expired</label>
                            <input type="date" id="expired_date" name="expired_date" class="form-control @error('expired_date') is-invalid @enderror" value="{{ old('expired_date') }}" required>
                            @error('expired_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label">Status Lisensi</label>
                            <input type="text" class="form-control" value="{{ old('status', 'Ditentukan otomatis') }}" disabled>
                            <div class="form-hint">Status ditentukan otomatis berdasarkan tanggal expired.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Dokumen Aqad --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-file-text"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Dokumen Aqad</h3>
                            <div class="text-muted small">Nomor dan berkas pendukung (format PDF)</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="contract_agreement_number">Nomor Aqad</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                <input type="text" class="form-control @error('contract_agreement_number') is-invalid @enderror" id="contract_agreement_number" name="contract_agreement_number" value="{{ old('contract_agreement_number') }}" required>
                            </div>
                            @error('contract_agreement_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="contract_document">Upload Dokumen Aqad (PDF)</label>
                            <input type="file" id="contract_document" name="contract_document" class="form-control @error('contract_document') is-invalid @enderror" accept="application/pdf" required>
                            @error('contract_document')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label required" for="document_form">Upload Dokumen Form Lisensi (PDF)</label>
                            <input type="file" id="document_form" name="document_form" class="form-control @error('document_form') is-invalid @enderror" accept="application/pdf" required>
                            @error('document_form')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Data Lokasi --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-building"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Lokasi</h3>
                            <div class="text-muted small">Kondisi bangunan tempat usaha</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_type">Tipe Bangunan</label>
                            <select name="building_type" id="building_type" class="form-select select2">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="1" @selected(old('building_type') == '1')>Rukan</option>
                                <option value="2" @selected(old('building_type') == '2')>Residence</option>
                                <option value="3" @selected(old('building_type') == '3')>Ruko</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_status">Status Bangunan</label>
                            <select name="building_status" id="building_status" class="form-select select2">
                                <option value="">-- Pilih Status --</option>
                                <option value="1" @selected(old('building_status') == '1')>Milik</option>
                                <option value="2" @selected(old('building_status') == '2')>Sewa</option>
                                <option value="3" @selected(old('building_status') == '3')>Lain-Lain</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_rent_expired_date">Sisa Masa Sewa</label>
                            <input type="date" name="building_rent_expired_date" id="building_rent_expired_date" class="form-control" value="{{ old('building_rent_expired_date') }}">
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_area">Luas Bangunan</label>
                            <div class="input-group">
                                <input type="text" name="building_area" id="building_area" class="form-control @error('building_area') is-invalid @enderror" value="{{ old('building_area') }}" inputmode="decimal">
                                <span class="input-group-text">m²</span>
                            </div>
                            @error('building_area')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_condition">Kondisi Bangunan</label>
                            <select name="building_condition" id="building_condition" class="form-select select2">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="1" @selected(old('building_condition') == '1')>Baik</option>
                                <option value="2" @selected(old('building_condition') == '2')>Cukup</option>
                                <option value="3" @selected(old('building_condition') == '3')>Tidak Baik</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="building_has_ac">AC</label>
                            <select name="building_has_ac" id="building_has_ac" class="form-select select2">
                                <option value="">-- Pilih AC --</option>
                                <option value="1" @selected(old('building_has_ac') === '1')>Ya</option>
                                <option value="0" @selected(old('building_has_ac') === '0')>Tidak</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Sosial Media --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-section-title">
                        <span class="section-icon"><i class="ti ti-world"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Akun Sosial Media</h3>
                            <div class="text-muted small">Opsional, isi dengan URL lengkap (https://...)</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $socials = [
                                ['instagram', 'Instagram', 'ti-brand-instagram'],
                                ['facebook_page', 'Facebook', 'ti-brand-facebook'],
                                ['tiktok', 'Tiktok', 'ti-brand-tiktok'],
                                ['youtube', 'Youtube', 'ti-brand-youtube'],
                                ['google_maps', 'Google Maps', 'ti-map-2'],
                                ['landing_page_student_registration', 'Landing Page', 'ti-link'],
                            ];
                        @endphp
                        @foreach($socials as [$field, $label, $icon])
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti {{ $icon }}"></i></span>
                                    <input type="url" id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field) }}" placeholder="https://">
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
                        <a href="{{ route('license_holders.index') }}" class="btn btn-outline-secondary">Batal</a>
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

    const $type = $('#license_type');
    const wilayahFields = ['province', 'city', 'district', 'sub_district', 'postal_code'];
    const lainFields = ['join_date', 'expired_date', 'contract_agreement_number', 'contract_document', 'document_form'];

    function toggleFields(ids, isFO) {
        ids.forEach(function (id) {
            const $f = $('#' + id);
            if (isFO) {
                $f.prop('required', false).prop('disabled', true).val('').trigger('change');
            } else {
                $f.prop('disabled', false).prop('required', true);
            }
        });
    }

    function applyLicenseType() {
        const isFO = $type.val() === 'FO';
        toggleFields(wilayahFields, isFO);
        toggleFields(lainFields, isFO);
        $('#fo-notice').toggleClass('d-none', !isFO).toggleClass('d-flex', isFO);
    }

    $type.on('change', applyLicenseType);
    applyLicenseType();

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

    // Tanggal expired tidak boleh sebelum tanggal bergabung
    $('#join_date').on('change', function () {
        $('#expired_date').attr('min', $(this).val());
    });
    if ($('#join_date').val()) $('#expired_date').attr('min', $('#join_date').val());

    // Validasi ukuran & tipe file PDF (maks 5 MB)
    $('#contract_document, #document_form').on('change', function () {
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
});
</script>
@endpush