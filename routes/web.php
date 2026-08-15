<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingAccountController;
use App\Http\Controllers\AccountingJournalController;
use App\Http\Controllers\AccountingPeriodController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\LicensesController;
use App\Http\Controllers\LicenseHoldersController;
use App\Http\Controllers\LicenseHolderEducationController;
use App\Http\Controllers\LicenseHolderWorkExperience;
use App\Http\Controllers\LicenseHolderFamilyMember;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeEducationController;
use App\Http\Controllers\EmployeeWorkExperienceController;
use App\Http\Controllers\EmployeeFamilyMemberController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\LicenseImportController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\LicenseNotificationController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\JournalExportController;
use App\Http\Controllers\Api\AccountingApiController;
use App\Http\Controllers\Api\JournalApiController;
use App\Http\Controllers\Api\LicenseSessionController;
use App\Models\License;

Route::get('/', function () {
    return view('login');
});

require __DIR__.'/auth.php';
// Auth::routes();

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

Route::middleware(['auth', 'permission:lisensi.tambah'])->group(function () {
    Route::resource('/licenses', LicensesController::class)->only(['create', 'store']);
});

Route::middleware(['auth', 'permission:lisensi.ubah'])->group(function () {
    Route::resource('/licenses', LicensesController::class)->only(['edit', 'update']);
});

Route::middleware(['auth', 'role:Super-Admin|Pemilik Lisensi'])->group(function () {
    Route::resource('/licenses', LicensesController::class)->only(['index', 'show', 'destroy']);
});

Route::get('/switch-license/{license}', function ($licenseId) {
    $license = License::findOrFail($licenseId);
    $user = auth()->user();

    $isAuthorized = match (true) {
        $user->hasRole('Super-Admin') => true,
        $user->hasRole('Pemilik Lisensi') => $license->owners()->where('users.id', $user->id)->exists(),
        $user->hasRole(['Karyawan', 'Akuntan']) => $user->employee && $user->employee->licenses()->where('licenses.id', $license->id)->exists(),
        default => false,
    };

    abort_unless($isAuthorized, 403);

    session([
        'active_license_id' => $license->id,
        'active_license_name' => $license->name,
    ]);

    return back()->with('success', 'Lisensi aktif telah diganti ke ' . $license->name);
})->name('switch.license')->middleware('auth');



Route::middleware(['auth', 'permission:pemilik-lisensi.tambah'])->group(function () {
    Route::resource('/license_holders', LicenseHoldersController::class)->only(['create', 'store']);
});

Route::middleware(['auth', 'permission:pemilik-lisensi.ubah'])->group(function () {
    Route::resource('/license_holders', LicenseHoldersController::class)->only(['edit', 'update']);
});

Route::middleware(['auth', 'role:Super-Admin|Pemilik Lisensi'])->group(function () {
    Route::resource('/license_holders', LicenseHoldersController::class)->only(['index', 'show', 'destroy']);
});

Route::middleware(['auth', 'permission:karyawan.tambah'])->group(function () {
    Route::resource('/employees', EmployeeController::class)->only(['create', 'store']);
});

Route::middleware(['auth', 'permission:karyawan.ubah'])->group(function () {
    Route::resource('/employees', EmployeeController::class)->only(['edit', 'update']);
});

Route::middleware(['auth', 'role:Super-Admin|Karyawan|Pemilik Lisensi|Akuntan'])->group(function () {
    route::resource('/employees', EmployeeController::class)->only(['index', 'show', 'destroy']);
        Route::post('/employees/datatable', [EmployeeController::class, 'datatable'])
        ->name('employees.datatable');
});

Route::get('/employees/generate-nik/{licenseId}', [EmployeeController::class, 'generateNikAjax']);

Route::get('/students/generate-nis/{licenseId}', [StudentsController::class, 'generateNisAjax']);

// Route::get('/get-accounts-by-license/{license}', function($licenseId) {
//     return \App\Models\AccountingAccount::where('license_id', $licenseId)
//         ->orderBy('account_code')
//         ->get();
// });

Route::get('/journals/get-next-code/{license}', [AccountingJournalController::class, 'getNextCode']);

