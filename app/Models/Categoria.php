<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Categoria extends Model {
    use LogsActivity;

    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre_categoria', 'estado', 'created_by', 'updated_by'];

    /**
     * Get the user that created the Categoria.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the Categoria.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_categoria', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
