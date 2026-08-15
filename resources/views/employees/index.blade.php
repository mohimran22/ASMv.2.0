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
                        Karyawan
                    </h2>
                </div>
                <!-- Page title actions -->
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                    @can('karyawan.tambah')
                        <span class="d-none d-sm-inline">
                            <a href="{{ route("employees.create") }}" class="btn btn-primary text-white d-none d-sm-inline-block" >
                                <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Tambah Data Karyawan
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
                                 Data Karyawan
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="tableEmployees" class="table card-table table-vcenter text-nowrap" >
                                <thead>
                                    <tr>
                                        <th class="w-1">No.</th>
                                        <th>NIK</th>
                                        <th>Jenis Lisensi</th>
                                        <th>Nama Lisensi</th>
                                        <th>Nama Karyawan</th>
                                        <th>Panggilan</th>
                                        <th>Jenis kelamin</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Status Pernikahan</th>
                                        <th>Agama</th>
                                        <th>No KTP</th>
                                        <th>Email</th>
                                        <th>Alamat</th>
                                        <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Kode Pos</th>
                                        <th>Telepon</th>
                                        <th>Jabatan</th>
                                        <th>Departemen</th>
                                        <th>Unit Kerja</th>
                                        <th>Status Karyawan</th>
                                        <th>Tanggal Mlai Kerja</th>
                                        <th>Gaji Pokok</th>
                                        <th>Tunjangan</th>
                                        <th>Potongan</th>
                                        <th>Bonus</th>
                                        <th>THR</th>
                                        <th>Surat Perjanjian Kerja</th>
                                        <th>Sertifikat Instruktur</th>
                                        <th>Tanggal Expired Sertifikat</th>
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
@endsection

@push('js')
    <script>
        $(function() {
            const table = $('#tableEmployees').DataTable({
                columnDefs: [
                    {
                        targets: [0, 10, 17, 18],
                        className: 'dt-left'
                    },
                ],
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: {
                    leftColumns: 5
                },
                serverSide: true,
                processing: true,
                ajax: {
                    url: '{{ route("employees.datatable") }}',
                    type: 'POST',
                    data: function (d) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nik', name: 'employees.nik' }, 
                    { data: 'license_type', name: 'licenses.license_type' }, 
                    { data: 'license_name', name: 'licenses.name' },
                    { data: 'fullname', name: 'employees.fullname' },
                    { data: 'nickname', name: 'employees.nickname' },
                    { data: 'gender', name: 'employees.gender' },  
                    { data: 'birth_place', name: 'employees.birth_place' },
                    { data: 'birth_date', name: 'employees.birth_date' },  
                    { data: 'marital_status', name: 'employees.marital_status' },  
                    { data: 'religion_name', name: 'religions.name' },  
                    { data: 'identity_number', name: 'employees.identity_number' },  
                    { data: 'users_email', name: 'users.email' },
                    { data: 'address', name: 'employees.address' },  
                    { data: 'province_name', name: 'provinces.name' }, 
                    { data: 'city_name', name: 'cities.name' },  
                    { data: 'district_name', name: 'districts.name' },
                    { data: 'sub_district_name', name: 'sub_districts.name' },  
                    { data: 'postal_code', name: 'postal_codes.postal_code' },  
                    { data: 'phone', name: 'employees.phone' },  
                    { data: 'position', name: 'employees.position' },  
                    { data: 'department', name: 'employees.department' },
                    { data: 'unit', name: 'employees.unit' },  
                    { data: 'employment_status', name: 'employees.employment_status' },
                    { data: 'start_date', name: 'employees.start_date' },  
                    { data: 'basic_salary', name: 'employees.basic_salary' },
                    { data: 'allowance', name: 'employees.allowance' },  
                    { data: 'deduction', name: 'employees.deduction' },  
                    { data: 'bonus', name: 'employees.bonus' },  
                    { data: 'thr', name: 'employees.thr' },  
                    { data: 'contract_letter_file', name: 'employees.contract_letter_file' },
                    { data: 'instructure_certificate', name: 'employees.instructure_certificate' }, 
                    { data: 'expired_date_certificate', name: 'employees.expired_date_certificate' },  
                    { data: 'action', name: 'action', orderable: false, searchable: false }  
                ],
            });

            // Delete user functionally
            $('table').on('click', '.delete-employee', function () {
            const employeeId = $(this).data('id');

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

                        url: `/employees/${employeeId}`,
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