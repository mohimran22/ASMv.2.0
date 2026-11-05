@extends('tablar::page')

@section('content')
@php
    // [value => label]
    $accountTypes = [
        'AKTIVA'     => 'AKTIVA',
        'KEWAJIBAN'  => 'KEWAJIBAN',
        'EKUITAS'    => 'EKUITAS',
        'PENDAPATAN' => 'PENDAPATAN',
        'Beban'      => 'BEBAN',
    ];

    $balanceTypes = [
        'Aset Lancar - Kas & Bank',
        'Aset Lancar - Persediaan Barang',
        'Aset Lancar - Piutang',
        'Aset Lancar - Dana Belum Disetor',
        'Aset Lancar - Pajak Bayar Dimuka',
        'Aset Tetap',
        'Penyusutan',
        'Hutang',
        'Uang Muka Penjualan',
        'Pajak',
        'Modal',
        'Pendapatan Lisensi',
        'Pendapatan Modul',
        'Pendapatan Siswa',
        'Pendapatan Merchandise',
        'Pendapatan Lainnya',
        'Biaya Lisensi',
        'Biaya Pembelian Modul',
        'Biaya Pembelian Merchandise',
        'Biaya Produksi Modul',
        'Biaya Produksi Merchandise',
        'Beban Penjualan & Pemasaran',
        'Beban Administrasi & Umum',
    ];

    $backUrl = Route::has('accounting.index') ? route('accounting.index') : url()->previous();
@endphp

<style>
    .accounting-form .section-icon {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        background: rgba(32, 107, 196, .1);
        color: #206bc4;
        flex-shrink: 0;
    }
    .accounting-form .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    @media (max-width: 575.98px) {
        .accounting-form .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-3">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col">
                <div class="d-flex align-items-center">
                    <h2>Tambah Akun Akuntansi</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="d-flex">
                    <div class="me-2"><i class="ti ti-alert-circle fs-2"></i></div>
                    <div>
                        <h4 class="alert-title">Terdapat kesalahan pada isian form</h4>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('accounting.store') }}" method="POST" class="accounting-form">
            @csrf

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-report-money"></i></span>
                        <div>
                            <h3 class="card-title mb-0">Data Akun</h3>
                            <div class="text-muted small">Akun dalam bagan akun (chart of accounts)</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            @include('components.select-license', [
                                'licenses' => $licenses,
                                'selectedLicenseId' => old('license_id', $yourModel->license_id ?? null)
                            ])
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="account_type">Kategori</label>
                            <select name="account_type" id="account_type" class="form-select select2 @error('account_type') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($accountTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('account_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('account_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="balance_type">Sub Kategori</label>
                            <select name="balance_type" id="balance_type" class="form-select select2 @error('balance_type') is-invalid @enderror" required>
                                <option value="">-- Pilih Sub Kategori --</option>
                                @foreach($balanceTypes as $type)
                                    <option value="{{ $type }}" @selected(old('balance_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('balance_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="account_code">Kode Akun</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                <input type="text" id="account_code" name="account_code" class="form-control @error('account_code') is-invalid @enderror" value="{{ old('account_code') }}" required>
                            </div>
                            @error('account_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="account_name">Nama Akun</label>
                            <div class="input-icon">
                                <span class="input-icon-addon"><i class="ti ti-file-description"></i></span>
                                <input type="text" id="account_name" name="account_name" class="form-control @error('account_name') is-invalid @enderror" value="{{ old('account_name') }}" required>
                            </div>
                            @error('account_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="initial_balance">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="initial_balance" name="initial_balance" class="form-control @error('initial_balance') is-invalid @enderror" value="{{ old('initial_balance') }}" step="0.01" inputmode="decimal">
                            </div>
                            @error('initial_balance')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                                                            {{-- <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label>Apakah Akun Induk?</label>
                                            <input type="checkbox" name="is_parent" value="1">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>Akun Induk</label>
                                            <select name="parent_id" class="form-select">
                                                <option value="">-- Tidak Ada --</option>
                                                @foreach ($parentAccounts as $parent)
                                                    <option value="{{ $parent->id }}">{{ $parent->account_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
});
</script>
@endpush