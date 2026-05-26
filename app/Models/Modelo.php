<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Modelo extends Model
{
    use HasFactory, Auditable;

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
}
