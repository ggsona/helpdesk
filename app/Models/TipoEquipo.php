<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TipoEquipo extends Model {
    use LogsActivity;

    protected $table = 'tipos_equipo';

    protected $primaryKey = 'id_tipo_equipo';

    protected $fillable = ['nombre_tipo_equipo'];

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
