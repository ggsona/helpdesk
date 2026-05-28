<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TicketAdjunto;

class Ticket extends Model {

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'asunto',
        'id_usuario', 
        'id_tipo_equipo', 
        'id_equipo',
        'id_prioridad', 
        'id_categoria', 
        'descripcion_problema', 
        'estatus', 
        'estado_tecnico',
        'id_usuario_tecnico', 
        'fecha_cierre'
    ];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

    public function tecnico()
    {
        return $this->hasOneThrough(
            User::class,
            TicketAsignacion::class,
            'id_ticket', // Llave foránea en ticket_asignaciones
            'id',        // Llave foránea en users
            'id_ticket', // Llave local en tickets
            'id_usuario_tecnico' // Llave local en ticket_asignaciones
        );
    }

    public function prioridad() 
    { 
        return $this->belongsTo(Prioridad::class, 'id_prioridad'); 
    }

    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria'); }

    public function tipoEquipo() { return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); }
    
    public function equipo() { return $this->belongsTo(Equipo::class, 'id_equipo'); }
    
    public function adjuntos() { 
        return $this->hasMany(TicketAdjunto::class, 'id_ticket'); 
    }

    public function solucion() {
        return $this->hasOne(SolucionTecnica::class, 'id_ticket');
    }

    public function asignacion(): HasOne
    {
        // El segundo parámetro es la llave foránea en 'ticket_asignaciones'
        // El tercer parámetro es la llave local en 'tickets'
        return $this->hasOne(TicketAsignacion::class, 'id_ticket', 'id_ticket'); 
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

