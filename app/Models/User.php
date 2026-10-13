<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithAppAuthentication, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin_role',
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
            'profile_public' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && (in_array($this->admin_role, ['super_admin', 'moderator'], true) || $this->isConfiguredSuperAdmin());
    }

    public function isSuperAdmin(): bool
    {
        return $this->admin_role === 'super_admin' || $this->isConfiguredSuperAdmin();
    }

    private function isConfiguredSuperAdmin(): bool
    {
        return in_array(Str::lower((string) $this->email), config('motorclub.admin_emails', []), true);
    }

    /**
     * Get the user's initials
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            ShotGallery::where('user_id', $user->id)->each(fn (ShotGallery $gallery) => $gallery->delete());
            Vehicle::where('user_id', $user->id)->each(fn (Vehicle $vehicle) => $vehicle->delete());
        });
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function photographerProfile()
    {
        return $this->hasOne(PhotographerProfile::class);
    }

    public function shotGalleries()
    {
        return $this->hasMany(ShotGallery::class);
    }
}
