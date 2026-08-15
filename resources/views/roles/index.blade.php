@extends('tablar::page')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3>Role Management</h3>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">+ Tambah</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="roleTable">
            <thead>
                <tr>
                    <th>Nama Role</th>
                    <th>Permission Dipakai</th>
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
        $('#roleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
            url: '{{ route("roles.index") }}',
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'permissions_count', name: 'permissions_count' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        $('table').on('click', '.delete-role', function () {
    const deleteRoleId = $(this).data('id');

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
                url: `/roles/${deleteRoleId}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // pastikan ada di <head>
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        table.ajax.reload(null, false); // reload datatable
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

