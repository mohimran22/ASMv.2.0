@extends('tablar::page')

@section('content')
@php
    // Susun baris transaksi sekali, dipakai oleh tabel (desktop) dan kartu (mobile)
    $rows = [];
    $no = 1;
    $saldo = 0;
    $totalDebit = 0;
    $totalKredit = 0;

    foreach ($journals as $journal) {
        foreach ($journal->details as $detail) {
            $debit  = (float) $detail->debit;
            $kredit = (float) $detail->credit;
            $saldo += $debit - $kredit;
            $totalDebit += $debit;
            $totalKredit += $kredit;

            $rows[] = (object) [
                'no'          => $no++,
                'date'        => \Carbon\Carbon::parse($journal->transaction_date),
                'description' => $detail->description,
                'code'        => $detail->account->account_code ?? '-',
                'account'     => $detail->account->account_name ?? '-',
                'person'      => $detail->person_name,
                'pic'         => $journal->creator->name ?? null,
                'debit'       => $debit,
                'kredit'      => $kredit,
                'saldo'       => $saldo,
                'note'        => $journal->description ?? null,
            ];
        }
    }

    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 2, ',', '.');
    $canFilterLicense = auth()->user()->hasRole('Super-Admin')
        || auth()->user()->hasRole('Pemilik Lisensi')
        || auth()->user()->hasRole('Akuntan');
@endphp

