@extends('tablar::page')

@section('content')
@php
    $totalDebit  = $journal->details->sum('debit');
    $totalCredit = $journal->details->sum('credit');

    $enclosureUrl = $journal->enclosure ? asset('storage/' . $journal->enclosure) : null;
    $enclosureExt = $journal->enclosure ? strtolower(pathinfo($journal->enclosure, PATHINFO_EXTENSION)) : null;
@endphp

<style>
    .journal-show .section-icon {
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
    .journal-show .table-responsive { max-width: 100%; }
    .journal-show .amount { white-space: nowrap; }
    /* Mobile: beri ruang tiap kolom, tabel digeser horizontal di dalam kartu */
    @media (max-width: 767.98px) {
        .journal-show .journal-detail-table { min-width: 960px; }
        .journal-show .journal-detail-table th:nth-child(1) { min-width: 90px; }
        .journal-show .journal-detail-table th:nth-child(2) { min-width: 210px; }
        .journal-show .journal-detail-table th:nth-child(3) { min-width: 230px; }
        .journal-show .journal-detail-table th:nth-child(4) { min-width: 150px; }
        .journal-show .journal-detail-table th:nth-child(5),
        .journal-show .journal-detail-table th:nth-child(6) { min-width: 140px; }
    }
    @media (max-width: 575.98px) {
        .journal-show .card-body { padding: 1rem; }
        .page-header .page-title { font-size: 1.15rem; }
    }
</style>

<!-- Page header -->
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center g-2">
            <div class="col d-flex align-items-center">
                <a href="{{ route('journals.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                <h2 class="page-title mb-0">Detail Jurnal</h2>
            </div>

            <div class="col-12 col-md-auto ms-auto d-print-none">
                <div class="d-grid gap-2 d-sm-flex">
                    <a href="{{ route('journals.export', $journal->id) }}" class="btn btn-success">
                        <i class="ti ti-file-export me-1"></i> Ekspor
                    </a>
                    <a href="{{ route('journals.print', $journal->id) }}" target="_blank" class="btn btn-danger">
                        <i class="ti ti-printer me-1"></i> Cetak
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl journal-show">

        {{-- Informasi jurnal --}}
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="section-icon"><i class="ti ti-notebook"></i></span>
                    <h3 class="card-title mb-0">Informasi Jurnal</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    <div class="datagrid-item">
                        <div class="datagrid-title">No. Transaksi</div>
                        <div class="datagrid-content">{{ $journal->journal_code ?: '-' }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Transaksi</div>
                        <div class="datagrid-content">{{ \Carbon\Carbon::parse($journal->transaction_date)->format('d/m/Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Lisensi</div>
                        <div class="datagrid-content">{{ $journal->license->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail akun --}}
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="section-icon"><i class="ti ti-list-details"></i></span>
                    <h3 class="card-title mb-0">Detail Akun</h3>
                </div>
            </div>
            <div class="d-md-none text-muted small px-3 pt-2">Geser ke samping untuk melihat semua kolom →</div>
            <div class="table-responsive">
                <table class="table table-bordered table-vcenter card-table mb-0 journal-detail-table">
                    <thead>
                        <tr>
                            <th>No. Akun</th>
                            <th>Nama Akun</th>
                            <th>Deskripsi</th>
                            <th>User</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->details as $detail)
                            <tr>
                                <td>{{ $detail->account->account_code ?? '-' }}</td>
                                <td>{{ $detail->account->account_name ?? '-' }}</td>
                                <td>{{ $detail->description ?: '-' }}</td>
                                <td>{{ $detail->person_name ?: '-' }}</td>
                                <td class="text-end amount">Rp. {{ number_format($detail->debit, 2, ',', '.') }}</td>
                                <td class="text-end amount">Rp. {{ number_format($detail->credit, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="4">Total</td>
                            <td class="text-end amount">Rp. {{ number_format($totalDebit, 2, ',', '.') }}</td>
                            <td class="text-end amount">Rp. {{ number_format($totalCredit, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Keterangan & lampiran --}}
        <div class="row g-3 mb-3">
            <div class="col-12 {{ $enclosureUrl ? 'col-lg-6' : '' }}">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon"><i class="ti ti-notes"></i></span>
                            <h3 class="card-title mb-0">Keterangan</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="white-space: pre-line;">{{ $journal->description ?: '-' }}</div>
                    </div>
                </div>
            </div>

            @if ($enclosureUrl)
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="section-icon"><i class="ti ti-paperclip"></i></span>
                                <h3 class="card-title mb-0">Lampiran Jurnal</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(in_array($enclosureExt, ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ $enclosureUrl }}" alt="Lampiran" class="img-fluid rounded shadow-sm">
                            @elseif($enclosureExt === 'pdf')
                                <embed src="{{ $enclosureUrl }}" type="application/pdf" width="100%" height="500px" class="border rounded">
                                <div class="mt-2">
                                    <a href="{{ $enclosureUrl }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="ti ti-external-link me-1"></i> Buka PDF
                                    </a>
                                </div>
                            @else
                                <a href="{{ $enclosureUrl }}" target="_blank" class="btn btn-sm btn-secondary">
                                    <i class="ti ti-download me-1"></i> Download Lampiran
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Aksi & info perubahan --}}
        <div class="card">
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="text-muted small">
                        Terakhir diubah oleh <strong>{{ $journal->creator->name ?? 'Sistem' }}</strong>
                        pada {{ \Carbon\Carbon::parse($journal->updated_at)->translatedFormat('d F Y H:i') }} GMT+7
                    </div>
                    <div class="d-grid d-sm-block">
                        <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i> Ubah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection