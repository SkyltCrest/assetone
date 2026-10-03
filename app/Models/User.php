<?php

namespace App\Models;

use App\Models\Concerns\HasPhotos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPhotos, Notifiable;

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
        'department',
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
     * Assets this user currently holds as custodian.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'custodian_id');
    }

    /**
     * This user's full assignment history.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'custodian_id');
    }

    /**
     * Up to two initials, shown where the user has no profile picture.
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = array_map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)), array_slice($words, 0, 2));

        return implode('', $letters) ?: 'A';
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
     * Can this user manage assets?
     */
    public function canManageAssets(): bool
    {
        return in_array($this->role, ['administrator', 'asset_officer']);
    }

    /**
     * Can this user manage accounts?
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
