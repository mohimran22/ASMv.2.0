<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountingJournalRequest;
use App\Http\Requests\UpdateAccountingJournalRequest;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalDetail;
use App\Models\AccountingAccount;
use App\Models\AccountingPeriod;
use App\Models\License;
use App\Models\Student;
use App\Models\Employee;
use App\Models\LicenseHolder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\BalanceSheetService;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;


class AccountingJournalController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    if ($request->ajax()) {
        $journals = AccountingJournal::query()
            ->select(
                'accounting_journals.id',
                'accounting_journals.journal_code',
                'accounting_journals.transaction_date',
                'licenses.license_type as license_type',
                'licenses.name as license_name',
                'users.name as creator_name'
            )
            ->leftJoin('licenses', 'accounting_journals.license_id', '=', 'licenses.id')
            ->leftJoin('users', 'accounting_journals.created_by', '=', 'users.id')
            ->when(!$user->hasRole('Super-Admin'), function ($query) use ($user) {
                // Filter lisensi untuk Pemilik Lisensi & Akuntan
                $licenses = $user->hasRole('Pemilik Lisensi')
                    ? $user->licenses
                    : $user->employee?->licenses;

                if ($licenses && $licenses->isNotEmpty()) {
                    $query->whereIn('accounting_journals.license_id', $licenses->pluck('id'));
                } else {
                    $query->whereNull('accounting_journals.license_id'); // Biar aman
                }
            })
            ->when(session()->has('active_license_id'), function ($query) {
                $query->where('accounting_journals.license_id', session('active_license_id'));
            })
            ->orderByDesc('accounting_journals.transaction_date');

        return DataTables::of($journals)
            ->addIndexColumn()
            ->editColumn('transaction_date', function ($row) {
                return Carbon::parse($row->transaction_date)->format('d/m/Y');
            })
            ->addColumn('journal_code', function ($row) {
                return '<a href="' . route('journals.show', $row->id) . '" 
                        class="fw-bold text-primary">'
                        . $row->journal_code . '</a>';
            })
            ->addColumn('creator', function ($row) {
                return $row->creator_name 
                    ? e($row->creator_name)
                    : '<small class="fst-italic text-muted">dibuat oleh sistem</small>';
            })

            ->addColumn('action', function ($journal) {
                    $buttons = '';
                    if (auth()->user()->can('jurnal.ubah')) {
                        $buttons .= '<a href="' . route('journals.edit', $journal->id) . '" class="btn btn-icon btn-sm btn-warning me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('jurnal.lihat')) {
                        $buttons .= '<a href="' . route('journals.show', $journal->id) . '" class="btn btn-icon btn-sm btn-info me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('jurnal.hapus')) {
                        $buttons .= '<button data-id="' . $journal->id . '" class="btn btn-icon btn-sm btn-danger delete-journal" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
            })
            ->rawColumns(['journal_code', 'creator', 'action'])
            ->make(true);
    }

