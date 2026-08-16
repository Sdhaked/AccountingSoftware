<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'role',
        'profile_picture',
        'mobile_number_prefix',
        'mobile_number',
        'address',
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
        ];
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role');
    }

    public function hasAnyPermission(array|string $permissions): bool
    {
        if (! $this->role) {
            return false;
        }

        $this->loadMissing('roleModel.permissions');
        $permissionSlugs = collect((array) $permissions);

        return $this->roleModel?->permissions
            ->contains(fn (Permission $permission) => $permissionSlugs->contains($permission->slug)) ?? false;
    }
}
