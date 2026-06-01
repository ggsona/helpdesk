<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Marca extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = ['nombre_marca', 'id_tipo_equipo'];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_marca', 'id_marca');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_marca', 'id_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