Route::middleware(['auth', 'permission:siswa.tambah'])->group(function () {
    Route::resource('/students', StudentsController::class)->only(['create', 'store']);
});

Route::middleware(['auth', 'permission:siswa.ubah'])->group(function () {
    Route::resource('/students', StudentsController::class)->only(['edit', 'update']);
});

Route::middleware(['auth', 'role:Super-Admin|Pemilik Lisensi|Akuntan|Siswa'])->group(function () {
    Route::resource('/students', StudentsController::class)->only(['index', 'show', 'destroy']);
});

Route::middleware(['auth', 'role:Super-Admin|Akuntan'])->group(function () {

    Route::post(
        'accounting/datatable',
        [AccountingAccountController::class, 'datatable']
    )->name('accounting.datatable');

    Route::resource('accounting', AccountingAccountController::class)
        ->parameters([
            'accounting' => 'account'
        ]);
});

Route::get('/journals/{journal}/print', [AccountingJournalController::class, 'print'])
    ->name('journals.print');


Route::get('/journals/report', [AccountingJournalController::class, 'report'])
    ->name('journals.report')
    ->middleware(['role:Super-Admin|Akuntan|Pemilik Lisensi']);

Route::get('/journals/general', [AccountingJournalController::class, 'generalJournal'])
    ->name('journals.general')
    ->middleware(['role:Super-Admin|Akuntan|Pemilik Lisensi']);
Route::get('/journals/export/pdf', [AccountingJournalController::class, 'exportPDF'])->name('journals.export.pdf');

Route::get('/journals/ledger', [AccountingJournalController::class, 'ledger'])
    ->name('journals.ledger')
    ->middleware(['role:Super-Admin|Akuntan|Pemilik Lisensi']);
Route::get('/journals/ledgerpdf', [AccountingJournalController::class, 'exportLedgerPdf'])->name('ledgerpdf');

Route::get('/journals/export/trial-pdf', [AccountingJournalController::class, 'exportTrial'])
    ->name('journals.trial.pdf');

Route::get('/journals/income/pdf', [AccountingJournalController::class, 'exportIncome'])
    ->name('journals.income.pdf');


Route::middleware(['role:Super-Admin|Akuntan|Pemilik Lisensi'])->group(function () {
    Route::resource('journals', AccountingJournalController::class);
});

Route::get('/reports/balance_sheet', [AccountingJournalController::class, 'balanceSheet'])
    ->name('reports.balance_sheet')
    ->middleware(['role:Super-Admin|Akuntan|Pemilik Lisensi']);

Route::get('/reports/income-statement', [AccountingReportController::class, 'incomeStatement'])
    ->name('reports.income_statement');
Route::get('/reports/income-statement/pdf', [AccountingReportController::class, 'exportPdf'])
    ->name('reports.income_statement.pdf');

Route::get('/reports/income-statement/excel', [AccountingReportController::class, 'exportExcel'])
    ->name('reports.income_statement.excel');

Route::get('/journals/{journal}/export', [JournalExportController::class, 'export'])
    ->name('journals.export');

Route::get('/journals/export/general', [JournalExportController::class, 'exportGeneral'])
    ->name('general.export');

Route::get('/ledger/export', [JournalExportController::class, 'exportLedger'])
    ->name('ledger.export');

Route::get('/trial/export', [JournalExportController::class, 'exportTrialBalance'])
    ->name('trial.export');





Route::patch('/notifications/{notification}/read', [LicenseNotificationController::class, 'markAsRead'])->name('notifications.read');

