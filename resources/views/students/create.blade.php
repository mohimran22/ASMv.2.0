@extends('tablar::page')

@section('content')
<!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Page pre-title -->
                    <div class="page-pretitle">
                        Overview
                    </div>
                    <p class="page-title">
                       Siswa
                    </p>
                </div>
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                 
                        <a href=" {{ route("students.index") }} " class="btn btn-primary text-white d-none d-sm-inline-block" >
                            Kembali
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                                    <div class="card-header">
                                        <p class="text-center mb-4">
                                            Tambah Data Siswa
                                        </p>
                                    </div>

                            <div class="card-body">
                                <form class="font-normal" action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    {{-- SECTION 1: Informasi Lead --}}
                                    <h2 class="mt-4 mb-3">Data Siswa</h2>
                                    <div class="row mb-4">

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Pilih Lisensi</label>
                                            <select name="license_id" class="form-select select2" required>
                                                <option value="">-- Pilih Lisensi --</option>
                                                @foreach($licenses as $license)
                                                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="col-md-6 mb-3">
                                            <label class="required">NIS</label>
                                            <input type="text" name="nis" class="form-control" required readonly>
                                            <small class="text-muted">NIS akan terisi otomatis setelah memilih lisensi</small>
                                        </div>


                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nama Lengkap Siswa </label>
                                            <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname') }}" required>
                                            @error('fullname')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                         <div class="col-md-6 mb-3">
                                            <label class="required">Nama Panggilan </label>
                                            <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname') }}" required>
                                            @error('nickname')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Jenis Kelamin </label>
                                            <select name="gender" class="form-select select2" required>
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="1">Laki - Laki</option>
                                                <option value="2">Perempuan</option>
                                            </select>
                                        </div>
                                                 
                                        <div class="col-md-6 mb-3">
                                            <label class="required">Tempat Lahir </label>
                                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place') }}" required>
                                            @error('birth_place')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Tanggal Lahir </label>
                                            <input type="date" name="birth_date" class="form-control" required
                                                value="{{ old('birth_date') }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>Umur</label>
                                            <input type="text" class="form-control @error('age') is-invalid @enderror" id="age" name="age" value="{{ old('age') }}">
                                            @error('age')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        

                                        <div class="col-md-6 mb-3">
                                            <label class="required" for="religion_id">Agama </label>
                                            <select name="religion_id" class="form-select select2" required>
                                                <option value="">-- Pilih Agama --</option>
                                                @foreach($religions as $religion)
                                                    <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                                        {{ $religion->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                           
                                        <div class="col-md-6 mb-3">
                                            <label class="required">Alamat Lengkap</label>
                                            <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" required>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Email</label>
                                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Provinsi </label>
                                            <select name="province_id" id="province" class="form-select select2" required>
                                                <option value="">-- Pilih Provinsi --</option>
                                                @foreach($provinces as $province)
                                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Kabupaten/Kota </label>
                                            <select name="city_id" id="city" class="form-select select2" required>
                                                <option value="city">-- Pilih Kota --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Kecamatan </label>
                                            <select name="district_id" id="district" class="form-select select2" required>
                                                <option value="district">-- Pilih Kecamatan --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Desa </label>
                                            <select name="sub_district_id" id="sub_district" class="form-select select2" required>
                                                <option value="sub_district">-- Pilih Desa --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Kode Pos </label>
                                            <select name="postal_code_id" id="postal_code" class="form-select select2" required>
                                                <option value="postal_code">-- Pilih Desa --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nama Ayah </label>
                                            <input type="text" class="form-control @error('father_name') is-invalid @enderror" id="father_name" name="father_name" value="{{ old('father_name') }}" required>
                                            @error('father_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nomor HP Ayah </label>
                                            <input type="number" class="form-control @error('father_phone') is-invalid @enderror" id="father_phone" name="father_phone" value="{{ old('father_phone') }}" required>
                                            @error('father_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nama Bunda </label>
                                            <input type="text" class="form-control @error('mother_name') is-invalid @enderror" id="mother_name" name="mother_name" value="{{ old('mother_name') }}" required>
                                            @error('mother_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nomor HP Bunda </label>
                                            <input type="number" class="form-control @error('mother_phone') is-invalid @enderror" id="mother_phone" name="mother_phone" value="{{ old('mother_phone') }}" required>
                                            @error('mother_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>Nomor HP Siswa</label>
                                            <input type="number" class="form-control @error('student_phone') is-invalid @enderror" id="student_phone" name="student_phone" value="{{ old('student_phone') }}">
                                            @error('student_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                         <div class="col-md-6 mb-3">
                                            <label for="photo" class="form-label">Upload Photo</label>
                                            <input type="file" name="photo" class="form-control" accept="image/*">
                                            @error('photo')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                    </div>

                                    {{-- SECTION 2: Data Kontak --}}
                                    <h2 class="mt-4 mb-3">Data Sekolah</h2>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label class="required">Nama Sekolah Asal </label>
                                            <input type="text" class="form-control @error('previous_school') is-invalid @enderror" id="previous_school" name="previous_school" value="{{ old('previous_school') }}" required>
                                            @error('previous_school')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Kelas </label>
                                            <input type="text" class="form-control @error('grade') is-invalid @enderror" id="grade" name="grade" value="{{ old('grade') }}" required>
                                            @error('grade')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="required">Status </label>
                                            <select name="status" class="form-select select2">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="Aktif">Aktif</option>
                                                <option value="Alumni">Alumni</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="required">Tanggal Pendaftaran </label>
                                            <input type="date" name="registered_date" class="form-control" required
                                                value="{{ old('registered_date') }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Tau Darimana </label>
                                            <select name="where_know" class="form-select select2">
                                                <option value="">-- Pilih Info --</option>
                                                <option value="1">Teman/Keluarga</option>
                                                <option value="2">Website AHA</option>
                                                <option value="3">Instagram</option>
                                                <option value="4">Tiktok</option>
                                                <option value="5">Facebook</option>
                                                <option value="6">Youtube</option>
                                                <option value="7">Whatsapp</option>
                                                <option value="8">Banner</option>
                                                <option value="9">Kantor AHA</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Submit Button --}}
                                        <div class="text-end">
                                        <button type="submit" class="btn btn-primary text-white">Simpan</button>
                                        </div>
                                </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

                                    @push('js')
                                        <script>
                                            $(document).ready(function() {
                                                $('.select2').select2({
                                                    placeholder: "-- Pilih --",
                                                    width: '100%'
                                                });
                                            });
                                        </script>

<script>
    $(document).ready(function () {
        const $licenseSelect = $('select[name="license_id"]');
        const $nisInput = $('input[name="nis"]');

        // Fungsi AJAX untuk ambil NIS
        function generateNis(licenseId) {
            $.ajax({
                url: `/students/generate-nis/${licenseId}`, // pastikan endpoint ini sesuai
                type: 'GET',
                success: function (response) {
                    if (response.nis) {
                        $nisInput.val(response.nis);
                    } else {
                        $nisInput.val('');
                    }
                },
                error: function () {
                    console.error("Gagal mengambil NIS.");
                    $nisInput.val('');
                }
            });
        }

        // Event saat lisensi dipilih
        $licenseSelect.on('change', function () {
            const licenseId = $(this).val();
            if (licenseId) {
                generateNis(licenseId);
            } else {
                $nisInput.val('');
            }
        });

        // Untuk halaman edit: generate NIS hanya kalau input masih kosong
        if ($licenseSelect.val() && !$nisInput.val()) {
            generateNis($licenseSelect.val());
        }

    });
</script>

                                        <script>
                                            $('#province').change(function () {
                                            var id = $(this).val();
                                            $('#city').html('<option>Loading...</option>');
                                            $('#district').html('<option value="">-- Pilih kecamatan --</option>');
                                            $('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

                                            if (id) {
                                            $.get('/api/cities/' + id, function (data) {
                                            $('#city').empty().append('<option value="">-- Pilih city --</option>');
                                            $.each(data, function (i, city) {
                                                $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
                                                    });
                                                });
                                                }
                                            });

                                            $('#city').change(function () {
                                            var id = $(this).val();
                                            $('#district').html('<option>Loading...</option>');
                                            $('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

                                            if (id) {
                                                $.get('/api/districts/' + id, function (data) {
                                                    $('#district').empty().append('<option value="">-- Pilih kecamatan --</option>');
                                                    $.each(data, function (i, district) {
                                                        $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                                                            });
                                                        });
                                                    }
                                                });

                                            $('#district').change(function () {
                                            var id = $(this).val();
                                            $('#sub_district').html('<option>Loading...</option>');

                                                if (id) {
                                                    $.get('/api/sub_districts/' + id, function (data) {
                                                        $('#sub_district').empty().append('<option value="">-- Pilih kelurahan --</option>');
                                                        $.each(data, function (i, sub_district) {
                                                            $('#sub_district').append('<option value="' + sub_district.id + '">' + sub_district.name + '</option>');
                                                        });
                                                    });
                                                }
                                            });

                                            $('#sub_district').change(function () {
                                            var id = $(this).val();
                                            $('#postal_code').html('<option>Loading...</option>');

                                            if (id) {
                                                $.get('/api/postal_codes/' + id, function (data) {
                                                    $('#postal_code').empty().append('<option value="">-- Pilih kode pos --</option>');
                                                    $.each(data, function (i, postal_code) {
                                                        $('#postal_code').append('<option value="' + postal_code.id + '">' + postal_code.postal_code + '</option>');
                                                    });
                                                });
                                              }
                                         });
                                        </script>
                                    @endpush