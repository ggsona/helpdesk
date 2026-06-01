<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Modelo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';

    protected $fillable = ['nombre_modelo', 'id_marca'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_modelo', 'id_marca'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
