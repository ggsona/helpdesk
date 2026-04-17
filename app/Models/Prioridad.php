<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prioridad extends Model
{
    protected $table = 'prioridades';
    protected $primaryKey = 'id_prioridad';
    protected $fillable = ['nombre_prioridad'];
}