@extends('tablar::page')

@section('content')
<div class="container-fluid">
    <h1>Daftar Jurnal</h1>

    <a href="{{ route('journals.create') }}" class="btn btn-primary text-white mb-3">Tambah Jurnal</a>
    <div class="table-responsive">
        <table id="journals-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tipe Lisensi</th>
                    <th>Nama Lisensi</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal Dibuat</th>
                    <th>PIC</th>
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
    const table = $('#journals-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("journals.index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'license_type', name: 'licenses.license_type' },
            { data: 'license_name', name: 'licenses.name' },
            { data: 'journal_code', name: 'accounting_journals.journal_code' },
            { data: 'transaction_date', name: 'accounting_journals.transaction_date' },
            { data: 'creator', name: 'users.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    $('table').on('click', '.delete-journal', function () {
            const journalId = $(this).data('id');

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

                        url: `https://asm.aharightbrain.com/journals/${journalId}`,
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
