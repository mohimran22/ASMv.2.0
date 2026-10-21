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
                        Siswa
                    </h2>
                </div> --}}
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                 @can('siswa.tambah')       
                  <span class="d-none d-sm-inline">
                        <a href="{{ route("students.create") }}" class="btn btn-primary text-white d-none d-sm-inline-block" >
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Tambah Data Siswa
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
                                Daftar Siswa
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="tableStudents" class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th class="w-1">No.</th>
                                        <th>NIS</th>
                                        <th>Tipe Lisensi</th>
                                        <th>Nama Lisensi</th>
                                        <th>Nama Siswa</th>
                                        <th>Panggilan</th>
                                        <th>Jenis kelamin</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Umur</th>
                                        <th>Agama</th>
                                        <th>Email</th>
                                        <th>Alamat</th>
                                        <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Kode Pos</th>
                                        <th>Nama Ayah</th>
                                        <th>Nomor HP Ayah</th>
                                        <th>Nama Bunda</th>
                                        <th>Nomor HP Bunda</th>
                                        <th>Nomor HP Siswa</th>
                                        <th>Asal Sekolah</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                        <th>Tau Darimana</th>
                                        <th>Tanggal Pendaftaran</th>
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
@can('siswa.tambah')
<a href="{{ route('students.create') }}"
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
            const table = $('#tableStudents').DataTable({
                columnDefs: [
                    {
                        targets: [0, 16, 18],
                        className: 'dt-right'
                    },
                ],
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: !isMobile ? {
                    leftColumns: 5
                } : false,
                serverSide: true,
                processing: true,
                responsive: false,
                ajax: '{{ route("students.index") }}',
                columns: 
                [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nis', name: 'nis' },
                { data: 'license_type', name: 'licenses.license_type' },
                { data: 'license_name', name: 'licenses.name' },
                { data: 'fullname', name: 'fullname' },
                { data: 'nickname', name: 'nickname' },
                { data: 'gender', name: 'gender' },
                { data: 'birth_place', name: 'birth_place' },
                { data: 'birth_date', name: 'birth_date' },
                { data: 'age', name: 'age' },
                { data: 'religion_name', name: 'religions.name' },
                { data: 'email', name: 'email' },
                { data: 'address', name: 'address' },
                { data: 'province_name', name: 'provinces.name' },
                { data: 'city_name', name: 'cities.name' },
                { data: 'district_name', name: 'districts.name' },
                { data: 'sub_district_name', name: 'sub_districts.name' },
                { data: 'postal_code', name: 'postal_codes.postal_code' },
                { data: 'father_name', name: 'father_name' },
                { data: 'father_phone', name: 'father_phone' },
                { data: 'mother_name', name: 'mother_name' },
                { data: 'mother_phone', name: 'mother_phone' },
                { data: 'student_phone', name: 'student_phone' },
                { data: 'previous_school', name: 'previous_school' },
                { data: 'grade', name: 'grade' },
                { data: 'status', name: 'status' },
                { data: 'where_know', name: 'where_know' },
                { data: 'registered_date', name: 'registered_date' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari siswa...",
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
            $('table').on('click', '.delete-student', function () {
            const studentId = $(this).data('id');

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

                        url: `/students/${studentId}`,
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
@endpush