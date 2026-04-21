<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComentario extends Model
{
    use HasFactory;

    protected $table = 'ticket_comentarios'; // Opcional si sigue la convención
    protected $primaryKey = 'id_comentario';

    protected $fillable = [
        'id_ticket',
        'id_usuario',
        'mensaje',
        'es_interno',
    ];

    // Relación: Un comentario pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
