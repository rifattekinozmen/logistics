<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomPermission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(CustomRole::class, 'custom_permission_role', 'permission_id', 'role_id')
            ->withTimestamps();
    }
}
