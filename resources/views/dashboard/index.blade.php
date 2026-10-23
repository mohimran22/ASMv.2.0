@extends('tablar::page')

@section('content')
<div class="container-xl">
    {{-- Header --}}
    <div class="bg-primary text-white p-4 rounded mb-4">
        @php
            $userName = auth()->user()->name;
        @endphp
        <h2 class="mb-1">
            Selamat Datang di ASM 2.0,
            {{ is_string($userName) ? $userName : 'Admin Utama' }}
        </h2>
        
        <p class="mb-0">Kini jadi lebih mudah dalam mengelola : <strong>{{ is_string($licenseName) ? $licenseName : '-' }}</strong></p>

        <small class="d-block mt-2">
            Terakhir login: {{ now()->translatedFormat('d F Y H:i') }}
            {{-- @if (auth()->check() && auth()->user()->last_login_at)
                {{ auth()->user()->last_login_at->locale('id')->translatedFormat('l, d F Y H:i') }}
            @else
                Belum pernah login
            @endif --}}
        </small>

    </div>


    {{-- Tampilkan hanya untuk Super Admin dan Pemilik Lisensi --}}
    @if(auth()->user()->hasRole('Super-Admin') || auth()->user()->hasRole('Pemilik Lisensi'))

    <div class="row mb-4">
    {{-- Total Lisensi --}}
    <div class="col-md-3 mb-3">
        <a href="{{ route('licenses.index') }}" class="text-decoration-none">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Lisensi</h6>
                    <h3 class="card-text">{{ is_numeric($licensesCount) ? number_format($licensesCount) : '-' }}</h3>
                </div>
            </div>
        </a>
    </div>

    {{-- Total Siswa --}}
    <div class="col-md-3 mb-3">
        <a href="{{ route('students.index') }}" class="text-decoration-none">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Siswa</h6>
                    <h3 class="card-text">{{ is_numeric($studentsCount) ? number_format($studentsCount) : '-' }}</h3>
                </div>
            </div>
        </a>
    </div>

    {{-- Total Karyawan --}}
    <div class="col-md-3 mb-3">
        <a href="{{ route('employees.index') }}" class="text-decoration-none">
            <div class="card text-center shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Karyawan</h6>
                    <h3 class="card-text">{{ is_numeric($employeesCount) ? number_format($employeesCount) : '-' }}</h3>
                </div>
            </div>
        </a>
    </div>

    {{-- Pendapatan --}}
    <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title">Pendapatan Bulan Ini</h6>
                <h3 class="card-text">Rp {{ number_format($monthlyRevenue, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>  

        {{-- Aksi Cepat --}}
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('licenses.create') }}" class="btn btn-primary text-white w-100">
                    <i class="ti ti-building-plus"></i> Tambah Lisensi
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('students.create') }}" class="btn btn-success text-white w-100">
                    <i class="ti ti-person-plus"></i> Daftarkan Siswa
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('employees.create') }}" class="btn btn-secondary text-white w-100">
                    <i class="ti ti-user-plus"></i> Tambah Karyawan
                </a>
            </div>
            @can('akun-akuntansi.lihat')
                <div class="col-md-3">
                    <a href="{{ route('accounting.index') }}" class="btn btn-danger text-white w-100">
                        <i class="ti ti-people-"></i> Akuntasi
                    </a>
                </div>
            @endcan
        </div>

        {{-- Top 3 Siswa Baru --}}
        <div class="card shadow-sm mb-4 mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ti ti-trophy-fill"></i> Top 3 Cabang (Siswa Baru)</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Lisensi</th>
                            <th>Siswa Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topLicenseByStudents as $branch)
                            <tr>
                                <td>
                                    <strong>{{ $branch->name }}</strong><br>
                                    <small class="text-muted">Pemilik: {{ $branch->owner_name }}</small>
                                </td>
                                <td>{{ $branch->students }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top 3 Pendapatan --}}
        <div class="card shadow-sm mb-4 mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="ti ti-cash-stack"></i> Top 3 Cabang (Pendapatan)</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Lisensi</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topLicenseByRevenue as $branch)
                            <tr>
                                <td>
                                    <strong>{{ $branch->name }}</strong><br>
                                    <small class="text-muted">Pemilik: {{ $branch->owner_name }}</small>
                                </td>
                                <td>Rp {{ number_format($branch->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif
    @if($notifications->count())
        <div class="alert alert-warning notification-item">
            <h5>📢 Notifikasi Lisensi Akan Expired</h5>
            <ul>
                @foreach($notifications as $note)
                    <li>{{ $note->message }} ({{ $note->created_at->diffForHumans() }})</li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
@endsection



        
