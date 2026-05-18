<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadAdministrativa extends Model
{
    use SoftDeletes;
    protected $table = 'unidades_administrativas';
    protected $fillable = ['nombre', 'id_nivel', 'parent_id', 'is_active'];

    public function nivel()
    {
        return $this->belongsTo(NivelJerarquico::class, 'id_nivel');
    }

    public function parent()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnidadAdministrativa::class, 'parent_id');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_unidad_administrativa');
    }
}
