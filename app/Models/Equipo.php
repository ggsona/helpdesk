<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Equipo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'equipos';

    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'nombre',
        'numero_bien',
        'id_marca',
        'id_modelo',
        'ip_address',
        'mac_address',
        'ram',
        'procesador',
        'disco_duro',
        'id_tipo_equipo',
        'id_usuario_asignado',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con el Tipo de Equipo.
     */
    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    /**
     * Relación con el Usuario Asignado.
     */
    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_usuario_asignado', 'id');
    }

    /**
     * Relación con la Marca.
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    /**
     * Relación con el Modelo.
     */
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre', 'numero_bien', 'id_marca', 'id_modelo', 
                'ip_address', 'mac_address', 'ram', 'procesador', 
                'disco_duro', 'id_tipo_equipo', 'id_usuario_asignado', 'estado'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
