<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ArticuloConocimiento extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'articulos_conocimiento';
    protected $primaryKey = 'id_articulo';

    protected $fillable = [
        'origen',
        'id_solucion',
        'titulo',
        'slug',
        'extracto',
        'contenido',
        'id_categoria',
        'id_autor',
        'id_editor',
        'estado',
        'es_destacado',
        'es_interno',
        'vistas',
        'veces_usado',
        'fecha_publicacion',
    ];

    protected $casts = [
        'es_destacado' => 'boolean',
        'es_interno' => 'boolean',
        'fecha_publicacion' => 'datetime',
    ];

    public function solucion()
    {
        return $this->belongsTo(SolucionTecnica::class, 'id_solucion', 'id_solucion');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'id_autor');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'id_editor');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'articulo_tag', 'id_articulo', 'id_tag');
    }

    public function adjuntos()
    {
        return $this->hasMany(ArticuloAdjunto::class, 'id_articulo', 'id_articulo');
    }

    public function valoraciones()
    {
        return $this->hasMany(ArticuloValoracion::class, 'id_articulo', 'id_articulo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'id_categoria'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
