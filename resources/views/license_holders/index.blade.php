@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                {{-- <div class="col">
                    <div class="page-pretitle">
                        Overview
                    </div>
                    <h2 class="page-title">
                        Pemilik Lisensi
                    </h2>
                </div> --}}
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                 @can('pemilik-lisensi.tambah')       
                  <span class="d-none d-sm-inline">
                        <a href="{{ route("license_holders.create") }}" class="btn btn-primary text-white d-none d-sm-inline-block" >
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Data Pemilik
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
                                Daftar Pemilik Lisensi
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="tableLicenseHolders" class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>ID Lisensi</th>
                                        <th>Tipe Lisensi</th>
                                        <th>Nama Lisensi</th>
                                        <th>Nama Pemilik</th>
                                        <th>Panggilan</th>
                                        <th>Jenis kelamin</th>
                                        <th>Agama</th>
                                        <th>Nomor KTP</th>
                                        <th>Nomor SIM</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Alamat</th>
                                        <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Kode Pos</th>
                                        <th>Telepon</th>
                                        <th>Hobi</th>
                                        <th>Status Pernikahan</th>
                                        <th>Tanggal Pernikahan</th>
                                        <th>Bahasa Indonesia(Baca/Tulis)</th>
                                        <th>Bahasa Indonesia(Bicara)</th>
                                        <th>Bahasa Arab (Baca/Tulis)</th>
                                        <th>Bahasa Arab (Bicara)</th>
                                        <th>Bahasa Inggris (Baca/Tulis)</th>
                                        <th>Bahasa Inggris (Bicara)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @can('pemilik-lisensi.tambah')
<a href="{{ route('license_holders.create') }}"
   class="mobile-fab d-md-none">

    <svg xmlns="http://www.w3.org/2000/svg"
         width="26"
         height="26"
         viewBox="0 0 24 24"
         stroke-width="2"
         stroke="currentColor"
         fill="none"
         stroke-linecap="round"
         stroke-linejoin="round">

        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>

    </svg>

</a>
@endcan
@endsection

@push('js')
    <script>
        $(function() {
            const isMobile = window.innerWidth < 576;
            const table = $('#tableLicenseHolders').DataTable({
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: !isMobile ? {
                    leftColumns: 4
                } : false,
                serverSide: true,
                processing: true,
                responsive: false,
                ajax: '{{ route("license_holders.index") }}',
                columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'license_id', name: 'licenses.license_id' },
                { data: 'license_type', name: 'licenses.license_type' },
                { data: 'license_name', name: 'licenses.name' },
                { data: 'license_holder_name', name: 'license_holders.fullname' },
                { data: 'nickname', name: 'license_holders.nickname' },
                { data: 'gender', name: 'license_holders.gender' },
                { data: 'religion_name', name: 'religions.name' },
                { data: 'identity_number', name: 'license_holders.identity_number' },
                { data: 'driver_license_number', name: 'license_holders.driver_license_number' },
                { data: 'birth_place', name: 'license_holders.birth_place' },
                { data: 'birth_date', name: 'license_holders.birth_date' },
                { data: 'address', name: 'license_holders.address' },
                { data: 'province_name', name: 'provinces.name' },
                { data: 'city_name', name: 'cities.name' },
                { data: 'district_name', name: 'districts.name' },
                { data: 'sub_district_name', name: 'sub_districts.name' },
                { data: 'postal_code', name: 'postal_codes.postal_code' },
                { data: 'phone', name: 'license_holders.phone' },
                { data: 'hobby', name: 'license_holders.hobby' },
                { data: 'marital_status', name: 'license_holders.marital_status' },
                { data: 'married_date', name: 'license_holders.married_date' },
                { data: 'indonesian_literacy', name: 'license_holders.indonesian_literacy' },
                { data: 'indonesian_proficiency', name: 'license_holders.indonesian_proficiency' },
                { data: 'arabic_literacy', name: 'license_holders.arabic_literacy' },
                { data: 'arabic_proficiency', name: 'license_holders.arabic_proficiency' },
                { data: 'english_literacy', name: 'license_holders.english_literacy' },
                { data: 'english_proficiency', name: 'license_holders.english_proficiency' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[2, 'asc']], // misal urutkan nama license_holder
            columnDefs: [
                { width: '50px', targets: 0 },
                { width: '150px', targets: 2 },
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari pemilik lisensi...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "›",
                    previous: "‹"
                }
            },
            initComplete: function () {
                const input = $('.dt-search input');
                input.removeClass('form-control-sm')
                    .addClass('form-control');
            }
            });

            // Delete user functionally
            $('table').on('click', '.delete-license_holder', function () {
            const license_holderId = $(this).data('id');

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

                        url: `/license_holders/${license_holderId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },

                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Pemilik telah dihapus.',
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

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
@endpush