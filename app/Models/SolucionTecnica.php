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
        'procedimiento_detallado'
    ];

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }
}