    return view('journals.index');
}

    public function create(Request $request)
{
    $user = Auth::user();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } else {
        $licenses = $user->hasRole('Pemilik Lisensi')
            ? $user->licenses
            : $user->employee?->licenses;

        if (!$licenses || $licenses->isEmpty()) {
            abort(403, 'License tidak ditemukan untuk user ini.');
        }
    }

    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    $openPeriod = DB::table('accounting_periods')
        ->where('license_id', $activeLicenseId)
        ->where('is_closed', false)
        ->orderByDesc('year')
        ->first();
    $licenseIds = $activeLicenseId
        ? [(string) $activeLicenseId]
        : $licenses->pluck('id')->toArray();

    // Ambil akun yang disembunyikan sesuai role user
    $hiddenAccounts = [];
    foreach ($user->getRoleNames() as $role) {
        if (isset(config('accounting.hidden_accounts')[$role])) {
            $hiddenAccounts = array_merge(
                $hiddenAccounts,
                config('accounting.hidden_accounts')[$role]
            );
        }
    }

    // Accounts
    $accounts = AccountingAccount::where('is_parent', false)
        ->where('is_active', true)
        ->whereIn('license_id', $licenseIds)
        ->orderBy('account_code')
        ->get();

    // Students
    $students = Student::whereIn('license_id', $licenseIds)
        ->select('id', 'fullname as name')
        ->get();

    // Employees
    $employees = Employee::whereHas('licenses', function ($q) use ($licenseIds) {
            $q->whereIn('employee_license.license_id', $licenseIds);
        })
        ->select('id', 'fullname as name')
        ->get();

    $licenseholders = User::whereHas('licenses', function ($q) use ($licenseIds) {
            $q->whereIn('license_user.license_id', $licenseIds);
        })
        
        ->select('id', 'name')
        ->get();

    $licenseList = License::whereIn('id', $licenseIds)
        ->select('id', 'name')
        ->get();

    $pusatLicense = License::where('name', 'AHA Right Brain')->first();

    $pusatUserId   = $pusatLicense?->pusatUser()?->id;
    $pusatUserName = $pusatLicense?->pusatUser()?->name;

    $journalCode = $activeLicenseId
    ? $this->generateNextJournalCode($activeLicenseId)
    : null; 

        return view('journals.create', compact(
            'accounts', 'licenses', 'journalCode', 'activeLicenseId', 'students', 'employees', 'licenseholders', 'licenseList', 'pusatUserId', 'pusatUserName', 'openPeriod'
        ));
}

    private function generateNextJournalCode($licenseId)
{
    $license = License::findOrFail($licenseId);

     $lastJournalNumber = AccountingJournal::where('license_id', $license->id)
        ->where('journal_code', 'LIKE', 'IJ-' . $license->license_id . '-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(journal_code, '^IJ-' || ? || '-', '') AS INTEGER
                )
            ) as last_number
        ", [$license->license_id])
        ->value('last_number');

        do {
            $nextNumber = str_pad($lastJournalNumber + 1, 4, '0', STR_PAD_LEFT);
            $journalCode = 'IJ-' . $license->license_id . '-' . $nextNumber;

            $exists = AccountingJournal::where('journal_code', $journalCode)->exists();
            $lastJournalNumber++;
        } while ($exists);

        return $journalCode;
}

public function getNextCode($licenseId)
{
    $nextCode = $this->generateNextJournalCode($licenseId);

    return response()->json([
        'next_code'  => $nextCode,
        'license_id' => $licenseId,
    ]);
}

