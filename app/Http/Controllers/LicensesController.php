<?php
   
namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use App\Models\AccountingAccount;
use App\Models\LicenseNotification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

 
class LicensesController extends Controller
{
    
    public function index(Request $request) 
    {

        if ($request->ajax()) {
            $licenses = $this->getFilteredLicenses();

            $buildingTypes = [1 => 'Ruko', 2 => 'Gedung', 3 => 'Rumah'];
            $buildingStatuses = [1 => 'Milik Sendiri', 2 => 'Sewa', 3 => 'Lain-Lain'];
            $buildingConditions = [1 => 'Baik', 2 => 'Perlu Renovasi', 3 => 'Rusak Berat'];
            $status = ['active' => 'Aktif', 'inactive' => 'Masa Tenggang', 'expired' => 'Sudah Habis'];
            $buildinghasAC = [true => 'Punya', false => 'Tidak Punya'];

            return DataTables::of($licenses)
                ->addIndexColumn()
                ->addColumn('province_name', fn($row) => $row->province->name ?? '-')
                ->addColumn('city_name', fn($row) => $row->city->name ?? '-')
                ->addColumn('district_name', fn($row) => $row->district->name ?? '-')
                ->addColumn('sub_district_name', fn($row) => $row->subDistrict->name ?? '-')
                ->addColumn('postal_code', fn($row) => $row->postalCode->postal_code ?? '-')
                ->editColumn('building_type', fn($row) => $buildingTypes[$row->building_type] ?? 'Tidak Diketahui')
                ->editColumn('building_status', fn($row) => $buildingStatuses[$row->building_status] ?? 'Tidak Diketahui')
                ->editColumn('building_condition', fn($row) => $buildingConditions[$row->building_condition] ?? 'Tidak Diketahui')
                ->editColumn('building_has_ac', fn($row) => $buildinghasAC[$row->building_has_ac] ?? 'Tidak Diketahui')
                ->editColumn('contract_document', function ($row) {
                    if ($row->contract_document) {
                        // Ambil URL dari disk public
                        $url = Storage::disk('public')->url($row->contract_document);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->editColumn('document_form', function ($row) {
                    if ($row->document_form) {
                        $url = Storage::disk('public')->url($row->document_form);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->editColumn('name', function ($row) {
                    $url = route('licenses.edit', $row->id); // Ganti dengan route detailmu
                    return '<a href="'.$url.'">'.e($row->name).'</a>';
                })
                ->addColumn('instagram', fn($row) => $this->linkOrDash($row->instagram))
                ->addColumn('facebook_page', fn($row) => $this->linkOrDash($row->facebook_page))
                ->addColumn('tiktok', fn($row) => $this->linkOrDash($row->tiktok))
                ->addColumn('youtube', fn($row) => $this->linkOrDash($row->youtube))
                ->addColumn('google_maps', fn($row) => $this->linkOrDash($row->google_maps))
                ->addColumn('landing_page_student_registration', fn($row) => $this->linkOrDash($row->landing_page_student_registration))
                ->addColumn('join_date', fn($row) => $row->join_date ? Carbon::parse($row->join_date)->format('d/m/Y') : '-')
                ->addColumn('expired_date', fn($row) => $row->expired_date ? Carbon::parse($row->expired_date)->format('d/m/Y') : '-')
                ->addColumn('building_rent_expired_date', fn($row) => $row->building_rent_expired_date ? Carbon::parse($row->building_rent_expired_date)->format('d/m/Y') : '-')
                ->addColumn('status', function ($row) use ($status) {
                    $label = $status[$row->status] ?? 'Tidak Diketahui';

                    $color = match ($label) {
                        'Aktif' => 'success',
                        'Masa Tenggang' => 'warning',
                        'Sudah Habis' => 'danger',
                        default => 'secondary',
                    };

                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })

                ->addColumn('action', function ($license) {
                    $buttons = '';
                    if (auth()->user()->can('lisensi.ubah')) {
                        $buttons .= '<a href="' . route('licenses.edit', $license->id) . '" class="btn btn-icon btn-sm btn-warning me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('lisensi.hapus')) {
                        $buttons .= '<button data-id="' . $license->id . '" class="btn btn-icon btn-sm btn-danger delete-license" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['action', 'status', 'instagram', 'facebook_page', 'tiktok', 'youtube', 'name', 'google_maps', 'landing_page_student_registration', 'contract_document', 'document_form'])
                ->make(true);
        }

        return view('licenses.index');
  
    }

        private function getFilteredLicenses()
    {
        $auth = auth()->user();

        $query = License::query()
        ->with(['province', 'city', 'district', 'subDistrict', 'postalCode'])
        ->when(!$auth->hasRole('Super-Admin'), function ($query) use ($auth) {
            $activeLicenseId = session('active_license_id');

            if (!$activeLicenseId) {
                if ($auth->hasRole('Pemilik Lisensi')) {
                    $first = License::whereHas('owners', fn($q) => $q->where('users.id', $auth->id))->first();
                } elseif ($auth->hasRole('Karyawan') && $auth->employee) {
                    $first = $auth->employee->licenses->first();
                }

                if (!empty($first)) {
                    session([
                        'active_license_id' => $first->id,
                        'active_license_name' => $first->name,
                    ]);
                    $activeLicenseId = $first->id;
                }
            }

            $query->where('id', $activeLicenseId);
        })

        ->orderBy('id', 'desc');


        return $query->get();
    }

    private function linkOrDash($url)
    {
        return $url ? '<a href="' . e($url) . '" target="_blank" title="Lihat"><i class="ti ti-check text-success"></i></a>' : '<i class="ti ti-minus text-muted"></i>';
    }

    public function create() 
    {
        $provinces = Province::all();
        return view('licenses.create', compact('provinces'));
    }

    public function store(Request $request) 
{
    // Aturan validasi dasar
    $rules = [
        'license_type' => 'required|in:FO,SO,LO,LC', 
        'name' => 'required|unique:licenses,name',
        'email' => 'required|email|unique:licenses,email',
        'address' => 'required',
        'phone' => 'required',
        'building_type' => 'nullable|in:1,2,3',
        'building_status' => 'nullable|in:1,2,3',
        'building_rent_expired_date' => ['nullable', 'date_format:Y-m-d'],
        'building_area' => 'nullable|numeric|min:0',
        'building_condition' => 'nullable|in:1,2,3',
        'building_has_ac' => 'nullable|boolean',
        'instagram' => 'nullable|url',
        'facebook_page' => 'nullable|url',
        'tiktok' => 'nullable|url',
        'youtube' => 'nullable|url',
        'google_maps' => 'nullable|url',
        'landing_page_student_registration' => 'nullable|url',
    ];

    // Kalau bukan FO (pusat) → wilayah wajib diisi
    if ($request->license_type !== 'FO') {
        $rules = array_merge($rules, [
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'sub_district_id' => 'required|exists:sub_districts,id',
            'postal_code_id' => 'required|exists:postal_codes,id',
            'join_date' => ['required', 'date_format:Y-m-d'],
            'expired_date' => ['required', 'date_format:Y-m-d'],
            'contract_agreement_number' => 'required',
            'contract_document' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'document_form' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);
    } else {
        // FO → wilayah boleh kosong
        $rules = array_merge($rules, [
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'postal_code_id' => 'nullable|exists:postal_codes,id',
            'join_date' => ['nullable', 'date_format:Y-m-d'],
            'expired_date' => ['nullable', 'date_format:Y-m-d'],
            'contract_agreement_number' => 'nullable',
            'contract_document' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            'document_form' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);
    }

    $validated = $request->validate($rules);

    // Cegah duplikat district, hanya untuk non-FO
    if ($request->license_type !== 'FO' && License::where('district_id', $validated['district_id'])->exists()) {
        return back()->withErrors(['district_id' => 'Sudah ada lisensi di kecamatan ini.'])->withInput();
    }

    // Set license_id
    $validated['license_id'] = $request->license_type !== 'FO' 
        ? $validated['district_id'] 
        : '0000'; // pusat tidak punya district

    // Upload file kontrak
    if ($request->hasFile('contract_document')) {
        $validated['contract_document'] = $request->file('contract_document')
            ->store('contracts', 'public');
    }

    // Upload file form
    if ($request->hasFile('document_form')) {
        $validated['document_form'] = $request->file('document_form')
            ->store('contracts', 'public');
    }

    if ($validated['license_type'] === 'FO') {
        // Pusat → status aktif terus
        $validated['status'] = 'active';
    } else {

        // Cabang → cek expired_date
        $expiredDate = Carbon::parse($validated['expired_date']);
        $today = now();
        $fiveMonthsLater = $today->copy()->addMonths(5);

        if ($expiredDate->lt($today)) {
            $validated['status'] = 'expired';
        } elseif ($expiredDate->lte($fiveMonthsLater)) {
            $validated['status'] = 'inactive';
        } else {
            $validated['status'] = 'active';
        }
    }

    // Simpan lisensi
    $license = License::create($validated);

    // Kirim notifikasi kalau expired atau akan expired
    if ($validated['status'] === 'expired') {
        LicenseNotification::create([
            'license_id' => $license->id,
            'message' => "⚠️ Lisensi {$license->license_id} ({$license->license_type}) {$license->name} telah *expired* pada {$expiredDate->format('d-m-Y')}.",
            'read' => false,
        ]);
    } elseif ($validated['status'] === 'inactive') {
        LicenseNotification::create([
            'license_id' => $license->id,
            'message' => "📢 Lisensi {$license->license_id} ({$license->license_type}) {$license->name} akan *expired* pada {$expiredDate->format('d-m-Y')}.",
            'read' => false,
        ]);
    }

    return redirect()->route('licenses.index')->with('success', 'Data berhasil ditambahkan.');
}


     public function edit(License $license) 
     {
        $owners = User::role('Pemilik Lisensi')
        ->whereHas('licenses', function ($q) {
            $q->where('licenses.id', session('active_license_id'));
        })->get();

        if (auth()->user()->hasRole('Pemilik Lisensi')) {
            // Cek apakah lisensi ini milik dia
            if (!$license->owners->contains(auth()->id())) {
                abort(403, 'Anda tidak memiliki izin mengedit lisensi ini.');
            }

            // Cek apakah ini lisensi yang aktif
            if (session('active_license_id') != $license->id) {
                return redirect()
                    ->route('licenses.index')
                    ->with('error', 'Lisensi yang sedang dibuka bukan lisensi aktif.');
            }
        }


        $provinces = Province::all();
        $cities = City::where('province_id', $license->province_id)->get();
        $districts = District::where('city_id', $license->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $license->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $license->sub_district_id)->get();

        return view('licenses.edit', compact('license', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes', 'owners'));
    }

    public function update(Request $request, License $license) 
{
    $user = auth()->user();
    $activeLicenseId = session('active_license_id');

    if (!$user->hasRole('Super-Admin') && $license->id != $activeLicenseId) {
        abort(403, 'Anda hanya bisa mengedit lisensi yang aktif.');
    }

    $isOwner = $user->hasRole('Pemilik Lisensi');

    // Rules dasar
    $rules = [
        'license_type' => 'required|in:FO,SO,LO,LC',
        'name' => ['required', Rule::unique('licenses', 'name')->ignore($license->id)],
        'email' => ['required', 'email', Rule::unique('licenses')->ignore($license->id)],
        'address' => 'required',
        'phone' => 'required',
        'building_type' => 'nullable|in:1,2,3',
        'building_status' => 'nullable|in:1,2,3',
        'building_rent_expired_date' => ['nullable', 'date_format:Y-m-d'],
        'building_area' => 'nullable|numeric|min:0',
        'building_condition' => 'nullable|in:1,2,3',
        'building_has_ac' => 'nullable|boolean',
        'instagram' => 'nullable|url',
        'facebook_page' => 'nullable|url',
        'tiktok' => 'nullable|url',
        'youtube' => 'nullable|url',
        'google_maps' => 'nullable|url',
        'landing_page_student_registration' => 'nullable|url',
    ];

    // FO → wilayah nullable
    if ($request->license_type === 'FO') {
        $rules = array_merge($rules, [
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'postal_code_id' => 'nullable|exists:postal_codes,id',
            'join_date' => ['nullable', 'date_format:Y-m-d'],
            'expired_date' => ['nullable', 'date_format:Y-m-d'],
            'contract_agreement_number' => 'nullable',
        ]);
    } else {
        // Selain FO → wajib isi wilayah
        $rules = array_merge($rules, [
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => [
                'required',
                'exists:districts,id',
                Rule::unique('licenses', 'district_id')->ignore($license->id),
            ],
            'sub_district_id' => 'required|exists:sub_districts,id',
            'postal_code_id' => 'required|exists:postal_codes,id',
            'join_date' => ['required', 'date_format:Y-m-d'],
            'expired_date' => ['required', 'date_format:Y-m-d'],
            'contract_agreement_number' => 'required',
        ]);
    }

    // Hanya validasi file kalau bukan Pemilik Lisensi
    if (!$isOwner) {
        $rules['contract_document'] = ['nullable', 'file', 'mimes:pdf', 'max:2048'];
        $rules['document_form'] = ['nullable', 'file', 'mimes:pdf', 'max:2048'];
    }

    $validated = $request->validate($rules);

    // ⚠️ Hanya cek duplikat district_id kalau license_type bukan FO
    if ($request->license_type !== 'FO' 
        && $license->district_id != ($validated['district_id'] ?? null)) {
        $alreadyExists = License::where('district_id', $validated['district_id'])
            ->where('id', '!=', $license->id)
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors(['district_id' => 'Sudah ada lisensi di kecamatan ini.'])->withInput();
        }

        $validated['license_id'] = $validated['district_id'];
    }

    // Upload file (hanya non-owner)
    if (!$isOwner) {
        if ($request->hasFile('contract_document')) {
            if ($license->contract_document) {
                Storage::disk('public')->delete($license->contract_document);
            }
            $validated['contract_document'] = $request->file('contract_document')
                ->store('contracts_aqad', 'public');
        }

        if ($request->hasFile('document_form')) {
            if ($license->document_form) {
                Storage::disk('public')->delete($license->document_form);
            }
            $validated['document_form'] = $request->file('document_form')
                ->store('contracts_aqad', 'public');
        }
    }

        $defaultAccounts = Config::get('accounting_defaults.accounts');

        foreach ($defaultAccounts as $acc) {
            AccountingAccount::firstOrCreate(
                [
                    // Kombinasi unik, bukan account_code saja
                    'license_id' => $license->id,
                    'account_code' => $acc['account_code'],
                ],
                [
                    'id' => Str::uuid(),
                    'category' => $acc['category'] ?? null,
                    'account_name' => $acc['account_name'] ?? null,
                    'person_type' => $acc['person_type'] ?? null,
                    'is_active' => true,
                    'is_parent' => $acc['is_parent'] ?? false,
                    'initial_balance' => 0,
                    'sub_category' => $acc['sub_category'] ?? null,
                ]
            );
        }

    
    // Status lisensi
    if ($validated['license_type'] === 'FO') {
        $validated['status'] = 'active';
        $validated['expired_date'] = null;
    } else {
        $expiredDate = Carbon::parse($validated['expired_date']);
        $today = now();
        $fiveMonthsLater = $today->copy()->addMonths(5);

        if ($expiredDate->lt($today)) {
            $validated['status'] = 'expired';
            LicenseNotification::firstOrCreate([
                'license_id' => $license->id,
                'message' => "⚠️ Lisensi {$license->license_id} ({$license->license_type}) {$license->name} telah *expired* pada {$expiredDate->format('d-m-Y')}.",
            ], ['read' => false]);
        } elseif ($expiredDate->lte($fiveMonthsLater)) {
            $validated['status'] = 'inactive';
            LicenseNotification::firstOrCreate([
                'license_id' => $license->id,
                'message' => "📢 Lisensi {$license->license_id} ({$license->license_type}) {$license->name} akan *expired* pada {$expiredDate->format('d-m-Y')}.",
            ], ['read' => false]);
        } else {
            $validated['status'] = 'active';
        }
    }

    $license->update($validated);

    return redirect()->route('licenses.index')->with('success', 'Data berhasil diperbarui.');
}

    public function destroy(License $license) 
    {
        if (auth()->user()->hasRole('Pemilik Lisensi')) {
        abort(403);
        }
    
        if ($license) {
            $license->delete();
            return response()->json(['status' => 'success', 'message' => 'License deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }

}