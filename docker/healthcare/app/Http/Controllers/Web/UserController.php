<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private const ROLES = [
        'admin'         => 'Quản trị viên',
        'physician'     => 'Bác sĩ',
        'nurse'         => 'Điều dưỡng',
        'pharmacist'    => 'Dược sĩ',
        'lab_tech'      => 'Kỹ thuật viên XN',
        'radiologist'   => 'Bác sĩ chẩn đoán hình ảnh',
        'receptionist'  => 'Lễ tân',
        'billing'       => 'Kế toán',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->orderBy('username');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('employee_id', 'like', "%$search%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'), ['roles' => self::ROLES]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'username'    => 'required|string|max:50|unique:users',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => ['required', Password::min(8)->mixedCase()->numbers()],
            'role'        => 'required|in:' . implode(',', array_keys(self::ROLES)),
            'employee_id' => 'nullable|string|max:50|unique:users',
            'provider_npi'=> 'nullable|string|max:10',
            'status'      => 'required|in:active,suspended,inactive',
        ]);

        User::create([
            'uuid'        => Str::uuid(),
            'username'    => $validated['username'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'employee_id' => $validated['employee_id'] ?? null,
            'provider_npi'=> $validated['provider_npi'] ?? null,
            'status'      => $validated['status'],
            'mfa_enabled' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo người dùng thành công.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'), ['roles' => self::ROLES]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'username'    => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'       => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'        => 'required|in:' . implode(',', array_keys(self::ROLES)),
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $user->id,
            'provider_npi'=> 'nullable|string|max:10',
            'status'      => 'required|in:active,suspended,inactive',
            'password'    => ['nullable', Password::min(8)->mixedCase()->numbers()],
        ]);

        $data = [
            'username'    => $validated['username'],
            'email'       => $validated['email'],
            'role'        => $validated['role'],
            'employee_id' => $validated['employee_id'] ?? null,
            'provider_npi'=> $validated['provider_npi'] ?? null,
            'status'      => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
            $data['password_changed_at'] = now();
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính mình.');
        }

        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã vô hiệu hóa người dùng.');
    }
}