public function store(StoreAccountingJournalRequest $request)
{
    $user = Auth::user();
    $licenseId = $request->license_id;
    $transactionDate = Carbon::parse($request->transaction_date);

    $period = DB::table('accounting_periods')
        ->where('license_id', $licenseId)
        ->whereDate('start_date', '<=', $transactionDate)
        ->whereDate('end_date', '>=', $transactionDate)
        ->first();

    if (!$period) {
        return back()
            ->withInput()
            ->with('error', 'Periode akuntansi tidak ditemukan.');
    }

    if ($period->is_closed) {
        return back()
            ->withInput()
            ->with('error', 'Periode akuntansi sudah ditutup. Tidak dapat membuat jurnal.');
    }
    $cabangLicense = License::find($licenseId);

    $totalDebit = collect($request->details)->sum('debit');
    $totalCredit = collect($request->details)->sum('credit');
    if ($totalDebit != $totalCredit) {
        return back()
            ->withInput()
            ->with('error', 'Total debit dan kredit harus seimbang.');
    }
    $enclosurePath = null;
    if ($request->hasFile('enclosure')) {
        $file = $request->file('enclosure');
        $enclosurePath = $file->storeAs(
            'attachments',  // 📌 bisa ganti "photos" → "attachments" biar general (karena bisa pdf/img/doc)
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'public'
        );
    }
    DB::transaction(function () use ($request, $user, $licenseId, $enclosurePath) {
        $journal = AccountingJournal::create([
            'license_id' => $licenseId,
            'journal_code' => $request->journal_code,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
            'created_by' => $user->id,
            'enclosure' => $enclosurePath,
        ]);

        foreach ($request->details as $detail) {
            AccountingJournalDetail::create([
                'journal_id' => $journal->id,
                'account_id' => $detail['account_id'],
                'person' => $detail['person'] ?? null,
                'debit' => $detail['debit'] ?? 0,
                'credit' => $detail['credit'] ?? 0,
                'description' => $detail['description'] ?? null,
            ]);
        }
    });
    return redirect()->route('journals.index')->with('success', 'Jurnal berhasil dibuat.');
}

    public function show(Request $request, AccountingJournal $journal)
    {
        $user = Auth::user();

        if ($user->hasRole('Super-Admin')) {
            $licenses = License::all();
            $activeLicenseId = $request->get('license_id'); // Ambil dari filter form
        } else {
            $licenses = $user->hasRole('Pemilik Lisensi')
                ? $user->licenses
                : $user->employee?->licenses;

            if (!$licenses || $licenses->isEmpty()) {
                abort(403, 'License tidak ditemukan untuk user ini.');
            }

            $activeLicenseId = session('active_license_id');
        }

        $journal->load(['details.account', 'creator']);
        return view('journals.show', compact('journal', 'licenses', 'activeLicenseId'));
    }

    public function edit(AccountingJournal $journal)
 {
    $user = Auth::user();
    $activeLicenseId = null;

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } else {
        $licenses = $user->hasRole('Pemilik Lisensi')
            ? $user->licenses
            : $user->employee?->licenses;

        if (!$licenses || $licenses->isEmpty()) {
            abort(403, 'Lisensi tidak ditemukan.');
        }

        // Cek: jurnal ini milik lisensi user?
        if (!$licenses->pluck('id')->contains($journal->license_id)) {
            abort(403, 'Anda tidak punya akses ke jurnal ini.');
        }

        $activeLicenseId = session('active_license_id');
    } 
    
    $licenseIds = $activeLicenseId
        ? [(string) $activeLicenseId]
        : $licenses->pluck('id')->toArray();

    $accounts = AccountingAccount::where('is_active', true)
        ->when($activeLicenseId, fn($q) => $q->where('license_id', $activeLicenseId))
        ->when(!$user->hasRole('Super-Admin'), fn($q) => $q->whereIn('license_id', $licenseIds))
        ->orderBy('account_code')
        ->get();

    $students = Student::whereIn('license_id', array_merge($licenseIds, [$journal->license_id]))
        ->select('id', 'fullname as name')
        ->orderBy('fullname')
        ->get();
    
    $employees = Employee::whereHas('licenses', function ($q) use ($licenseIds, $journal) {
            $q->whereIn('employee_license.license_id', array_merge($licenseIds, [$journal->license_id]));
        })
        ->select('id', 'fullname as name')
        ->orderBy('fullname')
        ->get();
        
    $licenseholders = User::whereHas('licenses', function ($q) use ($licenseIds, $journal) {
            $q->whereIn('license_user.license_id', array_merge($licenseIds, [$journal->license_id]));
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    $licenseList = License::where(function ($q) use ($licenseIds, $journal) {
            $q->whereIn('id', $licenseIds)
              ->orWhere('id', $journal->license_id);
        })
        ->select('id', 'name')
        ->get();

    $journal->load('details');

    return view('journals.edit', compact('journal', 'activeLicenseId', 'licenses', 'accounts', 'students', 'employees', 'licenseholders', 'licenseList'));
}


    public function update(UpdateAccountingJournalRequest $request, AccountingJournal $journal)
{
      $user = Auth::user();

    if ($user->hasRole('Super-Admin')) {
        // Boleh ganti license_id
        $journal->license_id = $request->license_id;
    } else {
        $licenses = $user->hasRole('Pemilik Lisensi')
            ? $user->licenses
            : $user->employee?->licenses;

        if (!$licenses || $licenses->isEmpty()) {
            abort(403, 'Lisensi tidak ditemukan.');
        }

        // Harus punya lisensi yang cocok dengan jurnal
        if (!$licenses->pluck('id')->contains($journal->license_id)) {
            abort(403, 'Anda tidak punya akses untuk jurnal ini.');
        }

        // Akuntan tidak boleh ganti license_id, hanya data lain
    }

    $enclosurePath = $journal->enclosure; // default: tetap file lama
    if ($request->hasFile('enclosure')) {
        $file = $request->file('enclosure');

        // Hapus file lama kalau ada
        if ($journal->enclosure && Storage::disk('public')->exists($journal->enclosure)) {
            Storage::disk('public')->delete($journal->enclosure);
        }

        // Simpan file baru
        $enclosurePath = $file->storeAs(
            'attachments',
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'public'
        );
    }

    $journal->transaction_date = $request->transaction_date;
    $journal->description = $request->description;
    $journal->enclosure = $enclosurePath;
    $journal->save();

    // Hapus semua detail lama
    $journal->details()->delete();

    // Buat detail baru
    foreach ($request->details as $detail) {
        $journal->details()->create([
            'account_id' => $detail['account_id'],
            'debit' => $detail['debit'] ?? 0,
            'credit' => $detail['credit'] ?? 0,
            'description' => $detail['description'] ?? null,
        ]);
    }

    return redirect()->route('journals.index')->with('success', 'Jurnal berhasil diperbarui.');

}

    public function destroy($id)
    {
        $journal = AccountingJournal::findOrFail($id);
         if ($journal->enclosure && Storage::disk('public')->exists($journal->enclosure)) {
            Storage::disk('public')->delete($journal->enclosure);
        }
        $journal->details()->delete();
        $journal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jurnal berhasil dihapus.'
        ]);
    }

    public function report(Request $request)
{
    $user = Auth::user();
    
    $accountId = $request->input('account_id');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $licenseFilterId = $request->input('license_id');

    
    $licenses = collect();
    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Pemilik Lisensi')) {
        $licenses = $user->licenses ?? collect();
        if ($licenses->isEmpty()) {
            abort(403, 'Lisensi tidak ditemukan.');
        }
    } elseif ($user->hasRole('Akuntan')) {
        $licenses = $user->employee?->licenses ?? collect();
        if ($licenses->isEmpty()) {
            abort(403, 'Lisensi tidak ditemukan.');
        }
    } else {
        abort(403, 'Role tidak diizinkan.');
    }

    // Filter akun
    $accountsQuery = AccountingAccount::where('is_parent', false)->where('is_active', true);
    if ($licenseFilterId) {
        $accountsQuery->where('license_id', $licenseFilterId);
    } else {
        $accountsQuery->whereIn('license_id', $licenses->pluck('id'));
    }
    $accounts = $accountsQuery->orderBy('account_code')->get();

    $journalsQuery = AccountingJournal::query()
        ->with('creator')
        ->with(['details' => function ($q) use ($accountId) {
            // jika ada filter akun, eager-load hanya details untuk account tersebut
            if ($accountId) {
                $q->where('account_id', $accountId)->with('account');
            } else {
                $q->with('account');
            }
        }])
        ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
        ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
        // pastikan jurnal memiliki detail yang sesuai (biar jurnal tanpa akun itu tidak muncul)
        ->when($accountId, fn($q) => $q->whereHas('details', fn($q2) => $q2->where('account_id', $accountId)));


    if ($licenseFilterId) {
        if (
            $user->hasRole('Super-Admin') ||
            ($user->hasRole('Pemilik Lisensi') && $licenses->pluck('id')->contains($licenseFilterId)) ||
            ($user->hasRole('Akuntan') && $licenses->pluck('id')->contains($licenseFilterId))
        ) {
            $journalsQuery->where('license_id', $licenseFilterId);
        } else {
            abort(403, 'Lisensi tidak valid.');
        }
    } else {
        $journalsQuery->whereIn('license_id', $licenses->pluck('id'));
    }

    $journals = $journalsQuery->orderBy('transaction_date')->get();

    return view('journals.report', compact(
        'accounts',
        'journals',
        'licenses',
        'licenseFilterId',
        'accountId',
        'startDate',
        'endDate'
    ));
}

   public function generalJournal(Request $request)
{
    $user = Auth::user();

    // 🔹 Default filter tanggal: bulan berjalan
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // 🔹 Base query
    $journals = AccountingJournal::with(['details.account']);

    // 🔹 Filter role user
    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } else {     
        $licenses = $user->hasRole('Pemilik Lisensi')
            ? $user->licenses
            : $user->employee?->licenses;

        abort_if(!$licenses || $licenses->isEmpty(), 403, 'Lisensi tidak ditemukan.');
    }

    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    if ($activeLicenseId) {
        $journals->where('license_id', $activeLicenseId);
    } else {
        $journals->whereIn('license_id', $licenses->pluck('id'));
    }

    // 🔹 Filter tanggal
    $journals = $journals->whereBetween('transaction_date', [$startDate, $endDate])
        ->orderBy('transaction_date')
        ->get();

    // 🔹 Hitung total debit & kredit
    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($journals as $journal) {
        foreach ($journal->details as $detail) {
            $totalDebit += $detail->debit;
            $totalCredit += $detail->credit;
        }
    }

    return view('journals.general', compact('journals', 'licenses', 'activeLicenseId', 'startDate', 'endDate', 'totalDebit', 'totalCredit'));
}

