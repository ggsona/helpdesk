<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model {

    protected $table = 'comentarios';

    protected $primaryKey = 'id_comentario';

    protected $fillable = ['id_ticket', 'id_usuario', 'mensaje'];

    public function ticket() { return $this->belongsTo(Ticket::class, 'id_ticket'); }

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

}

