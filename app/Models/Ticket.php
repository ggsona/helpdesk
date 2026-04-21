<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TicketAdjunto;

class Ticket extends Model {

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'asunto', // <-- AGREGA ESTO AQUÍ
        'id_usuario', 
        'id_tipo_equipo', 
        'id_prioridad', 
        'id_categoria', 
        'descripcion_problema', 
        'estatus', 
        'id_usuario_tecnico', 
        'fecha_cierre'
    ];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

    public function tecnico() { return $this->belongsTo(User::class, 'id_usuario_tecnico'); }

    public function prioridad() { return $this->belongsTo(Prioridad::class, 'id_prioridad'); }

    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria'); }

    public function tipoEquipo() { return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); }
    
    public function adjuntos() { 
        return $this->hasMany(TicketAdjunto::class, 'id_ticket'); 
    }

    // "Traductor" para que el estatus (1, 2, 3) se vea como texto en la tabla
    public function getEstadoTextoAttribute() {
        return match($this->estatus) {
            0 => 'Borrador',
            1 => 'Abierto',
            2 => 'En Proceso',
            3 => 'Resuelto',
            4 => 'Cerrado',
            default => 'Desconocido',
        };
    }

    public function comentarios()
    {
        // Cambiamos Comentario::class por TicketComentario::class
        return $this->hasMany(TicketComentario::class, 'id_ticket', 'id_ticket');
    }
}

