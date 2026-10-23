<aside class="{{$layoutData['cssClasses'] ?? 'navbar navbar-vertical navbar-expand-lg'}}"
       @if(config('tablar.layout_light_sidebar') !== null)
           data-bs-theme="{{ config('tablar.layout_light_sidebar') ? 'light' : 'dark' }}"
       @endif
>
    <div class="sidebar-inner">
        <div class="sidebar-header">
            <div class="sidebar-logo-wrapper">
                <div class="logo-expand">
                    @include('tablar::partials.common.logo')
                </div>
                {{-- Belum ada logo ikon AHA: pakai logo yang sama, diperkecil.
                     Ganti dengan <img src="{{ asset('images/aha-icon.png') }}"> kalau sudah ada. --}}
                <div class="logo-collapse">
                    <img src="{{ asset('ahasquare.png') }}">
                </div>
            </div>
            <button id="sidebarToggle" class="sidebar-toggle-btn d-none d-lg-flex" type="button">
                <i class="ti ti-layout-sidebar-left-collapse"></i>
            </button>
        </div>

        <div class="sidebar-menu-wrapper" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @include('partials.menu_item', ['menus' => \App\Services\SidebarMenu::forUser(auth()->user())])
            </ul>
        </div>
    </div>
</aside>

@if(config('tablar.layout_enable_top_header'))
    @include('tablar::partials.header.sidebar-top')
@endif

{{-- Header mobile (< 992px): tombol buka sidebar, notifikasi, ganti lisensi, dan menu user.
     sidebar-top bawaan hanya tampil di layar lebar, jadi di mobile header inilah yang dipakai. --}}
<header class="mobile-header d-flex d-lg-none d-print-none">
    <div class="mobile-left">
        <button id="mobileSidebarToggle" type="button" aria-label="Buka menu">
            <i class="ti ti-menu-2"></i>
        </button>
    </div>
    <div class="mobile-right">
        @include('tablar::partials.header.notifications')

        {{-- Ganti lisensi (sama seperti di sidebar-top; hanya untuk selain Super Admin) --}}
        @role('Pemilik Lisensi|Karyawan|Akuntan')
            @php
                $mUser = Auth::user();

                if ($mUser->hasRole('Pemilik Lisensi')) {
                    $mLicenses = $mUser->licenses;
                } elseif ($mUser->hasRole(['Karyawan', 'Akuntan'])) {
                    $mLicenses = $mUser->employee?->licenses ?? collect();
                } else {
                    $mLicenses = collect();
                }
            @endphp
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-2" data-bs-toggle="dropdown" aria-label="Switch License">
                    <i class="ti ti-building"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    @forelse($mLicenses as $license)
                        <a class="dropdown-item {{ session('active_license_id') == $license->id ? 'active fw-normal text-white' : '' }}"
                           href="{{ route('switch.license', $license->id) }}">
                            @if(session('active_license_id') == $license->id)
                                <i class="ti ti-check me-1"></i>
                            @endif
                            {{ $license->name }}
                        </a>
                    @empty
                        <span class="dropdown-item text-muted">Tidak ada lisensi</span>
                    @endforelse
                </div>
            </div>
        @endrole

        @include('tablar::partials.header.top-right')
    </div>
</header>
<div class="sidebar-overlay"></div>

