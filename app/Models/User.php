<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'status',
        'password',
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

    /**
     * Assets this user is currently the custodian (Person in Charge) of.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'custodian_id');
    }

    /**
     * Full assignment history for this user as a custodian.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'custodian_id');
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    public function isAssetOfficer(): bool
    {
        return $this->role === 'asset_officer';
    }

    /**
     * Roles that are allowed to manage assets (create/edit/delete records).
     */
    public function canManageAssets(): bool
    {
        return in_array($this->role, ['administrator', 'asset_officer']);
    }

    /**
     * Roles that are allowed to manage user accounts.
     */
    public function canManageUsers(): bool
    {
        return $this->role === 'administrator';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
