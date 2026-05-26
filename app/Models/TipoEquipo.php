<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class TipoEquipo extends Model {
    use Auditable;

    protected $table = 'tipos_equipo';

    protected $primaryKey = 'id_tipo_equipo';

    protected $fillable = ['nombre_tipo_equipo'];

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }
}
