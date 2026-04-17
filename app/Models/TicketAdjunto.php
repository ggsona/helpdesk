<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAdjunto extends Model
{
    protected $table = 'ticket_adjuntos';

    protected $fillable = [
        'id_ticket',
        'ruta_archivo',
        'nombre_original',
        'tipo_mimo',
        'tamano'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }
}