public function exportPDF(Request $request)
{
    ini_set('memory_limit', '512M');
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate = $request->end_date ?? now()->endOfMonth()->toDateString();
    $activeLicenseId = $request->license_id ?? auth()->user()->license_id;

    $rows = AccountingJournalDetail::query()
        ->join('accounting_journals as j', 'j.id', '=', 'accounting_journal_details.journal_id')
        ->join('accounting_accounts as a', 'a.id', '=', 'accounting_journal_details.account_id')
        ->select([
            'j.transaction_date',
            'j.journal_code',
            'accounting_journal_details.description',
            'a.account_code',
            'a.account_name',
            'accounting_journal_details.debit',
            'accounting_journal_details.credit',
        ])
        ->whereBetween('j.transaction_date', [$startDate, $endDate])
        ->where('j.license_id', $activeLicenseId)
        ->orderBy('j.transaction_date')
        ->get();

    $totalDebit = $rows->sum('debit');
    $totalCredit = $rows->sum('credit');

    $pdf = Pdf::loadView('journals.export-pdf', compact(
        'rows',
        'startDate',
        'endDate',
        'totalDebit',
        'totalCredit'
    ))->setPaper('a4', 'landscape');

    $filename = sprintf(
        'Jurnal Umum %s - %s.pdf',
        Carbon::parse($startDate)->format('d-m-Y'),
        Carbon::parse($endDate)->format('d-m-Y')
    );

    return $pdf->stream($filename);
}

