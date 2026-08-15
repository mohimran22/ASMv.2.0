<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;
use App\Models\AccountingAccount;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AccountingAccountController extends Controller
{
    public function index()
{
    return view('accounting.index');
}

public function datatable(Request $request)
{
    $user = Auth::user();

    $query = AccountingAccount::query()
        ->leftJoin(
            'licenses',
            'licenses.id',
            '=',
            'accounting_accounts.license_id'
        )
        ->select([
            'accounting_accounts.*',
            'licenses.license_type as license_type',
            'licenses.name as license_name',
        ]);

    if ($user->hasRole('Super-Admin')) {

        // Super Admin dapat melihat semua akun

    } elseif ($user->hasRole('Pemilik Lisensi')) {

        $licenses = optional($user->licenses);

        if ($licenses?->isNotEmpty()) {

            $query->whereIn(
                'accounting_accounts.license_id',
                $licenses->pluck('id')
            );

        } else {
            abort(403, 'Lisensi tidak ditemukan untuk pemilik lisensi.');
        }

    } elseif ($user->hasRole('Akuntan')) {

        $licenses = optional($user->employee)->licenses;

        if ($licenses && $licenses->count() > 0) {

            $query->whereIn(
                'accounting_accounts.license_id',
                $licenses->pluck('id')
            );

        } else {
            abort(403, 'Lisensi tidak ditemukan.');
        }

    } else {

        abort(403, 'Role Tidak diizinkan');
    }

    if (!$user->hasRole('Super-Admin')) {

        $activeLicenseId = session('active_license_id');

        if (!$activeLicenseId) {
            abort(403, 'Silakan pilih lisensi aktif terlebih dahulu.');
        }

        $query->where(
            'accounting_accounts.license_id',
            $activeLicenseId
        );
    }

    return DataTables::eloquent($query)

        ->addIndexColumn()

        ->editColumn('license_type', function ($account) {
            return $account->license_type ?? '-';
        })

        ->editColumn('license_name', function ($account) {
            return $account->license_name ?? '-';
        })

        ->editColumn('account_code', function ($account) {
            return $account->account_code ?? '-';
        })

        ->editColumn('account_name', function ($account) {
            return $account->account_name ?? '-';
        })

        ->editColumn('category', function ($account) {
            return $account->category ?? '-';
        })

        ->editColumn('sub_category', function ($account) {
            return $account->sub_category ?? '-';
        })

        ->editColumn('initial_balance', function ($account) {
            return number_format(
                (float) $account->initial_balance,
                2
            );
        })

        ->editColumn('is_active', function ($account) {

            if ($account->is_active) {
                return '<span class="badge bg-success">Aktif</span>';
            }

            return '<span class="badge bg-secondary">Nonaktif</span>';
        })

        ->addColumn('action', function ($account) {

            $buttons = '';

            if (auth()->user()->can('akun-akuntansi.ubah')) {

                $buttons .= '
                    <a href="' . route('accounting.edit', $account->id) . '"
                       class="btn btn-warning btn-sm"
                       title="Edit">
                        <i class="ti ti-edit"></i>
                    </a>
                ';
            }

            if (auth()->user()->can('akun-akuntansi.hapus')) {

                $buttons .= '
                    <form action="' . route('accounting.destroy', $account->id) . '"
                          method="POST"
                          style="display:inline-block;">

                        ' . csrf_field() . '

                        ' . method_field('DELETE') . '

                        <button
                            type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm(\'Hapus akun ini?\')"
                            title="Hapus">

                            <i class="ti ti-trash"></i>

                        </button>
                    </form>
                ';
            }

            return $buttons;
        })

        ->rawColumns([
            'is_active',
            'action',
        ])

        ->make(true);
}
    // public function index(Request $request)
    // {
    //     $user = Auth::user();

    //     $query = AccountingAccount::with(['license', 'parent']);

    //     if ($user->hasRole('Super-Admin')) {
    //         // Lihat semua akun
    //     } elseif ($user->hasRole('Pemilik Lisensi')) {
    //     $licenses = optional($user->licenses);

    //     if ($licenses?->isNotEmpty()) {
    //         $query->whereIn('license_id', $licenses->pluck('id'));
    //     } else {
    //         abort(403, 'Lisensi tidak ditemukan untuk pemilik lisensi.');
    //     } 
        
    // } elseif ($user->hasRole('Akuntan')) {
    //         $licenses = optional($user->employee)->licenses; // ← pakai relasi belongsToMany

    //         if ($licenses && $licenses->count() > 0) {
    //             $query->whereIn('license_id', $licenses->pluck('id'));
    //         } else {
    //             abort(403, 'Lisensi tidak ditemukan.');
    //         }
    //     } else {
    //         abort(403, 'Role Tidak diizinkan');
    //     }

    //     // ✅ Tambahkan filter lisensi aktif (kecuali Super Admin)
    //     if (! $user->hasRole('Super-Admin')) {
    //         $activeLicenseId = session('active_license_id');

    //         if (!$activeLicenseId) {
    //             abort(403, 'Silakan pilih lisensi aktif terlebih dahulu.');
    //         }

    //         $query->where('license_id', $activeLicenseId);
    //     }

    //     $accounts = $query->orderBy('account_code')->get();


    //     return view('accounting.index', compact('accounts'));
    // }


    public function create()
{
    $user = Auth::user();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Akuntan')) {
        $licenses = $user->employee?->licenses;

        if (!$licenses || $licenses->count() === 0) {
            abort(403, 'Lisensi tidak ditemukan.');
        }
    } else {
        abort(403, 'Role tidak diizinkan.');
    }

    $parentAccounts = AccountingAccount::where('is_parent', true)->get();

    return view('accounting.create', compact('licenses', 'parentAccounts'));
}


    public function store(Request $request)
    {
        $request->validate([
            'license_id' =>   'required|exists:licenses,id',
            'account_code' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'required|string|max:255',
            'initial_balance' => 'nullable|numeric',
            'is_parent' => 'boolean',
            'parent_id' => 'nullable|uuid|exists:accounting_accounts,id',
        ]);

        AccountingAccount::create([
            'id' => Str::uuid(),
            'license_id' => $request->license_id ?? null, // sesuaikan jika ada
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'category' => $request->category,
            'sub_category' => $request->sub_category,
            'initial_balance' => $request->initial_balance,
            'is_parent' => $request->is_parent ?? false,
            'parent_id' => $request->parent_id,
            'is_active' => true,
        ]);

        return redirect()->route('accounting.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(AccountingAccount $account)
    {
        $user = Auth::user();

    if ($user->hasRole('Super-Admin')) {
        $licenses = License::all();
    } elseif ($user->hasRole('Akuntan')) {
        $licenses = $user->employee?->licenses;

        if (!$licenses || $licenses->count() === 0) {
            abort(403, 'Lisensi tidak ditemukan.');
        }
    } else {
        abort(403, 'Role tidak diizinkan.');
    }

    $parentAccounts = AccountingAccount::where('is_parent', true)->get();

        return view('accounting.edit', compact('account', 'licenses', 'parentAccounts'));
    }

    public function update(Request $request, AccountingAccount $account)
    {
        $request->validate([
            'account_code' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'required|in:Debit,Kredit',
            'initial_balance' => 'nullable|numeric',
            'is_parent' => 'boolean',
            'parent_id' => 'nullable|uuid|exists:accounting_accounts,id',
        ]);

        $account->update([
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'category' => $request->category,
            'sub_category' => $request->sub_category,
            'initial_balance' => $request->initial_balance,
            'is_parent' => $request->is_parent ?? false,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('accounting.index')->with('success', 'Akun berhasil diubah.');
    }

     public function destroy(AccountingAccount $account)
    {
        $account->delete();
        return redirect()->route('accounting.index')->with('success', 'Akun berhasil dihapus.');
    }

}
