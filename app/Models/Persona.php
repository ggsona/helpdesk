<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $fillable = [
        'nombre', 
        'segundo_nombre',
        'apellido', 
        'segundo_apellido',
        'cedula', 
        'telefono', 
        'id_unidad_administrativa'
    ];

    public function unidadAdministrativa()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'id_unidad_administrativa')->withTrashed();
    }
}
