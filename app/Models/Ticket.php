<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    protected $fillable = ['id_usuario', 'id_tipo_equipo', 'id_prioridad', 'id_categoria', 'descripcion_problema', 'estatus', 'id_usuario_tecnico', 'fecha_cierre'];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

    public function tecnico() { return $this->belongsTo(User::class, 'id_usuario_tecnico'); }

    public function prioridad() { return $this->belongsTo(Prioridad::class, 'id_prioridad'); }

    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria'); }

    public function tipoEquipo() { return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); }

}

