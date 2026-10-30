@extends('tablar::page') {{-- Atau sesuaikan dengan layout Tabler kamu --}}

@section('content')
<style>
    /* Tab horizontal, bisa digeser di mobile */
    .holder-tabs {
        position: relative;
    }
    .holder-tabs .tabs-scroll {
        display: flex;
        flex-wrap: nowrap;
        gap: .25rem;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x proximity;
        scrollbar-width: none;           /* Firefox */
        padding: 0 .5rem;
    }
    .holder-tabs .tabs-scroll::-webkit-scrollbar { display: none; } /* Chrome/Safari */

    .holder-tabs .tab-link {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .9rem 1rem;
        font-weight: 500;
        white-space: nowrap;
        color: var(--tblr-secondary, #667382);
        text-decoration: none;
        border-bottom: 3px solid transparent;
        scroll-snap-align: center;
        transition: color .15s, border-color .15s, background-color .15s;
    }
    .holder-tabs .tab-link:hover {
        color: #206bc4;
        background: rgba(32, 107, 196, .04);
    }
    .holder-tabs .tab-link.active {
        color: #206bc4;
        border-bottom-color: #206bc4;
    }
    .holder-tabs .tab-link .ti { font-size: 1.15rem; }

    /* Gradien penanda masih ada tab yang bisa digeser */
    .holder-tabs::before,
    .holder-tabs::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 28px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s;
        z-index: 2;
    }
    .holder-tabs::before {
        left: 0;
        background: linear-gradient(to right, var(--tblr-card-bg, #fff), rgba(255, 255, 255, 0));
    }
    .holder-tabs::after {
        right: 0;
        background: linear-gradient(to left, var(--tblr-card-bg, #fff), rgba(255, 255, 255, 0));
    }
    .holder-tabs.has-left::before  { opacity: 1; }
    .holder-tabs.has-right::after  { opacity: 1; }

    @media (max-width: 575.98px) {
        .holder-tabs .tab-link { padding: .8rem .85rem; font-size: .9rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <div>
                        <h2>Detail Pemilik Lisensi</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Tab Navigasi (di atas, bisa digeser di mobile) --}}
        <div class="card mb-3">
            <div class="holder-tabs" id="holder-tabs">
                <nav class="tabs-scroll" role="tablist" aria-label="Menu pemilik lisensi">
                    <a href="#" role="tab" class="tab-link"
                        data-tab="licenses"
                        data-url="{{ route('license_holders.licenses', $license_holder->id) }}">
                        <i class="ti ti-license"></i> Data Lisensi
                    </a>

                    <a href="#" role="tab" class="tab-link"
                        data-tab="profile"
                        data-url="{{ route('license_holders.profile', $license_holder->id) }}">
                        <i class="ti ti-user"></i> Data Diri
                    </a>

                    <a href="#" role="tab" class="tab-link"
                        data-tab="educations"
                        data-url="{{ route('license_holders.educations', $license_holder->id) }}">
                        <i class="ti ti-school"></i> Riwayat Pendidikan
                    </a>

                    <a href="#" role="tab" class="tab-link"
                        data-tab="workers"
                        data-url="{{ route('license_holders.workers', $license_holder->id) }}">
                        <i class="ti ti-briefcase"></i> Riwayat Pekerjaan
                    </a>

                    <a href="#" role="tab" class="tab-link"
                        data-tab="families"
                        data-url="{{ route('license_holders.families', $license_holder->id) }}">
                        <i class="ti ti-users"></i> Data Keluarga
                    </a>
                </nav>
            </div>
        </div>

        {{-- Konten Dinamis --}}
        <div id="tab-content">
            @if (request('tab') == 'profile')
                @include('license_holders.tab.profile')
            @else
                @include('license_holders.tab.licenses')
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    const $wrapper = $('#holder-tabs');
    const scroller = $wrapper.find('.tabs-scroll')[0];

    // Tampilkan gradien penanda di sisi yang masih bisa digeser
    function updateScrollHints() {
        const max = scroller.scrollWidth - scroller.clientWidth;
        $wrapper.toggleClass('has-left', scroller.scrollLeft > 4);
        $wrapper.toggleClass('has-right', scroller.scrollLeft < max - 4);
    }
    $(scroller).on('scroll', updateScrollHints);
    $(window).on('resize', updateScrollHints);
    updateScrollHints();

    // Geser tab aktif ke tengah (tanpa menggulung halaman secara vertikal)
    function centerActiveTab(el) {
        if (el && el.scrollIntoView) {
            el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }

    // Klik tab
    $('.tab-link').on('click', function (e) {
        e.preventDefault();

        const $link = $(this);
        const url = $link.data('url');
        const tab = $link.data('tab');
        const $container = $('#tab-content');

        // Simpan tab aktif
        try { localStorage.setItem('activeTab', tab); } catch (err) {}

        // Set active class
        $('.tab-link').removeClass('active').attr('aria-selected', 'false');
        $link.addClass('active').attr('aria-selected', 'true');
        centerActiveTab(this);

        // Loading
        $container.html(
            '<div class="card"><div class="card-body text-center py-5">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<div class="text-muted mt-2">Memuat...</div></div></div>'
        );

        // Pakai path relatif agar otomatis mengikuti http/https halaman saat ini
        const target = new URL(url, window.location.origin);
        $.get(target.pathname + target.search, function (res) {
            $container.html(res);
        }).fail(function () {
            $container.html('<div class="alert alert-danger">Gagal memuat data.</div>');
        });
    });

    // Tentukan tab awal: ?tab= di URL, lalu localStorage, lalu tab pertama
    let initial = new URLSearchParams(window.location.search).get('tab');
    if (!initial) {
        try { initial = localStorage.getItem('activeTab'); } catch (err) {}
    }

    let $initialLink = initial ? $('.tab-link[data-tab="' + initial + '"]') : $();
    if (!$initialLink.length) $initialLink = $('.tab-link').first();
    $initialLink.trigger('click');
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