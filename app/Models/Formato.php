<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formato extends Model
{
    protected $fillable = [
        'nombre',
        'archivo_original',
        'archivo_path',
        'contenido_texto',
        'campos_json',
    ];

    protected $casts = [
        'campos_json' => 'array',
    ];

    public function registros(): HasMany
    {
        return $this->hasMany(Registro::class);
    }

    public function esDocx(): bool
    {
        return str_ends_with(strtolower($this->archivo_original), '.docx');
    }

    public function esPdf(): bool
    {
        return str_ends_with(strtolower($this->archivo_original), '.pdf');
    }
}
