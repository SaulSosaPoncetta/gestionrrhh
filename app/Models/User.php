<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROL_SUPERADMIN = 'superadmin';
    public const ROL_ADMIN      = 'admin_rrhh';
    public const ROL_SUPERVISOR = 'supervisor';
    public const ROL_EMPLEADO   = 'empleado';

    public const ROLES = [
        self::ROL_SUPERADMIN => 'Superadministrador',
        self::ROL_ADMIN      => 'Administrador RRHH',
        self::ROL_SUPERVISOR => 'Supervisor',
        self::ROL_EMPLEADO   => 'Empleado',
    ];

    protected $fillable = [
        'empresa_id', 'name', 'email', 'password', 'rol', 'activo', 'ultimo_acceso',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function esSuperadmin(): bool { return $this->rol === self::ROL_SUPERADMIN; }
    public function esAdmin(): bool      { return $this->rol === self::ROL_ADMIN; }
    public function esSupervisor(): bool { return $this->rol === self::ROL_SUPERVISOR; }
    public function esEmpleado(): bool   { return $this->rol === self::ROL_EMPLEADO; }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function getRolNombreAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }
}