Route::post('/notifications/read-all', [LicenseNotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    Route::get('/periods', [AccountingPeriodController::class, 'index'])->name('periods.index');
    Route::post('/periods/close', [AccountingPeriodController::class, 'close'])->name('periods.close');
    Route::post('/periods/reopen', [AccountingPeriodController::class, 'reopen'])->name('periods.reopen');
    Route::delete('/periods/{period}', [AccountingPeriodController::class, 'destroy'])
    ->name('periods.destroy');


Route::middleware(['auth', 'role:Super-Admin'])->group(function () {
    Route::resource('roles', RoleController::class);
});

Route::middleware(['auth', 'role:Super-Admin|Pemilik Lisensi'])->group(function () {
    route::resource('/users', UsersController::class);
});

Route::get('/license_holders/{id}/licenses', [LicenseHoldersController::class, 'showLicense'])->name('license_holders.licenses');
Route::get('/license_holders/{id}/profile', [LicenseHoldersController::class, 'showProfile'])->name('license_holders.profile');
Route::get('/license_holders/{id}/educations', [LicenseHoldersController::class, 'showTab'])->name('license_holders.educations');
Route::get('/license_holders/{id}/workers', [LicenseHoldersController::class, 'showWorks'])->name('license_holders.workers');
Route::get('/license_holders/{id}/families', [LicenseHoldersController::class, 'showFams'])->name('license_holders.families');

route::resource('/license_holder_educations', LicenseHolderEducationController::class);
route::resource('/license_holder_workers', LicenseHolderWorkExperience::class);
route::resource('/license_holder_families', LicenseHolderFamilyMember::class);

Route::get('/employees/{id}/profile', [EmployeeController::class, 'showProfile'])->name('employees.profile');
Route::get('/employees/{id}/educations', [EmployeeController::class, 'showTab'])->name('employees.educations');
Route::get('/employees/{id}/workers', [EmployeeController::class, 'showWorks'])->name('employees.workers');
Route::get('/employees/{id}/families', [EmployeeController::class, 'showFams'])->name('employees.families');

route::resource('/employee_educations', EmployeeEducationController::class);
route::resource('/employee_workers', EmployeeWorkExperienceController::class);
route::resource('/employee_families', EmployeeFamilyMemberController::class);

Route::get('/students/{id}/profile', [StudentsController::class, 'showProfile'])->name('students.profile');
Route::get('/students/{id}/educations', [StudentsController::class, 'showTab'])->name('students.educations');

Route::get('/kas/export/excel', [KasController::class, 'exportExcel'])->name('kas.export.excel');


Route::get('/api/cities/{province_id}', function ($province_id) {
    return \App\Models\City::where('province_id', $province_id)->select('id', 'name')->get();
});

Route::get('/api/districts/{city_id}', function ($city_id) {
    return \App\Models\District::where('city_id', $city_id)->select('id', 'name')->get();
});

Route::get('/api/sub_districts/{district_id}', function ($district_id) {
    return \App\Models\SubDistrict::where('district_id', $district_id)->select('id', 'name')->get();
});

Route::get('/api/postal_codes/{sub_district_id}', function ($sub_district_id) {
    return \App\Models\PostalCode::where('sub_district_id', $sub_district_id)->select('id', 'postal_code')->get();
});

Route::middleware(['auth'])->group(function () {
    // sinkronisasi lisensi aktif dari navbar (POST dari form/navbar)
    Route::post('/active-license', [LicenseSessionController::class, 'set'])->name('active-license.set');

    // data untuk form jurnal
    Route::get('/get-accounts-by-license/{licenseId?}', [AccountingApiController::class, 'accounts']);
    Route::get('/get-students', [AccountingApiController::class, 'students']);
    Route::get('/get-employees', [AccountingApiController::class, 'employees']);
    Route::get('/get-licenseholders', [AccountingApiController::class, 'licenseholders']);
    Route::get('/get-licenses', [AccountingApiController::class, 'licenses']); // utk person_type=license

    // kode jurnal berikutnya
    Route::get('/journals/next-code/{licenseId?}', [JournalApiController::class, 'nextCode']);
});


// Route::get('/accounts/by-license/{id}', function ($id) {
//     $user = Auth::user();

//     $licenseIds = $user->hasRole('Super-Admin')
//         ? License::pluck('id')
//         : ($user->licenses ?? $user->employee?->licenses ?? collect())->pluck('id');

//     // Ubah semua ke string agar perbandingan sukses
//     $licenseIds = $licenseIds->map(fn($i) => (string) $i);

//     if (! $licenseIds->contains((string) $id)) {
//         abort(403, 'Akses ditolak.');
//     }

//     $accounts = \App\Models\AccountingAccount::where('license_id', $id)
//         ->where('is_parent', false)
//         ->where('is_active', true)
//         ->orderBy('account_code')
//         ->get();

//     return response()->json($accounts);
// })->name('accounts.byLicense');