public function ledger(Request $request)
{
    $user = Auth::user();

    // 🔹 Default filter tanggal: bulan berjalan
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } else {
        $licenses = $user->hasRole('Pemilik Lisensi')
            ? $user->licenses
            : $user->employee?->licenses;

        abort_if(!$licenses || $licenses->isEmpty(), 403, 'Lisensi tidak ditemukan.');
    }

    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    [$ledger, $licenses] = $this->getLedgerData($startDate, $endDate, $licenses, $activeLicenseId);

    return view('journals.ledger', compact(
        'ledger', 'licenses', 'activeLicenseId', 'startDate', 'endDate'
    ));
}

private function getLedgerData($startDate, $endDate, $licenses, $licenseId = null)
{
    $query = AccountingJournalDetail::with(['journal', 'account']);
    $user = Auth::user();

    // 🔹 Filter lisensi
    if ($user->hasRole('Super-Admin')) {
        if ($licenseId) {
            $licenses = License::where('id', $licenseId)->get();
        } else {
            $licenses = License::all();
        }
    } else {
        if ($user->hasRole('Pemilik Lisensi')) {
            $licenses = $user->licenses;
        } elseif ($user->employee) {
            $licenses = $user->employee->licenses;
        } else {
            $licenses = collect();
        }

        if ($licenseId) {
            $licenses = $licenses->where('id', $licenseId);
        }
    }

    // 🔹 Filter periode
    $query->whereHas('journal', function ($q) use ($startDate, $endDate) {
        $q->whereBetween('transaction_date', [$startDate, $endDate]);
    });

    // 🔹 Filter lisensi
    if ($licenses->isNotEmpty()) {
        $query->whereHas('journal', function ($q) use ($licenses) {
            $q->whereIn('license_id', $licenses->pluck('id'));
        });
    }

    $details = $query
        ->join('accounting_accounts', 'accounting_accounts.id', '=', 'accounting_journal_details.account_id')
        ->orderByRaw('CAST(accounting_accounts.account_code AS INTEGER) ASC')
        ->orderBy(
            AccountingJournal::select('transaction_date')
                ->whereColumn('id', 'accounting_journal_details.journal_id')
        )
        ->select('accounting_journal_details.*')
        ->get();

    // 🔹 Kelompokkan per akun
    $ledger = [];
    foreach ($details->groupBy('account_id') as $accountId => $items) {
        $balance = 0;
        $rows = [];

        foreach ($items as $detail) {
            $balance += ($detail->debit - $detail->credit);

            $rows[] = [
                'journal_id'       => $detail->journal_id,
                'transaction_date' => $detail->journal->transaction_date,
                'journal_code'     => $detail->journal->journal_code,
                'description'      => $detail->description,
                'debit'            => $detail->debit,
                'credit'           => $detail->credit,
                'balance'          => $balance,
            ];
        }

        $ledger[$accountId] = [
            'account' => $items->first()->account,
            'rows'    => $rows,
        ];
    }

    return [$ledger, $licenses];
}

