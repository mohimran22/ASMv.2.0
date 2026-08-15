@extends('tablar::page')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Akun</h1>

    <a href="{{ route("accounting.create") }}" class="btn btn-primary text-white mb-3">Tambah Akun</a>
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

        order: [
            [2, 'asc']
        ],

        scrollX: true,

    });

});
</script>
@endpush