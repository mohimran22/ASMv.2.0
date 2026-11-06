@extends('tablar::page')

@section('content')
@php
    $isBalanced = round((float) $totalDebit, 2) === round((float) $totalCredit, 2);
    $difference = abs((float) $totalDebit - (float) $totalCredit);

    $exportParams = [
        'start_date' => request('start_date'),
        'end_date'   => request('end_date'),
        'license_id' => request('license_id'),
    ];

    // Tampilkan "-" jika nilai nol agar tabel lebih mudah dibaca
    $money = function ($value) {
        return ((float) $value) == 0 ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
    };
@endphp

<style>
    .journal-page .section-icon {
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
    .journal-page .form-label.required::after {
        content: " *";
        color: #d63939;
    }
    .journal-page .select2-container--default .select2-selection--single {
        height: calc(1.4375rem + 1.125rem + 2px);
        border: 1px solid var(--tblr-border-color, #dadfe5);
        border-radius: var(--tblr-border-radius, 4px);
        display: flex;
        align-items: center;
    }
    .journal-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.4;
        padding-left: .75rem;
    }
    .journal-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    /* Tabel jurnal (desktop) */
    .journal-page .journal-table thead th {
        white-space: nowrap;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .journal-page .journal-table tbody tr.journal-first td {
        border-top: 2px solid var(--tblr-border-color, #dadfe5);
    }
    .journal-page .journal-table td.merged {
        background: var(--tblr-bg-surface-secondary, #f6f8fb);
        vertical-align: top;
    }
    .journal-page .journal-table .amount {
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    /* Kartu jurnal (mobile) */
    .journal-page .journal-card-line + .journal-card-line {
        border-top: 1px dashed var(--tblr-border-color, #dadfe5);
    }
    .journal-page .amount-chip {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .journal-page .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<div class="journal-page">

    <!-- Page header -->
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-icon"><i class="ti ti-notebook"></i></span>
                        <div>
                            <h2 class="mb-0">Jurnal Umum</h2>
                            <div class="text-muted small">Daftar transaksi jurnal perusahaan</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="d-grid gap-2 d-sm-flex">
                        <a href="{{ route('general.export', request()->all()) }}" class="btn btn-success" target="_blank">
                            <i class="ti ti-file-export me-1"></i> Ekspor Excel
                        </a>
                        <a href="{{ route('journals.export.pdf', $exportParams) }}" class="btn btn-danger" target="_blank">
                            <i class="ti ti-printer me-1"></i> Cetak
                        </a>
                    </div>
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
                    <form method="GET" action="{{ route('journals.general') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label" for="start_date">Dari</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label" for="end_date">Sampai</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control">
                        </div>

                        @if(auth()->user()->hasRole('Super-Admin'))
                            <div class="col-12 col-lg-4">
                                <label for="license_id" class="form-label required">Filter Lisensi</label>
                                <select name="license_id" id="license_id" class="form-select select2" required>
                                    <option value="">-- Pilih Lisensi --</option>
                                    @foreach ($licenses as $license)
                                        <option value="{{ $license->id }}" {{ $activeLicenseId == $license->id ? 'selected' : '' }}>
                                            {{ $license->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- Selain Super Admin, lisensi aktif dikirim lewat hidden input --}}
                            <input type="hidden" name="license_id" value="{{ $activeLicenseId }}">
                        @endif

                        <div class="col-12 col-lg-2">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-filter me-1"></i> Filter
                                </button>
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
                            <div class="text-muted small text-uppercase">Total Debit</div>
                            <div class="h2 mb-0 text-break">Rp {{ number_format($totalDebit, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Total Kredit</div>
                            <div class="h2 mb-0 text-break">Rp {{ number_format($totalCredit, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase">Status Keseimbangan</div>
                            @if ($isBalanced)
                                <div class="h2 mb-0 text-success">
                                    <i class="ti ti-circle-check me-1"></i> Seimbang
                                </div>
                            @else
                                <div class="h2 mb-0 text-danger">
                                    <i class="ti ti-alert-triangle me-1"></i> Tidak seimbang
                                </div>
                                <div class="text-muted small">Selisih Rp {{ number_format($difference, 2, ',', '.') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($journals->count())

                {{-- Tabel (tablet & desktop) --}}
                <div class="card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table journal-table">
                            <thead>
                                <tr>
                                    <th class="tanggal-column">Tanggal</th>
                                    <th class="kode-column">No Jurnal</th>
                                    <th>Deskripsi</th>
                                    <th>No. Akun</th>
                                    <th>Nama Akun</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($journals as $journal)
                                    @php $rowCount = $journal->details->count(); @endphp
                                    @foreach ($journal->details as $i => $detail)
                                        <tr class="{{ $i == 0 ? 'journal-first' : '' }}">
                                            @if($i == 0)
                                                {{-- Gabung kolom tanggal & kode jurnal --}}
                                                <td rowspan="{{ $rowCount }}" class="merged text-nowrap">
                                                    {{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}
                                                </td>
                                                <td rowspan="{{ $rowCount }}" class="merged">
                                                    <a href="{{ route('journals.show', $journal->id) }}" class="text-decoration-none fw-bold text-primary">
                                                        {{ $journal->journal_code }}
                                                    </a>
                                                </td>
                                            @endif
                                            <td>{{ $detail->description ?: '-' }}</td>
                                            <td class="text-nowrap">{{ $detail->account->account_code ?? '-' }}</td>
                                            <td>{{ $detail->account->account_name ?? '-' }}</td>
                                            <td class="amount">{{ $money($detail->debit) }}</td>
                                            <td class="amount">{{ $money($detail->credit) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="5">Total</td>
                                    <td class="amount">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                                    <td class="amount">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Kartu per jurnal (mobile) --}}
                <div class="d-md-none">
                    @foreach ($journals as $journal)
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between w-100 gap-2">
                                    <a href="{{ route('journals.show', $journal->id) }}" class="fw-bold text-primary text-decoration-none text-break">
                                        {{ $journal->journal_code }}
                                    </a>
                                    <span class="text-muted small text-nowrap">
                                        <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body py-2">
                                @foreach ($journal->details as $detail)
                                    <div class="journal-card-line py-2">
                                        <div class="fw-medium text-break">
                                            {{ $detail->account->account_code ?? '-' }} &middot; {{ $detail->account->account_name ?? '-' }}
                                        </div>
                                        @if($detail->description)
                                            <div class="text-muted small text-break">{{ $detail->description }}</div>
                                        @endif
                                        <div class="d-flex justify-content-between mt-1 small">
                                            <span>
                                                <span class="text-muted">Debit</span>
                                                <span class="amount-chip">{{ $money($detail->debit) }}</span>
                                            </span>
                                            <span>
                                                <span class="text-muted">Kredit</span>
                                                <span class="amount-chip">{{ $money($detail->credit) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-between small fw-bold">
                                    <span>Debit: Rp {{ number_format($journal->details->sum('debit'), 0, ',', '.') }}</span>
                                    <span>Kredit: Rp {{ number_format($journal->details->sum('credit'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ti ti-notebook-off fs-1 d-block mb-2"></i>
                        Tidak ada transaksi jurnal pada periode ini.
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
});
</script>
@endpush