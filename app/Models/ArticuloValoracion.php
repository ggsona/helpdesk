<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloValoracion extends Model
{
    use HasFactory;

    protected $table = 'articulo_valoraciones';

    protected $fillable = [
        'id_articulo',
        'id_usuario',
        'es_util',
        'comentario',
    ];

    protected $casts = [
        'es_util' => 'boolean',
    ];

    public function articulo()
    {
        return $this->belongsTo(ArticuloConocimiento::class, 'id_articulo', 'id_articulo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
