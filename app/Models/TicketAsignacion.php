<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAsignacion extends Model
{
    protected $table = 'ticket_asignaciones';
    protected $fillable = ['id_ticket', 'id_usuario_tecnico'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_usuario_tecnico', 'id');
    }
}
