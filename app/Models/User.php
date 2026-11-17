<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'bartender', 'cajero', 'bodeguero'];

    public const ROLE_LABELS = [
        'admin' => 'Administrador',
        'bartender' => 'Bartender',
        'cajero' => 'Cajero',
        'bodeguero' => 'Bodeguero',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * URL completa del avatar de perfil con control de caché.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar).'?v='.($this->updated_at ? $this->updated_at->timestamp : time());
        }

        return null;
    }
}
