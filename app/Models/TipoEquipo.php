<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model {

    protected $table = 'tipos_equipo';

    protected $primaryKey = 'id_tipo_equipo';

    protected $fillable = ['nombre_tipo_equipo'];

}
