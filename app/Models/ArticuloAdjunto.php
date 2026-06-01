<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloAdjunto extends Model
{
    use HasFactory;

    protected $table = 'articulo_adjuntos';

    protected $fillable = [
        'id_articulo',
        'nombre_original',
        'ruta_archivo',
        'tipo_mime',
        'tamano',
        'descripcion',
        'descargas',
        'subido_por',
    ];

    public function articulo()
    {
        return $this->belongsTo(ArticuloConocimiento::class, 'id_articulo', 'id_articulo');
    }

    public function subidor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