@push('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    const btn  = document.getElementById("sidebarToggle");

    function applyResponsiveSidebar() {
        const w = window.innerWidth;
        if (w < 992)                    root.classList.remove('sidebar-collapsed'); // mobile: off-canvas, tampil penuh saat dibuka
        else if (w <= 1200)             root.classList.add('sidebar-collapsed');
        else root.classList.toggle('sidebar-collapsed', localStorage.getItem('sidebarCollapsed') === 'true');
    }
    applyResponsiveSidebar();
    window.addEventListener('resize', applyResponsiveSidebar);

    if (btn) btn.addEventListener("click", function () {
        root.classList.toggle("sidebar-collapsed");
        localStorage.setItem("sidebarCollapsed", root.classList.contains("sidebar-collapsed"));
    });

    // Sidebar off-canvas di layar < 992px: dibuka lewat tombol hamburger di header mobile
    const sidebar   = document.querySelector('aside.navbar-vertical');
    const overlay   = document.querySelector('.sidebar-overlay');
    const mobileBtn = document.getElementById('mobileSidebarToggle');

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('show-sidebar');
        if (overlay) overlay.classList.remove('show');
    }
    if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show-sidebar');
            if (overlay) overlay.classList.toggle('show');
        });
    }
    if (overlay) overlay.addEventListener('click', closeMobileSidebar);
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeMobileSidebar();
    });

    // Buka/tutup submenu (accordion)
    document.querySelectorAll('[data-submenu]').forEach(link => {
        link.addEventListener('click', function (e) {
            const submenu = document.getElementById(this.dataset.submenu);
            if (!submenu) return;
            if (root.classList.contains('sidebar-collapsed') && window.innerWidth >= 992) return;
            e.preventDefault();
            submenu.classList.toggle('show');
            this.classList.toggle('open');
        });
    });

    // Floating submenu saat sidebar collapsed
    document.querySelectorAll('.has-dropdown').forEach(item => {
        const submenu = item.querySelector('.submenu');
        if (!submenu || window.innerWidth < 992) return;
        let timeout;

        function showMenu() {
            if (!root.classList.contains('sidebar-collapsed')) return;
            clearTimeout(timeout);
            item.classList.add('floating-active');
            const rect = item.getBoundingClientRect();
            submenu.style.top = '0px';
            submenu.classList.add('show-floating');
            let top = rect.top;
            const h = submenu.offsetHeight;
            if (top + h > window.innerHeight - 20) top = window.innerHeight - h - 20;
            if (top < 10) top = 10;
            submenu.style.top  = top + 'px';
            submenu.style.left = (rect.right + 8) + 'px';
        }
        function hideMenu() {
            timeout = setTimeout(() => {
                submenu.classList.remove('show-floating');
                item.classList.remove('floating-active');
            }, 200);
        }
        item.addEventListener('mouseenter', showMenu);
        item.addEventListener('mouseleave', hideMenu);
        submenu.addEventListener('mouseenter', () => clearTimeout(timeout));
        submenu.addEventListener('mouseleave', hideMenu);
    });
});
</script>
@endpush

<style>
:root{
    /* Ganti dua baris ini ke #dccba8 / #c3bbb0 kalau mau persis krem Ruang Kembali */
    --sb-active-bg: #ffcc00;
    --sb-hover-bg:  #fff0b3;
    --sb-text:      #000;
}

/* ===== Kerangka sidebar ===== */
.navbar.navbar-vertical.navbar-expand-lg{
    position: fixed; top:0; bottom:0; left:0;
    width: 260px; margin:0; padding-top:15px;
    display:flex; flex-direction:column;
    color: var(--sb-text) !important;
    background:#fff;
    transition: width .3s ease;
    overflow: visible !important;
    z-index: 1035;
}
.sidebar-collapsed .navbar.navbar-vertical.navbar-expand-lg{ width:80px; }