public function exportLedgerPdf(Request $request)
{
    $startDate = $request->get('start_date');
    $endDate   = $request->get('end_date');
    $licenseId = $request->get('license_id');

    $user = auth()->user();

    // 🔹 Tentukan nama lisensi
    if ($user->hasRole('Super-Admin')) {
        if ($licenseId) {
            $license = License::find($licenseId);
            $licenseName = $license?->name ?? '-';
        } else {
            $licenseName = 'Semua Lisensi';
        }
    } else {
        if ($user->hasRole('Pemilik Lisensi')) {
            $licenseName = $user->licenses->pluck('name')->join(', ');
        } elseif ($user->employee) {
            $licenseName = $user->employee->licenses->pluck('name')->join(', ');
        } else {
            $licenseName = '-';
        }
    }

    // 🔹 Ambil data ledger sesuai filter
    [$ledger, $licenses] = $this->getLedgerData(
        $startDate,
        $endDate,
        $user->hasRole('Super-Admin') ? License::all() : ($user->hasRole('Pemilik Lisensi') ? $user->licenses : $user->employee?->licenses),
        $licenseId
    );

    // 🔹 Load view PDF
    $pdf = Pdf::loadView('journals.ledgerpdf', compact(
        'ledger', 'licenseName', 'startDate', 'endDate'
    ))->setPaper('a4', 'landscape');

    return $pdf->stream('Buku Besar '.$startDate.'_to_'.$endDate.'.pdf');
}


