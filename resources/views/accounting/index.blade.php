@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">

                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @can('akun-akuntansi.tambah')       
                            <span class="d-none d-sm-inline">
                                    <a href="{{ route("accounting.create") }}" class="btn btn-primary text-white d-none d-sm-inline-block" >
                                        <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                        Tambah Data Akun
                                    </a>
                            </span>
                        @endcan      
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-center mb-4">
                                Daftar Akun
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="tableAccounts" class="table card-table table-vcenter text-nowrap" >
                                <thead>
                                    <tr>
                                        <th>Tipe Lisensi</th>
                                        <th>Nama Lisensi</th>
                                        <th>Kode Akun</th>
                                        <th>Nama Akun</th>
                                        <th>Kategori</th>
                                        <th>Sub Kategori</th>
                                        <th>Saldo Awal</th>
                                        {{-- <th>Akun Induk</th> --}}
                                        <th>Status</th>
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
    @can('akun-akuntansi.tambah')
<a href="{{ route('accounting.create') }}"
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
$(function () {

    $('#tableAccounts').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url: '{{ route("accounting.datatable") }}',
            type: 'POST',

            data: function (d) {
                d._token = '{{ csrf_token() }}';
            }
        },

        columns: [

            {
                data: 'license_type',
                name: 'licenses.license_type'
            },

            {
                data: 'license_name',
                name: 'licenses.name'
            },

            {
                data: 'account_code',
                name: 'accounting_accounts.account_code'
            },

            {
                data: 'account_name',
                name: 'accounting_accounts.account_name'
            },

            {
                data: 'category',
                name: 'accounting_accounts.category'
            },

            {
                data: 'sub_category',
                name: 'accounting_accounts.sub_category'
            },

            {
                data: 'initial_balance',
                name: 'accounting_accounts.initial_balance'
            },

            {
                data: 'is_active',
                name: 'accounting_accounts.is_active',
                orderable: true,
                searchable: false
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }

        ],
            language: {
                search: "",
                searchPlaceholder: "Cari akun...",
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
            },
        order: [
            [2, 'asc']
        ],

        scrollX: true,

    });

});
</script>
@endpush