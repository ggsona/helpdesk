<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $fillable = ['nombre', 'apellido', 'telefono', 'id_oficina'];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'id_oficina');
    }
}