.sidebar-inner{
    width:100%; height:100vh;
    display:flex; flex-direction:column; align-items:stretch !important;
    overflow-x:visible; overflow-y:auto; position:relative;
}
.sidebar-header{
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 16px; gap:10px;
}
.sidebar-logo-wrapper{ flex:1; display:flex; justify-content:center; align-items:center; background:#fff; }
.logo-expand{ display:block; max-width:140px; }
.logo-expand img, .logo-collapse img{ max-width:100%; height:auto; }
.logo-collapse{ display:none; width:48px; overflow:hidden; }
.sidebar-collapsed .logo-expand{ display:none; }
.sidebar-collapsed .logo-collapse{ display:block; }
.sidebar-collapsed .sidebar-header{ flex-direction:column; padding:16px 0; gap:12px; }

.sidebar-toggle-btn{
    width:36px; height:36px; border:none; background:transparent;
    display:flex; align-items:center; justify-content:center;
    border-radius:8px; cursor:pointer; transition:.2s ease;
}
.sidebar-toggle-btn:hover{ background:#f1f5f9; }
.sidebar-toggle-btn i{ font-size:20px; color:#374151; }

.sidebar-menu-wrapper{
    flex:1; overflow-y:auto; overflow-x:hidden; padding:15px 15px 60px 0;
    scrollbar-width: thin;
}
.sidebar-menu-wrapper::-webkit-scrollbar{ width:4px; }
.sidebar-menu-wrapper::-webkit-scrollbar-thumb{ background:rgba(0,0,0,.2); border-radius:4px; }
.sidebar-menu-wrapper .navbar-nav,
.sidebar-menu-wrapper .nav-item{ width:100%; }
/* Semua menu (dengan/tanpa dropdown) rata kiri; mode collapsed tetap center lewat aturan di bawah */
.sidebar-menu-wrapper .nav-link{ width:100%; justify-content:flex-start !important; text-align:left; }

/* ===== Item menu ===== */
.navbar-nav .nav-item .nav-link{
    display:flex; align-items:center; gap:12px;
    margin:8px 6px; padding:10px 12px;
    border-radius:8px; text-decoration:none;
    color: var(--sb-text) !important; white-space:nowrap;
    transition: all .2s ease-in-out;
}
.navbar-nav .nav-item .nav-link:hover{ background: var(--sb-hover-bg); }
.navbar-nav > .nav-item > .nav-link.active{ background: var(--sb-active-bg); color:#000 !important; }

.nav-link-icon{ width:10%; min-width:10%; text-align:center; }
.nav-link-icon i{ font-size:20px; display:inline-block !important; }

/* Chevron */
.submenu-arrow{
    margin-left:auto; width:8px; height:8px;
    border-right:2px solid currentColor; border-bottom:2px solid currentColor;
    transform:rotate(45deg); transition:.2s ease;
}
.nav-link.open .submenu-arrow{ transform:rotate(-135deg); }
.sidebar-collapsed .submenu-arrow{ display:none; }

/* ===== Submenu ===== */
.submenu{ max-height:0; opacity:0; overflow:visible; transition:max-height .25s ease, opacity .2s ease; }
.submenu.show{ max-height:500px; opacity:1; }
.submenu .nav{ margin-left:0 !important; padding-left:8px; }
.submenu .nav-item{ margin:2px 6px; }
.submenu .nav-link{
    margin:0; width:100%; padding:8px 10px; gap:10px;
    font-size:.9rem; color:#374151 !important; border-radius:8px;
}
.submenu .nav-link i{ font-size:14px; width:16px; min-width:16px; text-align:center; }
.submenu .nav-link:hover{ background:#e4e4e4; }
.submenu .nav-link.active{ background: var(--sb-hover-bg); color:#000 !important; }

/* ===== Collapsed ===== */
.sidebar-collapsed .navbar-nav .nav-link{ justify-content:center !important; padding:12px 16px !important; overflow:visible !important; }
.sidebar-collapsed .nav-link-icon{ display:flex; justify-content:center; width:auto; min-width:20px; margin:0; }
.sidebar-collapsed .nav-link-title{ display:none !important; }
.sidebar-collapsed .nav-item, .sidebar-collapsed .nav-link{ overflow:visible !important; position:relative; }

.sidebar-collapsed .navbar-nav > .nav-item > .nav-link::before{
    content:attr(data-title); position:fixed; left:78px; top:50%; transform:translateY(-50%);
    background:#111827; color:#fff; padding:8px 12px; border-radius:8px; white-space:nowrap;
    opacity:0; visibility:hidden; transition:.2s ease; z-index:100; pointer-events:none;
}
.sidebar-collapsed .navbar-nav > .nav-item > .nav-link:hover::before{ opacity:1; visibility:visible; }
.sidebar-collapsed .nav-item.floating-active > .nav-link::before{ display:none !important; }

.sidebar-collapsed .submenu{
    position:fixed; top:0; left:78px; width:220px; min-width:220px; max-width:220px;
    background:#fff; border-radius:12px; padding:8px;
    box-shadow:0 10px 30px rgba(0,0,0,.12); z-index:101;
    opacity:0; visibility:hidden; pointer-events:none; transition:opacity .15s ease;
}
.sidebar-collapsed .submenu.show-floating{
    opacity:1; visibility:visible; pointer-events:auto;
    max-height:calc(100vh - 20px); overflow-y:auto; overflow-x:hidden;
}
.sidebar-collapsed .submenu.show-floating .nav{ padding-left:0 !important; }
.sidebar-collapsed .submenu.show-floating .nav-item{ margin:2px 0; }
.sidebar-collapsed .submenu.show-floating .nav-link{
    justify-content:flex-start !important; padding:6px 8px !important; font-size:13px; min-height:34px;
}

/* ===== Konten bergeser mengikuti sidebar ===== */
/* Konten memakai variabel yang SAMA dengan header (--hd-left-*, --hd-right), jadi tepi kiri dan kanan
   konten selalu sejajar dengan header. Padding bawaan wrapper/container dinolkan supaya tidak
   menambah jarak diam-diam. */
.page-wrapper{
    margin-left:var(--hd-left-expanded) !important;   /* tandingi margin-left:15rem bawaan Tablar */
    margin-right:var(--hd-right) !important;
    padding-left:0 !important;
    padding-right:0 !important;
    transition:all .3s ease;
}
.sidebar-collapsed .page-wrapper{
    margin-left:var(--hd-left-collapsed) !important;
}
.page-wrapper .page-header,
.page-wrapper .page-body{ padding-left:0 !important; padding-right:0 !important; }
.page-wrapper .container-xl{
    width:100%; max-width:none !important;
    margin-left:0 !important; margin-right:0 !important;
    padding-left:0 !important; padding-right:0 !important;
}

/* ===== Header kartu putih (gaya Ruang Kembali) ===== */
:root{
    /* Jarak kiri header = tepi kiri konten. Sesuaikan kalau tidak sejajar dengan kartu di bawahnya. */
    --hd-left-expanded: 267px;
    --hd-left-collapsed: 111px;
    --hd-right: 30px;
    --hd-top: 16px;          /* jarak atas header (dari tepi atas layar) */
    --hd-gap-bottom: 24px;   /* jarak antara header dan konten di bawahnya */
}
aside.navbar-vertical ~ header.navbar{
    margin: var(--hd-top) var(--hd-right) var(--hd-gap-bottom) var(--hd-left-expanded) !important; /* tandingi margin-left:15rem bawaan Tabler */
    width:auto !important;
    height:70px;
    padding:0 20px;
    background:#fff !important;
    border:0 !important;
    --tblr-navbar-border-width:0px;
    border-radius:12px;
    box-shadow:0 4px 14px rgba(0,0,0,.05);
    align-items:center;
    /* Header ikut terlihat saat halaman di-scroll (di Ruang Kembali pakai position:fixed;
       sticky dipilih supaya tidak menimpa konten AHA) */
    position:sticky;
    top:var(--hd-top);
    z-index:200;
    transition:margin .3s ease;
}
.sidebar-collapsed aside.navbar-vertical ~ header.navbar{
    margin-left:var(--hd-left-collapsed) !important;
}
header.navbar > .container-xl{ padding-left:20px; padding-right:0; justify-content:flex-end; }
header.navbar .d-md-flex{ align-items:center; gap:10px; }

/* Avatar: kotak membulat berbingkai tipis seperti Ruang Kembali */
header.navbar .avatar{
    width:40px; height:40px; line-height:38px;
    border-radius:8px;
    background:#fff;
    border:1px solid #e6e7e9;
}
header.navbar .avatar img{ border-radius:7px; }

/* Dropdown ganti lisensi: kotak berbingkai + chevron seperti dropdown role Ruang Kembali */
header.navbar [aria-label="Switch License"]{
    align-items:center;
    gap:8px;
    padding:10px 16px !important;
    border:1px solid #e6e7e9;
    border-radius:8px;
    background:#fff;
}
header.navbar [aria-label="Switch License"]::after{
    content:"";
    width:7px; height:7px; margin-left:10px;
    border-right:2px solid currentColor;
    border-bottom:2px solid currentColor;
    transform:rotate(45deg) translateY(-2px);
}

/* ===== Tablet ===== */
@media (min-width:992px) and (max-width:1200px){
    #sidebarToggle{ display:none !important; }
}

/* ===== Mobile & tablet portrait (< 992px): sidebar off-canvas + header mobile ===== */
.mobile-header{ display:none; }
.sidebar-overlay{ display:none; }

@media (max-width:991.98px){
    body{ overflow-x:hidden; }

    /* Sidebar: geser keluar layar, dibuka lewat tombol hamburger (class .show-sidebar) */
    .navbar.navbar-vertical.navbar-expand-lg{
        transform:translateX(-100%);
        transition:transform .3s ease !important;
        width:260px !important; max-width:260px;
        overflow-y:auto;
        z-index:1070;
    }
    .navbar.navbar-vertical.navbar-expand-lg.show-sidebar{
        transform:translateX(0);
        box-shadow:0 0 30px rgba(0,0,0,.2);
    }
    .sidebar-overlay{
        display:block;
        position:fixed; inset:0;
        background:rgba(0,0,0,.4);
        opacity:0; visibility:hidden;
        transition:.3s ease;
        z-index:1065;
    }
    .sidebar-overlay.show{ opacity:1; visibility:visible; }

    /* Header mobile */
    .mobile-header{
        position:fixed; top:0; left:0; right:0;
        height:60px; padding:0 12px;
        display:flex; align-items:center; justify-content:space-between;
        background:#fff;
        box-shadow:0 2px 10px rgba(0,0,0,.08);
        z-index:1060;
    }
    .mobile-left, .mobile-right{ display:flex; align-items:center; gap:10px; }
    #mobileSidebarToggle{
        border:none; background:#fff; font-size:24px;
        display:flex; align-items:center; justify-content:center;
    }
    /* notifikasi bawaan AHA memakai d-none d-md-flex: paksa tampil di mobile */
    .mobile-header .d-none.d-md-flex{ display:flex !important; }
    .mobile-header .nav-item.dropdown{ margin-right:0 !important; }
    .mobile-header .notification-dropdown{ min-width:min(350px, 92vw) !important; }
    .mobile-header .avatar{
        width:36px; height:36px; line-height:34px;
        border-radius:8px; border:1px solid #e6e7e9;
    }

    /* Konten: turun di bawah header mobile, tanpa margin sidebar */
    .page-wrapper, .sidebar-collapsed .page-wrapper{
        margin-left:0 !important; margin-right:0 !important;
        padding:72px 12px 12px !important;
    }

    /* Menu di dalam sidebar mobile: tampil penuh, submenu jadi accordion */
    .submenu{ position:static !important; width:100% !important; background:transparent !important;
              box-shadow:none !important; padding:0 !important; display:none; }
    .submenu.show{ display:block; }
    .nav-link-title{ display:inline !important; }
    .submenu-arrow{ display:block !important; }
    .logo-expand{ display:block !important; }
    .logo-collapse{ display:none !important; }
}
</style>