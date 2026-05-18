<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelJerarquico extends Model
{
    protected $table = 'niveles_jerarquicos';
    protected $fillable = ['nombre', 'nivel', 'is_active'];

    public function unidades()
    {
        return $this->hasMany(UnidadAdministrativa::class, 'id_nivel');
    }
}
