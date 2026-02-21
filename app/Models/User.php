<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'status',
        'last_login_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(CustomRole::class, 'custom_role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Get all permissions for the user via roles.
     *
     * @param  bool  $customerPortalOnly  Sadece müşteri portalı rolleri için izinleri getir
     * @return array<int, string>
     */
    public function allPermissionCodes(bool $customerPortalOnly = false): array
    {
        // Pivot tablo üzerinden doğrudan sorgu yap
        $roleIds = DB::table('custom_role_user')
            ->where('user_id', $this->id)
            ->pluck('role_id')
            ->toArray();

        if (empty($roleIds)) {
            return [];
        }

        // Sadece aktif rolleri yükle
        $query = CustomRole::whereIn('id', $roleIds)
            ->whereNull('deleted_at');

        // Eğer sadece müşteri portalı rolleri isteniyorsa filtrele
        if ($customerPortalOnly) {
            $query->whereIn('name', ['customer', 'customer_user', 'customer_viewer']);
        }

        $roles = $query->get();

        $codes = [];

        foreach ($roles as $role) {
            // Her rol için aktif izinleri doğrudan pivot tablodan yükle
            $permissionIds = DB::table('custom_permission_role')
                ->where('role_id', $role->id)
                ->pluck('permission_id')
                ->toArray();

            if (empty($permissionIds)) {
                continue;
            }

            // Sadece aktif izinleri al
            $permissions = CustomPermission::whereIn('id', $permissionIds)
                ->whereNull('deleted_at')
                ->get();

            foreach ($permissions as $permission) {
                $codes[] = $permission->code;
            }
        }

        return array_values(array_unique($codes));
    }

    /**
     * Check if user has a specific permission code.
     *
     * @param  string  $permissionCode  İzin kodu
     * @param  bool  $customerPortalOnly  Sadece müşteri portalı rolleri için kontrol et
     */
    public function hasPermission(string $permissionCode, bool $customerPortalOnly = false): bool
    {
        // Admin rolü tüm izinlere sahip kabul edilir (sadece sistem rolleri için)
        if (! $customerPortalOnly && $this->hasRole('admin')) {
            return true;
        }

        return in_array($permissionCode, $this->allPermissionCodes($customerPortalOnly), true);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        // Pivot tablo üzerinden doğrudan sorgu yap
        $roleIds = DB::table('custom_role_user')
            ->where('user_id', $this->id)
            ->pluck('role_id')
            ->toArray();

        if (empty($roleIds)) {
            return false;
        }

        return CustomRole::whereIn('id', $roleIds)
            ->where('name', $roleName)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Get the employee record for the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the avatar URL.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->avatar
                ? Storage::disk('public')->url($this->avatar)
                : null,
        );
    }

    /**
     * Get the companies for the user.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies')
            ->withPivot('role', 'is_default')
            ->withTimestamps();
    }

    /**
     * Get the active company for the user.
     * MD: docs/06-company-settings-and-switch.md - Section 3.1
     */
    public function activeCompany(): ?Company
    {
        $companyId = session('active_company_id');

        if (! $companyId) {
            // user_companies tablosundan is_default = 1 olan firma bulunur
            $defaultCompany = $this->companies()->wherePivot('is_default', true)->first();

            // Bulunamazsa ilk firma seçilir
            $companyId = $defaultCompany?->id ?? $this->companies()->first()?->id;

            if ($companyId) {
                // Session'a active_company_id yazılır
                session(['active_company_id' => $companyId]);
            }
        }

        return $companyId ? Company::find($companyId) : null;
    }

    /**
     * Check if user has access to a company.
     */
    public function hasAccessToCompany(int $companyId): bool
    {
        return $this->companies()->where('companies.id', $companyId)->exists();
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
