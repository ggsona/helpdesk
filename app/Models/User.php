<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importante añadir esto
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_persona', // Asegúrate de que este campo esté en tu tabla 'users'
        'is_approved',
    ];

    /**
     * Relación con la información personal del usuario.
     */
    public function persona(): BelongsTo
    {
        // 1er parámetro: Modelo relacionado
        // 2do parámetro: Llave foránea en la tabla 'users'
        // 3er parámetro: Llave primaria en la tabla 'personas'
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Relación con los equipos asignados al usuario.
     */
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_usuario_asignado', 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(TicketAsignacion::class, 'id_usuario_tecnico', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_approved', 'role'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}