public function trialBalance(Request $request)
{
    $user = auth()->user();

    // Default periode (bulan berjalan)
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    // 🔹 Filter lisensi sesuai role
    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Pemilik Lisensi')) {
        $licenses = $user->licenses;
    } else {
        $licenses = $user->employee?->licenses ?? collect();
    }

    // 🔹 Lisensi aktif → default ambil yang pertama
    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    // 🔹 Ambil akun yang sudah dikelompokkan
    $groupedAccounts = $this->getTrialBalanceAccounts($startDate, $endDate, $activeLicenseId);

    // 🔹 Hitung total debit & kredit
    $totalDebit  = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));
    $totalCredit = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));

    return view('journals.trialbalance', compact(
        'groupedAccounts',
        'totalDebit',
        'totalCredit',
        'startDate',
        'endDate',
        'licenses',
        'activeLicenseId'
    ));
}
private function buildGroupedAccounts(
    \Closure $journalFilter,
    $licenseId = null
) {

    $accounts = AccountingAccount::query()
        ->where('is_parent', false)
        ->when($licenseId, function ($query) use ($licenseId) {
            $query->where('license_id', $licenseId);
        })
        ->get();

    $details = AccountingJournalDetail::query()
        ->whereHas('journal', function ($query) use ($journalFilter, $licenseId) {

            /*
             * Filter journal yang sama dengan filter sebelumnya
             */
            $journalFilter($query);

            /*
             * Filter license langsung pada accounting_journals
             */
            if ($licenseId) {
                $query->where('license_id', $licenseId);
            }
        })
        ->selectRaw('
            account_id,
            COALESCE(SUM(debit), 0) AS debit,
            COALESCE(SUM(credit), 0) AS credit
        ')
        ->groupBy('account_id')
        ->get()
        ->keyBy('account_id');

    return $accounts
        ->map(function ($account) use ($details) {

            $detail = $details->get($account->id);

            $debit = (float) ($detail->debit ?? 0);
            $credit = (float) ($detail->credit ?? 0);

            switch ($account->category) {

                case 'AKTIVA':
                case 'BEBAN':

                    $balance = $debit - $credit;

                    break;

                case 'KEWAJIBAN':
                case 'EKUITAS':
                case 'PENDAPATAN':

                    $balance = $credit - $debit;

                    break;

                default:

                    $balance = $debit - $credit;

                    break;
            }

            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'category'     => $account->category,
                'sub_category' => $account->sub_category,
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $balance,
            ];
        })

        ->groupBy('category')

        ->map(function ($catGroup) {

            return $catGroup
                ->groupBy('sub_category')
                ->map(function ($subGroup) {

                    return [
                        'accounts'        => $subGroup,
                        'subtotalDebit'   => $subGroup->sum('debit'),
                        'subtotalCredit'  => $subGroup->sum('credit'),
                        'subtotalBalance' => $subGroup->sum('balance'),
                    ];
                });
        });
}
// private function buildGroupedAccounts(\Closure $journalFilter, $licenseId = null)
// {
//     $accounts = AccountingAccount::query()
//         ->where('is_parent', false)
//         ->when($licenseId, function ($q) use ($licenseId) {
//             $q->where('license_id', $licenseId);
//         })
//         ->get()
//         ->map(function ($account) use ($journalFilter) {

//             $debit = AccountingJournalDetail::query()
//                 ->where('account_id', $account->id)
//                 ->whereHas('journal', $journalFilter)
//                 ->sum('debit');

//             $credit = AccountingJournalDetail::query()
//                 ->where('account_id', $account->id)
//                 ->whereHas('journal', $journalFilter)
//                 ->sum('credit');

//             switch ($account->category) {
//                 case 'AKTIVA':
//                 case 'BEBAN':
//                     $balance = $debit - $credit;
//                     break;

//                 case 'KEWAJIBAN':
//                 case 'EKUITAS':
//                 case 'PENDAPATAN':
//                     $balance = $credit - $debit;
//                     break;

