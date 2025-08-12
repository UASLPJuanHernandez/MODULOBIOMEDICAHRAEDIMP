<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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

    // Relaciones para el sistema de inventario
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function ordenesServicio(): HasMany
    {
        return $this->hasMany(OrdenServicio::class);
    }

    public function mobiliariosBloqueados(): HasMany
    {
        return $this->hasMany(Mobiliario::class, 'locked_by');
    }

    // Métodos auxiliares para roles
    public function esAdministrador(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function esPersonalApoyo(): bool
    {
        return $this->hasRole('Personal de Apoyo');
    }

    public function esJefeArea(): bool
    {
        return $this->hasRole('Jefe de Área');
    }

    public function puedeEditarMobiliario(): bool
    {
        return $this->esAdministrador() || $this->esPersonalApoyo();
    }

    public function puedeGenerarReportes(): bool
    {
        return $this->esAdministrador() || $this->esPersonalApoyo();
    }
}
