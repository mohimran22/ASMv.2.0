@extends('tablar::page')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            {{-- Sidebar Navigasi --}}
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="list-group list-group-transparent">
                            <a href="#" 
                                class="list-group-item list-group-item-action"
                                data-tab="licenses"
                                data-url="{{ route('license_holders.licenses', $license_holder->id) }}">
                                    Data Lisensi
                            </a>
                            
                            <a href="#" 
                                class="list-group-item list-group-item-action"
                                data-tab="profile"
                                data-url="{{ route('license_holders.profile', $license_holder->id) }}">
                                    Data Diri
                            </a>

                            <a href="#" 
                                class="list-group-item list-group-item-action"
                                data-tab="educations"
                                data-url="{{ route('license_holders.educations', $license_holder->id) }}">
                                    Riwayat Pendidikan
                            </a>

                            <a href="#" 
                                class="list-group-item list-group-item-action"
                                data-tab="workers"
                                data-url="{{ route('license_holders.workers', $license_holder->id) }}">
                                    Riwayat Pekerjaan
                            </a>

                            <a href="#" 
                                class="list-group-item list-group-item-action"
                                data-tab="families"
                                data-url="{{ route('license_holders.families', $license_holder->id) }}">
                                    Data Keluarga
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Konten Dinamis --}}

            <div class="col-md-9" id="tab-content">
                @if (request('tab') == 'profile')
                    @include('license_holders.tab.profile')
                @else
                    @include('license_holders.tab.licenses')
                @endif
            </div>


        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    // Klik menu tab
    $('.list-group-item').click(function (e) {
        e.preventDefault();

        const url = $(this).data('url');
        const tab = $(this).data('tab');
        const $container = $('#tab-content');

        // Simpan tab aktif di localStorage
        localStorage.setItem('activeTab', tab);

        // Set active class
        $('.list-group-item').removeClass('active');
        $(this).addClass('active');

        // Loading spinner
        $container.html('<div class="text-center p-3">Memuat...</div>');

        // Load konten via AJAX
        const path = new URL(url, window.location.origin).pathname;

        $.get(path, function (res) {
            $container.html(res);
        }).fail(function () {
            $container.html('<div class="alert alert-danger">Gagal memuat data.</div>');
        });
    });

    // Restore tab aktif dari localStorage
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $(`a[data-tab="${activeTab}"]`).trigger('click');
    } else {
        // Default ke tab pertama (Data Lisensi)
        $('.list-group-item').first().trigger('click');
    }
});

// Konfirmasi hapus dengan SweetAlert
function confirmDelete(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Hapus data?',
        text: "Data akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $(event.target).closest('form').submit();
        }
    });
}
</script>
@endsection
