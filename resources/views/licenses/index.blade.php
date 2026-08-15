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
                    <h2 class="page-title">
                        Lisensi
                    </h2>
                </div>
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                @can('lisensi.tambah')
                  <span class="d-none d-sm-inline">
                  
                        <a href="{{ route("licenses.create") }}" class="btn btn-primary text-white d-none d-sm-inline-block" >
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Data Lisensi
                        </a>
                    </span>
                @endcan
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
                                 Lisensi
                            </p>
                        </div>
                        <div class="table-wrapper">
                            <table id="tableLicenses" class="table nowrap w-100" >
                                <thead>
                                    <tr>
                                        <th class="fixed-column">No</th>
                                        <th class="fixed-column">Id Lisensi</th>
                                        <th class="fixed-column">Tipe Lisensi</th>
                                        <th class="fixed-column">Nama</th>
                                        <th>Email</th>
                                        <th>Alamat</th>
                                        <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Kode Pos</th>
                                        <th>Telepon</th>
                                        <th>Tanggal Bergabung</th>
                                        <th>Expired date</th>
                                        <th>Nomor Aqad</th>
                                        <th>Dokumen Aqad</th>
                                        <th>Dokumen Form Lisensi</th>
                                        <th>Status</th>
                                        <th>Tipe Bangunan</th>
                                        <th>Status Bangunan</th>
                                        <th>Tanggal Expired Sewa Bangunan</th>
                                        <th>Luas Bangunan</th>
                                        <th>Kondisi Bangunan</th>
                                        <th>Bangunan Punya AC?</th>
                                        <th>Instagram</th>
                                        <th>Halaman Facebook</th>
                                        <th>Tiktok</th>
                                        <th>Youtube</th>
                                        <th>Google Maps</th>
                                        <th>Landing Page Pendaftaran Siswa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    console.log('APP URL:', @json(config('app.url')));
    console.log('Current URL:', window.location.href);
    console.log('License URL:', @json(url('/licenses')));
</script>
    <script>
        $(function() {
            const table = $('#tableLicenses').DataTable({
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: {
                    leftColumns: 4
                },
                processing: true,
                serverSide: true, 
                ajax: '{{ route("licenses.index") }}',
                // ajax: {
                //     url: 'https://asm.aharightbrain.com/licenses', // ganti dengan URL API Laravel di Railway
                //     type: 'GET',
                //     xhrFields: {
                //         withCredentials: true // biar cookie session kebawa
                //     },
                //     error: function (xhr, error, thrown) {
                //         console.error("❌ AJAX Error:", error, thrown);
                //         console.log("📄 Response Text:", xhr.responseText);
                //         alert("Gagal memuat data! Cek console untuk detail error.");
                //     }
                // },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'license_id', name: 'license_id' },
                    { data: 'license_type', name: 'license_type' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email'},
                    { data: 'address', name: 'address' },
                    { data: 'province_name', name: 'province.name' },
                    { data: 'city_name', name: 'city.name'},
                    { data: 'district_name', name: 'district.name' },
                    { data: 'sub_district_name', name: 'sub_district_name' },
                    { data: 'postal_code', name: 'postal_code' },
                    { data: 'phone', name: 'phone'},
                    { data: 'join_date', name: 'join_date' },
                    { data: 'expired_date', name: 'expired_date' },
                    { data: 'contract_agreement_number', name: 'contract_agreement_number'},
                    { data: 'contract_document', name: 'contract_document'},
                    { data: 'document_form', name: 'document_form'},
                    { data: 'status', name: 'status' },
                    { data: 'building_type', name: 'building_type' },
                    { data: 'building_status', name: 'building_status'},
                    { data: 'building_rent_expired_date', name: 'building_rent_expired_date' },
                    { data: 'building_area', name: 'building_area' },
                    { data: 'building_condition', name: 'building_condition'},
                    { data: 'building_has_ac', name: 'building_has_ac' },
                    { data: 'instagram', name: 'instagram' },
                    { data: 'facebook_page', name: 'facebook_page'},
                    { data: 'tiktok', name: 'tiktok' },
                    { data: 'youtube', name: 'youtube' },
                    { data: 'google_maps', name: 'google_maps'},
                    { data: 'landing_page_student_registration', name: 'landing_page_student_registration' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
    
            });

            // Delete user functionally
            $('table').on('click', '.delete-license', function () {
            const licenseId = $(this).data('id');

            Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data akan hilang secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'

            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({

                        url: `/licenses/${licenseId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },

                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Lisensi telah dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                            });

                        table.ajax.reload(null, false); // refresh datatable
                        } else {

                            Swal.fire('Gagal', response.message || 'Tidak bisa menghapus data.', 'error');
                        }
                        },

                    error: function () {

                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                    }

                    });
                }
            });
            });


           
        });
    </script>
@endpush