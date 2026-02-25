<?php

namespace App\Models;

use App\Models\Core\Role;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'role',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        if ($this->role === $slug) {
            return true;
        }

        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if (in_array($this->role, ['super_admin', 'admin'], true)) {
            return true;
        }

        if ($this->roles()->whereIn('slug', ['super_admin', 'admin'])->exists()) {
            return true;
        }

        if ($this->roles()->doesntExist() && $this->role !== 'owner') {
            return true; // legacy compatibility for pre-RBAC users
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $permission))
            ->exists();
    }
}
