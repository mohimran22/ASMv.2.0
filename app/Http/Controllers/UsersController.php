<?php
   
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    
    public function index(Request $request) 
    {
    
        if ($request->ajax()) {

            $auth = auth()->user();

            if ($auth->hasRole('Super-Admin')) {
                 $users = User::query();
            }  else if ($auth->hasAnyRole(['Pemilik Lisensi', 'Karyawan'])) {
                $users = User::where('license_id', $auth->license_id)
                 ->whereHas('roles', function ($q) {
                     $q->whereIn('name', ['Pemilik Lisensi', 'Karyawan']);
                 });
}
 else {
            // Default: hanya user itu sendiri
            $users = User::where('user_id', $auth->id);
        }

            return Datatables::of($users)

            ->addIndexColumn()

            ->addColumn('created_at', function($user) {
                return Carbon::parse($user->created_at)->format('d-m-Y');
            })

            ->addColumn('action', function($user) {
                $editUrl = route('users.edit', $user->id);

                return ' 
                    <a href="'.$editUrl.'" class="btn btn-success btn-sm" title= "Ubah">
                        <i class="ti ti-edit"></i>
                    </a>
                    <button data-id="'.$user->id.'" class="btn btn-danger btn-sm delete-user" title="Hapus">
                        <i class="ti ti-trash"></i>
                    </button>
                '; 
            })

            ->rawColumns(['action'])
            ->make(true);
        }
          
        return view('users.index');
    }

    public function create()
    {
        $roles = $this->allowedRoles();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|string|exists:roles,name',
        ]);

        $authUser = auth()->user();

        abort_unless(in_array($validated['role'], $this->allowedRoles(), true), 403);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()->route('users.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        abort_if(
            $user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin'),
            403
        );

        $roles = $this->allowedRoles();

        return view('users.edit', compact('user', 'roles'));
    }

public function update(Request $request, User $user)
{
    abort_if(
        $user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin'),
        403
    );

    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        'password' => 'nullable|min:6',
        'role'     => 'required|string|exists:roles,name',
    ]);

    $authUser = auth()->user();

    // Pemilik Lisensi: hanya boleh mengubah role, data akun diabaikan
    if (! $authUser->hasRole('Pemilik Lisensi')) {
        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
    }

    abort_unless(in_array($validated['role'], $this->allowedRoles(), true), 403);

    $user->syncRoles([$validated['role']]);

    return redirect()->route('users.index')->with('success', 'Data berhasil diperbarui.');
}

private function allowedRoles(): array
{
    if (auth()->user()->hasRole('Super-Admin')) {
        return Role::pluck('name')->all();
    }

    // Pemilik Lisensi: semua kecuali role tingkat tinggi
    return Role::whereNotIn('name', ['Super-Admin', 'Pemilik Lisensi'])
        ->pluck('name')->all();
}

    public function destroy(User $user) 
    {
        abort_if(
            $user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin'),
            403
        );

        if ($user) {
            $user->delete();
            return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }

}