//                 default:
//                     $balance = $debit - $credit;
//             }

//             return [
//                 'account_code' => $account->account_code,
//                 'account_name' => $account->account_name,
//                 'category'     => $account->category,
//                 'sub_category' => $account->sub_category,
//                 'debit'        => $debit,
//                 'credit'       => $credit,
//                 'balance'      => $balance,
//             ];
//         });

//     return $accounts
//         ->groupBy('category')
//         ->map(function ($catGroup) {
//             return $catGroup->groupBy('sub_category')->map(function ($subGroup) {
//                 return [
//                     'accounts'        => $subGroup,
//                     'subtotalDebit'   => $subGroup->sum('debit'),
//                     'subtotalCredit'  => $subGroup->sum('credit'),
//                     'subtotalBalance' => $subGroup->sum('balance'),
//                 ];
//             });
//         });
// }
private function getBalanceSheetAccounts($startDate, $endDate, $licenseId = null)
{
    return $this->buildGroupedAccounts(function ($query) use ($startDate, $endDate, $licenseId) {

        $query->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($licenseId) {
            $query->where('license_id', $licenseId);
        }

    }, $licenseId);
}
private function getTrialBalanceAccounts($startDate, $endDate, $licenseId = null)
{
    return $this->buildGroupedAccounts(function ($query) use ($startDate, $endDate, $licenseId) {

        $query->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($licenseId) {
            $query->where('license_id', $licenseId);
        }

    }, $licenseId);
}

public function exportTrial(Request $request)
{
    $user = auth()->user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Pemilik Lisensi')) {
        $licenses = $user->licenses;
    } else {
        $licenses = $user->employee?->licenses ?? collect();
    }

    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    $groupedAccounts = $this->getTrialBalanceAccounts(
        $startDate,
        $endDate,
        $activeLicenseId
    );

    $totalDebit  = collect($groupedAccounts)
        ->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));

    $totalCredit = collect($groupedAccounts)
        ->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));

    $pdf = Pdf::loadView('journals.trialbalance-pdf', [
        'groupedAccounts' => $groupedAccounts,
        'startDate'       => $startDate,
        'endDate'         => $endDate,
        'licenses'        => $licenses,
        'activeLicenseId' => $activeLicenseId,
        'totalDebit'      => $totalDebit,
        'totalCredit'     => $totalCredit,
        'request'         => $request,
    ]);

    return $pdf->stream('trial-balance.pdf');
}

public function print(AccountingJournal $journal)
{
    $pdf = Pdf::loadView('journals.print', compact('journal'))
        ->setPaper('a4', 'landscape');

    return $pdf->stream('Jurnal Transaksi '.$journal->journal_code.'.pdf');
}

public function balanceSheet(Request $request)
{
    $user = auth()->user();

    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->endOfMonth()->toDateString();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Pemilik Lisensi')) {
        $licenses = $user->licenses;
    } else {
        $licenses = $user->employee?->licenses ?? collect();
    }

    $activeLicenseId = $request->get('license_id') ?? session('active_license_id');

    $viewType = $request->get('view', 'default'); // 🔹 default | skontro

    $groupedAccounts = $this->getBalanceSheetAccounts($startDate, $endDate, $activeLicenseId);

    $totals = BalanceSheetService::calculate($groupedAccounts);

    $totalDebit  = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalDebit']));
    $totalCredit = collect($groupedAccounts)->sum(fn($cat) => collect($cat)->sum(fn($sub) => $sub['subtotalCredit']));

    return view('reports.balance_sheet', array_merge([
        'startDate'       => $startDate,
        'endDate'         => $endDate,
        'licenses'        => $licenses,
        'activeLicenseId' => $activeLicenseId,
        'groupedAccounts' => $groupedAccounts,
        'viewType'        => $viewType,
        'totalDebit'      => $totalDebit,
        'totalCredit'     => $totalCredit,
    ], $totals));
}

}



