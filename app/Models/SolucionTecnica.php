<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolucionTecnica extends Model
{
    use HasFactory;

    protected $table = 'soluciones_tecnicas';
    protected $primaryKey = 'id_solucion';
    protected $fillable = [
        'id_ticket', 
        'id_usuario_tecnico', 
        'resumen_usuario', 
        'procedimiento_detallado',
        'diagnostico',
        'causa_raiz',
        'acciones_preventivas',
        'tiempo_resolucion',
        'dificultad',
        'publicar_en_kb',
    ];

    protected $casts = [
        'publicar_en_kb' => 'boolean',
    ];

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function articulo()
    {
        return $this->hasOne(ArticuloConocimiento::class, 'id_solucion', 'id_solucion');
    }
}
