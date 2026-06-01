<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'nombre',
        'slug',
        'color',
        'estado',
    ];

    public function articulos()
    {
        return $this->belongsToMany(ArticuloConocimiento::class, 'articulo_tag', 'id_tag', 'id_articulo');
    }
}