<style>
    .txn-page .section-icon {
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
    .txn-page .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .txn-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .txn-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    /* Tabel (desktop) */
    .txn-page #kasTable thead th {
        white-space: nowrap;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: center;
    }
    .txn-page #kasTable .amount {
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .txn-page .dataTables_wrapper .dataTables_length,
    .txn-page .dt-container .dt-length { padding: .75rem 1rem 0; }
    .txn-page .dataTables_wrapper .dataTables_info,
    .txn-page .dataTables_wrapper .dataTables_paginate,
    .txn-page .dt-container .dt-info,
    .txn-page .dt-container .dt-paging { padding: .75rem 1rem; }

    /* Kartu (mobile) */
    .txn-page .amount-chip {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .txn-page .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<div class="txn-page">

    <!-- Page header -->
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-arrows-exchange"></i></span>
                        <div>
                            <h2 class="page-title mb-0">Transaksi</h2>
                            <div class="text-muted small">Riwayat pemasukan dan pengeluaran</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <a href="{{ route('kas.export.excel', request()->all()) }}" class="btn btn-success w-100">
                        <i class="ti ti-file-export me-1"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">

            {{-- Filter --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('journals.report') }}" method="GET" class="row g-3 align-items-end">
                        @if ($canFilterLicense)
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="license_id" class="form-label">Lisensi</label>
                                <select name="license_id" id="license_id" class="form-select select2">
                                    <option value="">-- Semua Lisensi --</option>
                                    @foreach ($licenses as $license)
                                        <option value="{{ $license->id }}" {{ request('license_id') == $license->id ? 'selected' : '' }}>
                                            {{ $license->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="account_id" class="form-label">Akun</label>
                            <select name="account_id" id="account_id" class="form-select select2">
                                <option value="">-- Semua Akun --</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                        [{{ $account->account_code }}] {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                            <label for="start_date" class="form-label">Dari Tanggal</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                            <label for="end_date" class="form-label">Sampai Tanggal</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                        </div>

                        <div class="col-12 col-lg-{{ $canFilterLicense ? '2' : '5' }}">
                            <div class="d-grid gap-2 d-sm-flex">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('journals.report') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Total Debit (Pemasukan)</div>
                            <div class="h2 mb-0 text-success text-break">{{ $rp($totalDebit) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Total Kredit (Pengeluaran)</div>
                            <div class="h2 mb-0 text-danger text-break">{{ $rp($totalKredit) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Saldo</div>
                            <div class="h2 mb-0 text-break {{ $saldo < 0 ? 'text-danger' : '' }}">{{ $rp($saldo) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pencarian --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari transaksi (deskripsi, akun, user, PIC)..." autocomplete="off">
                    </div>
                </div>
            </div>

            @if (count($rows))

                {{-- Tabel (tablet & desktop) --}}
                <div class="card d-none d-md-block">
                    <div class="table-responsive">
                        <table id="kasTable" class="table table-vcenter card-table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th class="desc-column">Deskripsi</th>
                                    <th class="code-column">Kode Akun</th>
                                    <th class="account-column">Akun</th>
                                    <th class="user-column">User</th>
                                    <th class="user-column">PIC</th>
                                    <th>Debit (pemasukan)</th>
                                    <th>Kredit (pengeluaran)</th>
                                    <th>Saldo</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="text-center">{{ $row->no }}</td>
                                        <td class="text-nowrap" data-order="{{ $row->date->format('Y-m-d') }}">{{ $row->date->format('d/m/Y') }}</td>
                                        <td class="desc-column">{{ $row->description ?: '-' }}</td>
                                        <td class="text-center">{{ $row->code }}</td>
                                        <td class="account-column">{{ $row->account }}</td>
                                        <td class="user-column">{{ $row->person ?: '-' }}</td>
                                        <td>
                                            @if($row->pic)
                                                {{ $row->pic }}
                                            @else
                                                <small class="fst-italic text-muted">dibuat oleh sistem</small>
                                            @endif
                                        </td>
                                        <td class="amount {{ $row->debit > 0 ? 'text-success' : 'text-muted' }}" data-order="{{ $row->debit }}">{{ $rp($row->debit) }}</td>
                                        <td class="amount {{ $row->kredit > 0 ? 'text-danger' : 'text-muted' }}" data-order="{{ $row->kredit }}">{{ $rp($row->kredit) }}</td>
                                        <td class="amount fw-medium {{ $row->saldo < 0 ? 'text-danger' : '' }}" data-order="{{ $row->saldo }}">{{ $rp($row->saldo) }}</td>
                                        <td>{{ $row->note ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="7" class="text-end">TOTAL</td>
                                    <td class="amount">{{ $rp($totalDebit) }}</td>
                                    <td class="amount">{{ $rp($totalKredit) }}</td>
                                    <td class="amount">{{ $rp($saldo) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Kartu transaksi (mobile) --}}
                <div class="d-md-none" id="txn-cards">
                    @foreach ($rows as $row)
                        <div class="card mb-2 txn-card"
                             data-search="{{ strtolower($row->description . ' ' . $row->code . ' ' . $row->account . ' ' . $row->person . ' ' . ($row->pic ?? 'sistem') . ' ' . $row->note) }}">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-break">{{ $row->description ?: '-' }}</div>
                                        <div class="text-muted small text-break">{{ $row->code }} &middot; {{ $row->account }}</div>
                                    </div>
                                    <span class="text-muted small text-nowrap">{{ $row->date->format('d/m/Y') }}</span>
                                </div>

                                <div class="text-muted small mb-2">
                                    <i class="ti ti-user me-1"></i>{{ $row->person ?: '-' }}
                                    <span class="mx-1">&middot;</span>
                                    PIC: {{ $row->pic ?: 'sistem' }}
                                </div>

                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="text-muted small">Debit</div>
                                        <div class="amount-chip small {{ $row->debit > 0 ? 'text-success fw-bold' : 'text-muted' }}">{{ $rp($row->debit) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Kredit</div>
                                        <div class="amount-chip small {{ $row->kredit > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $rp($row->kredit) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted small">Saldo</div>
                                        <div class="amount-chip small fw-bold {{ $row->saldo < 0 ? 'text-danger' : '' }}">{{ $rp($row->saldo) }}</div>
                                    </div>
                                </div>

                                @if($row->note)
                                    <div class="text-muted small mt-2 text-break"><i class="ti ti-note me-1"></i>{{ $row->note }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="text-center text-muted py-4 d-none" id="txn-empty">Data tidak ditemukan.</div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary d-none" id="txn-more">
                            Tampilkan lebih banyak
                        </button>
                    </div>
                </div>

            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ti ti-receipt-off fs-1 d-block mb-2"></i>
                        Tidak ada transaksi pada filter ini.
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    // ===== Akun mengikuti lisensi yang dipilih =====
    const selectedAccount = "{{ request('account_id') }}";

    function loadAccounts(licenseId, preselect) {
        const $account = $('#account_id');
        $account.html('<option value="">Memuat...</option>').prop('disabled', true).trigger('change.select2');

        if (!licenseId) {
            $account.html('<option value="">-- Semua Akun --</option>').prop('disabled', false).trigger('change.select2');
            return;
        }

        $.get('/get-accounts-by-license/' + licenseId, function (data) {
            let options = '<option value="">-- Semua Akun --</option>';
            data.forEach(function (account) {
                options += '<option value="' + account.id + '">[' + account.account_code + '] ' + account.account_name + '</option>';
            });
            $account.html(options).prop('disabled', false);
            // Pertahankan akun yang sedang difilter saat halaman dimuat
            if (preselect) $account.val(preselect);
            $account.trigger('change.select2');
        }).fail(function () {
            $account.html('<option value="">-- Semua Akun --</option>').prop('disabled', false).trigger('change.select2');
        });
    }

    $('#license_id').on('change', function () {
        loadAccounts($(this).val(), null);
    });

    // Saat halaman dimuat, sinkronkan daftar akun dengan lisensi terpilih
    const initialLicenseId = $('#license_id').val();
    if (initialLicenseId) {
        loadAccounts(initialLicenseId, selectedAccount);
    }

    // ===== DataTable (tablet & desktop) =====
    let table = null;
    if ($('#kasTable').length) {
        table = $('#kasTable').DataTable({
            pageLength: 25,
            ordering: true,
            dom: 'lrtip', // kotak cari bawaan disembunyikan, pakai kotak cari di atas
            language: {
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
            }
        });
        $(window).on('resize', function () { table.columns.adjust(); });
    }

    // ===== Kartu transaksi (mobile): cari + tampilkan bertahap =====
    const PAGE_SIZE = 20;
    let visibleLimit = PAGE_SIZE;
    const $cards = $('.txn-card');
    const $more = $('#txn-more');
    const $empty = $('#txn-empty');

    function renderCards() {
        const q = ($('#searchInput').val() || '').toLowerCase().trim();
        let matched = 0;
        let shown = 0;

        $cards.each(function () {
            const isMatch = !q || String($(this).data('search')).indexOf(q) !== -1;
            if (isMatch) matched++;
            const show = isMatch && matched <= visibleLimit;
            $(this).toggleClass('d-none', !show);
            if (show) shown++;
        });

        $more.toggleClass('d-none', matched <= visibleLimit);
        $empty.toggleClass('d-none', matched !== 0);
    }

    $more.on('click', function () {
        visibleLimit += PAGE_SIZE;
        renderCards();
    });

    if ($cards.length) renderCards();

    // ===== Kotak cari (berlaku untuk tabel dan kartu) =====
    $('#searchInput').on('input', function () {
        if (table) table.search(this.value).draw();
        visibleLimit = PAGE_SIZE;
        if ($cards.length) renderCards();
    });
});
</script>
@endpush