<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Religion;
use App\Models\License;
use App\Models\Employee;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use Illuminate\Support\Carbon;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employees.index');
    }
    public function datatable(Request $request)
    {
        // if ($request->ajax()) {
            $employees = $this->getJoinedEmployees();

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('birth_date', fn($row) => $row->birth_date ? Carbon::parse($row->birth_date)->format('d/m/Y') : '-')
                ->addColumn('start_date', fn($row) => $row->start_date ? Carbon::parse($row->start_date)->format('d/m/Y') : '-')
                ->addColumn('marital_status', fn($row) => $this->readableMaritalStatus($row->marital_status))
                ->addColumn('gender', fn($row) => $this->readableGender($row->gender))
                ->addColumn('position', function ($row) {
                    $positions = $row->position;

                    // Decode 1x
                    $decoded = json_decode($positions, true);

                    // Kalau hasilnya masih string (double encode), decode lagi
                    if (is_string($decoded)) {
                        $decoded = json_decode($decoded, true);
                    }

                    return is_array($decoded) ? implode(', ', $decoded) : '-';
                })
                ->addColumn('department', function ($row) {
                    $departments = json_decode($row->department, true);
                    if (is_string($departments)) {
                        $departments = json_decode($departments, true);
                    }
                    return is_array($departments) ? implode(', ', $departments) : '-';
                })
                ->addColumn('unit', function ($row) {
                    $units = json_decode($row->unit, true);
                    if (is_string($units)) {
                        $units = json_decode($units, true);
                    }
                    return is_array($units) ? implode(', ', $units) : '-';
                })
                ->editColumn('birth_place', fn($row) => Str::title($row->birth_place))
                ->editColumn('address', fn($row) => Str::title($row->address))
                ->editColumn('nickname', fn($row) => Str::title($row->nickname))
                ->editColumn('fullname', function ($row) {
                    $url = route('employees.show', $row->employee_id);
                    $name = Str::title($row->fullname ?? '-');
                    return '<a href="'.$url.'">'.e($name).'</a>';
                })
                ->editColumn('contract_letter_file', function ($row) {
                    if ($row->contract_letter_file) {
                        // Ambil URL file lewat Storage::url
                        $url = Storage::url($row->contract_letter_file);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->editColumn('instructure_certificate', function ($row) {
                    if ($row->instructure_certificate) {
                        // Ambil URL file lewat Storage::url
                        $url = Storage::url($row->instructure_certificate);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->addColumn('action', function ($employee) {
                    $buttons = '';
                    if (auth()->user()->can('karyawan.ubah')) {
                        $buttons .= '<a href="' . route('employees.edit', $employee->employee_id) . '" class="btn btn-icon btn-sm btn-warning me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('karyawan.lihat')) {
                        $buttons .= '<a href="' . route('employees.show', $employee->employee_id) . '" class="btn btn-icon btn-sm btn-info me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('karyawan.hapus')) {
                        $buttons .= '<button data-id="' . $employee->employee_id . '" class="btn btn-icon btn-sm btn-danger delete-employee" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })

                ->rawColumns(['fullname', 'action', 'contract_letter_file', 'instructure_certificate'])
                ->make(true);
    }

    private function getJoinedEmployees()
    {
        $auth = auth()->user();
        $activeLicenseId = session('active_license_id');


        $query = \DB::table('employees')
        ->join('users', 'employees.user_id', '=', 'users.id')
        ->join('employee_license', 'employees.id', '=', 'employee_license.employee_id')
        ->join('licenses', 'employee_license.license_id', '=', 'licenses.id')
        ->leftJoin('religions', 'employees.religion_id', '=', 'religions.id')
        ->leftJoin('provinces', 'employees.province_id', '=', 'provinces.id')
        ->leftJoin('cities', 'employees.city_id', '=', 'cities.id')
        ->leftJoin('districts', 'employees.district_id', '=', 'districts.id')
        ->leftJoin('sub_districts', 'employees.sub_district_id', '=', 'sub_districts.id')
        ->leftJoin('postal_codes', 'employees.postal_code_id', '=', 'postal_codes.id')
        ->select(
            'employees.id as employee_id',
            'employees.nik',
            'licenses.license_type as license_type',
            'licenses.name as license_name',
            'employees.fullname',
            'employees.nickname',
            'employees.gender',
            'employees.birth_place',
            'employees.birth_date',
            'employees.marital_status',
            'religions.name as religion_name',
            'employees.identity_number',
            'users.email as users_email',
            'employees.address',
            'provinces.name as province_name',
            'cities.name as city_name',
            'districts.name as district_name',
            'sub_districts.name as sub_district_name',
            'postal_codes.postal_code',
            'employees.phone',
            'employees.position',
            'employees.department',
            'employees.unit',
            'employees.employment_status',
            'employees.start_date',
            'employees.basic_salary',
            'employees.allowance',
            'employees.deduction',
            'employees.bonus',
            'employees.thr',
            'employees.contract_letter_file',
            'employees.instructure_certificate',
            'employees.expired_date_certificate',
        );

        // Jika user punya role Pemilik Lisensi atau Akuntan
        if ($auth->hasRole(['Pemilik Lisensi', 'Akuntan', 'Karyawan'])) {

            // Ambil license IDs sesuai role
            $licenseIds = $auth->hasRole('Pemilik Lisensi')
                ? $auth->licenses()->pluck('id')
                : ($auth->employee?->licenses()->pluck('id') ?? collect());

            // Filter query berdasarkan license IDs
            if ($licenseIds->isNotEmpty()) {
                $query->whereIn('licenses.id', $licenseIds);
            } else {
                // Kalau akuntan/pemilik lisensi tidak punya license sama sekali,
                // bikin query kosong supaya tidak menampilkan data yang salah
                $query->whereNull('licenses.id');
            }
        }

        if ($activeLicenseId) {
            $query->where('licenses.id', $activeLicenseId);
        }


        return $query;
    }


    private function readableMaritalStatus($value)
    {
        return match ((int) $value) {
            1 => 'Belum Menikah',
            2 => 'Sudah Menikah',
            3 => 'Cerai Hidup',
            4 => 'Cerai Mati',
            default => '-',
        };
    }

    private function readableGender($value)
    {
        return match ((int) $value) {
            1 => 'Laki - Laki',
            2 => 'Perempuan',
            default => '-',
        };
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->hasRole('Super-Admin')) {
    $licenses = License::all();

} elseif ($user->hasRole('Pemilik Lisensi')) {
    // Lisensi langsung terhubung ke user
    $licenses = $user->licenses ?? collect();

} elseif ($user->hasRole('Akuntan')) {
    // Lisensi diambil dari relasi employee
    $licenses = $user->employee?->licenses ?? collect();

} else {
    $licenses = collect(); // role lain tidak punya lisensi
}


        $religions = Religion::all();
        $provinces = Province::all();
        return view('employees.create', compact('religions', 'licenses', 'provinces'));
    }

    public function store(Request $request)
{
    // Siapkan aturan validasi dasar
    $rules = [
        'licenses' => 'required|array',
        'licenses.*' => 'exists:licenses,id',
        'nik' => 'required|unique:employees,nik',
        'fullname' => 'required',
        'nickname' => 'required',
        'gender' => 'required|in:1,2',
        'birth_place' => 'required',
        'birth_date' => 'required|date_format:Y-m-d',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|string|exists:roles,name',
        'marital_status' => 'required|in:1,2,3,4',
        'religion_id' => 'required|exists:religions,id',
        'identity_number' => 'required|digits:16',
        'address' => 'required',
        'province_id' => 'required|exists:provinces,id',
        'city_id' => 'required|exists:cities,id',
        'district_id' => 'required|exists:districts,id',
        'sub_district_id' => 'required|exists:sub_districts,id',
        'postal_code_id' => 'required|exists:postal_codes,id',
        'phone' => 'required',
        'position' => 'nullable|array',
        'position.*' => 'in:Komisaris,Direktur,Manager,Supervisor,Staff',
        'department' => 'nullable|array',
        'department.*' => 'in:Networking,Produksi,Keuangan,HR,Marketing',
        'unit' => 'nullable|array',
        'unit.*' => 'in:Lisensi,Event,Training,Trainer Pusat,Trainer Wilayah,Pengadaan,Kursus,Instruktur',
        'employment_status' => 'required|in:Tetap,Kontrak,Harian,Honorer',
        'start_date' => 'required|date_format:Y-m-d',
        'basic_salary' => 'required|numeric|min:0',
        'allowance' => 'required|numeric|min:0',
        'deduction' => 'required|numeric|min:0',
        'bonus' => 'required|numeric|min:0',
        'thr' => 'required|numeric|min:0',
        'contract_letter_file' => 'required|file|mimes:pdf|max:2048',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'identity_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];

    // Validasi bersyarat: kalau ada "Instruktur" → sertifikat wajib
    if (in_array('Instruktur', $request->input('unit', []))) {
        $rules['instructure_certificate'] = 'nullable|file|mimes:pdf|max:2048';
    } else {
        $rules['instructure_certificate'] = 'nullable|file|mimes:pdf|max:2048';
    }

     // Validasi bersyarat: kalau ada "Instruktur" → sertifikat wajib
    if (in_array('Instruktur', $request->input('unit', []))) {
        $rules['expired_date_certificate'] = 'nullable|date_format:Y-m-d';
    } else {
        $rules['expired_date_certificate'] = 'nullable|date_format:Y-m-d';
    }

    // Lakukan validasi
    $validated = $request->validate($rules);

    $primaryLicenseId = $validated['licenses'][0] ?? null;

if (!$primaryLicenseId) {
    return back()->withErrors(['licenses' => 'Pilih minimal satu lisensi.']);
}

// Jika nik tidak diset (misalnya diisi otomatis via AJAX gagal), buatkan
if (empty($validated['nik']) || $validated['nik'] === 'AUTO') {
    $validated['nik'] = $this->generateNik($primaryLicenseId);
}


    // Simpan file foto jika ada
    if ($request->hasFile('photo')) {
        $validated['photo'] = $request->file('photo')
            ->storeAs('photos', Str::uuid() . '.' . $request->file('photo')->getClientOriginalExtension(), 'public');
    }

    if ($request->hasFile('identity_photo')) {
        $validated['identity_photo'] = $request->file('identity_photo')
            ->storeAs('photos', Str::uuid() . '.' . $request->file('identity_photo')->getClientOriginalExtension(), 'public');
    }

    // Simpan file kontrak
    $validated['contract_letter_file'] = $request->file('contract_letter_file')
        ->storeAs('contracts', Str::uuid() . '_' . $request->file('contract_letter_file')->getClientOriginalName(), 'public');

    // Simpan sertifikat instruktur jika ada
    if ($request->hasFile('instructure_certificate')) {
        $validated['instructure_certificate'] = $request->file('instructure_certificate')
            ->storeAs('certificates', Str::uuid() . '_' . $request->file('instructure_certificate')->getClientOriginalName(), 'public');
    }

    // Buat user
    $user = User::create([
        'name' => $validated['fullname'],
        'email' => $validated['email'],
        'password' => bcrypt('password123'), // Ganti jika mau password generator
    ]);
    $user->assignRole($validated['role']);

    // Masukkan user_id ke validated untuk Employee
    $validated['user_id'] = $user->id;

    // Simpan employee (kecuali licenses dan email)
    $employee = Employee::create(
        collect($validated)->except(['licenses', 'email'])->toArray()
    );

    // Relasi licenses (pivot table)
    $employee->licenses()->sync($validated['licenses']);

    return redirect()->route('employees.index')->with('success', 'Data berhasil ditambahkan.');
}

public function generateNikAjax($licenseId)
{
    $nik = $this->generateNik($licenseId); // pakai helper internal
    return response()->json(['nik' => $nik]);
}

private function generateNik($licenseId)
{
    $license = License::findOrFail($licenseId);

    // Asumsikan id lisensi kamu itu angka seperti: 3379
    $prefix = str_pad($license->license_id, 4, '0', STR_PAD_LEFT); // jadi 4 digit

    $prefix .= '01'; // tambahkan 01 setelah id lisensi

    // Ambil NIK terakhir yang sesuai prefix ini
    $lastNik = Employee::where('nik', 'like', $prefix . '%')
        ->orderByDesc('nik')
        ->first();

    $nextNumber = 1;
    if ($lastNik) {
        $lastNumber = (int)substr($lastNik->nik, strlen($prefix)); // ambil 4 digit akhir
        $nextNumber = $lastNumber + 1;
    }

    return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // ex: 3379010001
}

        public function show(Employee $employee)
    {

        $employee->load(['religion', 'user.licenses', 'province', 'city', 'district', 'subDistrict', 'postalCode']);
        return view('employees.show', compact('employee'));
    }

    public function showProfile($id)
    {
    $employee = Employee::with(['user.licenses', 'religion'])->findOrFail($id);
    return view('employees.tab.profile', compact('employee'));
    }

    public function showTab($id)
{
    $employee = Employee::with('educations')->findOrFail($id);
    return view('employees.tab.educations', compact('employee'));
}

    public function showWorks($id)
{
    $employee = Employee::with('workers')->findOrFail($id);
    return view('employees.tab.workers', compact('employee'));
}

    public function showFams($id)
{
    $employee = Employee::with('families')->findOrFail($id);
    return view('employees.tab.families', compact('employee'));
}
    
    public function edit(Employee $employee)
{
    $religions = Religion::all();
    $provinces = Province::all();
    $roles = Role::all();

    $user = auth()->user();
        if ($user->hasRole('Super-Admin')) {
    $licenses = License::all();

} elseif ($user->hasRole('Pemilik Lisensi')) {
    // Lisensi langsung terhubung ke user
    $licenses = $user->licenses ?? collect();

} elseif ($user->hasRole('Akuntan')) {
    // Lisensi diambil dari relasi employee
    $licenses = $user->employee?->licenses ?? collect();

} else {
    $licenses = collect(); // role lain tidak punya lisensi
}

    // Ambil salah satu license untuk wilayah
    $license = $employee;

    $cities = collect();
    $districts = collect();
    $subDistricts = collect();
    $postalCodes = collect();

    if ($license) {
        $cities = City::where('province_id', $license->province_id)->get();
        $districts = District::where('city_id', $license->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $license->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $license->sub_district_id)->get();
    }

    return view('employees.edit', compact(
        'employee', 'roles', 'religions', 'provinces', 'licenses', 'license',
        'cities', 'districts', 'subDistricts', 'postalCodes'
    ));
}

    public function update(Request $request, Employee $employee)
{
    $rules = [
    'licenses' => 'required|array',
    'licenses.*' => 'exists:licenses,id',
    'fullname' => 'required',
    'nik' => [
        'required',
        Rule::unique('employees', 'nik')->ignore($employee->id),
    ],
    'nickname' => 'required',
    'gender' => 'required|in:1,2',
    'birth_place' => 'required',
    'birth_date' => ['required', 'date_format:Y-m-d'],
    'email' => 'required|email|unique:users,email,' . $employee->user_id,
    'role' => 'required|string|exists:roles,name',
    'marital_status' => 'required|in:1,2,3,4',
    'religion_id' => 'required|exists:religions,id',
    'identity_number' => 'required|digits:16',
    'address' => 'required',
    'province_id' => 'required|exists:provinces,id',
    'city_id' => 'required|exists:cities,id',
    'district_id' => 'required|exists:districts,id',
    'sub_district_id' => 'required|exists:sub_districts,id',
    'postal_code_id' => 'required|exists:postal_codes,id',
    'phone' => 'required',
    'position' => 'nullable|array',
    'position.*' => 'in:Komisaris,Direktur,Manager,Supervisor,Staff',
    'department' => 'nullable|array',
    'department.*' => 'in:Networking,Produksi,Keuangan,HR,Marketing',
    'unit' => 'nullable|array',
    'unit.*' => 'in:Lisensi,Event,Training,Trainer Pusat,Trainer Wilayah,Pengadaan,Kursus,Instruktur',
    'employment_status' => 'required|in:Tetap,Kontrak,Harian,Honorer',
    'start_date' => ['required', 'date_format:Y-m-d'],
    'basic_salary' => ['required', 'numeric', 'min:0'],
    'allowance' => ['required', 'numeric', 'min:0'],
    'deduction' => ['required', 'numeric', 'min:0'],
    'bonus' => ['required', 'numeric', 'min:0'],
    'thr' => ['required', 'numeric', 'min:0'],
    'contract_letter_file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
    'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    'identity_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    'expired_date_certificate' => ['nullable', 'date_format:Y-m-d'],

];

// Upload sertifikat instruktur jika ada
if ($request->hasFile('instructure_certificate')) {
    if ($employee->instructure_certificate) {
        Storage::delete($employee->instructure_certificate);
    }

    $path = $request->file('instructure_certificate')
        ->storeAs('certificates', Str::uuid() . '_' . $request->file('instructure_certificate')->getClientOriginalName(), 'public');

    $employee->instructure_certificate = $path;
}


    $validator = Validator::make($request->all(), $rules);
    $validated = $validator->validate();

    DB::beginTransaction();

    try {
            $employee->update(collect($validated)->except(['licenses'])->toArray());

            if ($employee->user) {
                $employee->user->update([
                    'name' => $validated['fullname'],
                    'email' => $validated['email'],
                ]);

                $employee->user->syncRoles([$validated['role']]);

                $employee->licenses()->sync($validated['licenses']);
            }

        // Upload contract file jika ada
        if ($request->hasFile('contract_letter_file')) {
            if ($employee->contract_letter_file) {
            Storage::delete($employee->contract_letter_file);
        }

            $employee->contract_letter_file = $request->file('contract_letter_file')->store('contracts', 'public');
}

if ($request->hasFile('photo')) {
    if ($employee->photo) {
        Storage::delete($employee->photo);
    }

    $employee->photo = $request->file('photo')->store('photos', 'public');
}

if ($request->hasFile('identity_photo')) {
    if ($employee->identity_photo) {
        Storage::delete($employee->identity_photo);
    }

    $employee->identity_photo = $request->file('identity_photo')->store('photos', 'public');
}
        // Update employee
        $employee->update([
            'fullname' => $validated['fullname'],
            'nickname' => $validated['nickname'],
            'nik' => $validated['nik'],
            'gender' => $validated['gender'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'marital_status' => $validated['marital_status'],
            'religion_id' => $validated['religion_id'],
            'identity_number' => $validated['identity_number'],
            'address' => $validated['address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
            'phone' => $validated['phone'],
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'department' => isset($validated['department']) ? json_encode($validated['department']) : null,
            'unit' => isset($validated['unit']) ? json_encode($validated['unit']) : null,
            'employment_status' => $validated['employment_status'],
            'start_date' => $validated['start_date'],
            'basic_salary' => $validated['basic_salary'],
            'allowance' => $validated['allowance'],
            'deduction' => $validated['deduction'],
            'bonus' => $validated['bonus'],
            'thr' => $validated['thr'],
            'expired_date_certificate' => $validated['expired_date_certificate'] ?? null,
   
        ]);

        DB::commit();

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
}

    public function destroy(Employee $employee)
    {
            if ($employee) {
            $employee->delete();
            return response()->json(['status' => 'success', 'message' => 'Employee deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }
}
