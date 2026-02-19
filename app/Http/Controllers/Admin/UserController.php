<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\CustomRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'search', 'role_id', 'user_type']);
        $users = User::query()
            ->with(['roles'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            }))
            ->when($filters['role_id'] ?? null, fn ($q, $roleId) => $q->whereHas('roles', fn ($query) => $query->where('custom_roles.id', $roleId)))
            ->when($filters['user_type'] ?? null, function ($q, $userType) {
                if ($userType === 'customer') {
                    $q->whereHas('roles', fn ($query) => $query->whereIn('name', ['customer', 'customer_user', 'customer_viewer']));
                } elseif ($userType === 'system') {
                    $q->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['customer', 'customer_user', 'customer_viewer']));
                }
            })
            ->orderBy('name')
            ->paginate(25);

        $roles = CustomRole::orderBy('name')->get();
        $customerRoles = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->get();
        $systemRoles = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->get();

        return view('admin.users.index', compact('users', 'roles', 'customerRoles', 'systemRoles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = CustomRole::orderBy('name')->get();
        $customerRoles = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->orderBy('name')->get();
        $systemRoles = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'customerRoles', 'systemRoles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Rolleri ata
        if ($request->has('roles')) {
            // Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez kontrolü
            $customerRoleIds = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();
            $systemRoleIds = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();

            $selectedRoles = $request->roles;
            $hasCustomerRole = ! empty(array_intersect($selectedRoles, $customerRoleIds));
            $hasSystemRole = ! empty(array_intersect($selectedRoles, $systemRoleIds));

            if ($hasCustomerRole && $hasSystemRole) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['roles' => 'Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez.']);
            }

            $user->roles()->sync($selectedRoles);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['roles.permissions']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $user->load(['roles']);
        $roles = CustomRole::orderBy('name')->get();
        $customerRoles = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->orderBy('name')->get();
        $systemRoles = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'customerRoles', 'systemRoles'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Şifre güncelleniyorsa hash'le
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Rolleri güncelle
        if ($request->has('roles')) {
            // Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez kontrolü
            $customerRoleIds = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();
            $systemRoleIds = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();

            $selectedRoles = $request->roles;
            $hasCustomerRole = ! empty(array_intersect($selectedRoles, $customerRoleIds));
            $hasSystemRole = ! empty(array_intersect($selectedRoles, $systemRoleIds));

            if ($hasCustomerRole && $hasSystemRole) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['roles' => 'Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez.']);
            }

            $user->roles()->sync($selectedRoles);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }

    /**
     * Show form for assigning roles and permissions to user.
     */
    public function editRoles(User $user): View
    {
        $user->load(['roles.permissions']);

        // Kullanıcının müşteri portalı rolü var mı kontrol et
        $hasCustomerRole = $user->roles->contains(function ($role) {
            return in_array($role->name, ['customer', 'customer_user', 'customer_viewer'], true);
        });

        // Müşteri kullanıcısıysa sadece müşteri portalı rolleri göster
        if ($hasCustomerRole) {
            $roles = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])
                ->with('permissions')
                ->orderBy('name')
                ->get();
        } else {
            // Sistem kullanıcısıysa sadece sistem rolleri göster
            $roles = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])
                ->with('permissions')
                ->orderBy('name')
                ->get();
        }

        return view('admin.users.edit-roles', compact('user', 'roles', 'hasCustomerRole'));
    }

    /**
     * Update user roles.
     */
    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:custom_roles,id'],
        ]);

        $selectedRoles = $request->roles ?? [];

        // Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez kontrolü
        $customerRoleIds = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();
        $systemRoleIds = CustomRole::whereNotIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('id')->toArray();

        $hasCustomerRole = ! empty(array_intersect($selectedRoles, $customerRoleIds));
        $hasSystemRole = ! empty(array_intersect($selectedRoles, $systemRoleIds));

        if ($hasCustomerRole && $hasSystemRole) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['roles' => 'Müşteri portalı rolleri ile sistem rolleri birlikte seçilemez.']);
        }

        $user->roles()->sync($selectedRoles);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Kullanıcı rolleri başarıyla güncellendi.');
    }
}
