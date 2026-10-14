<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'filename',
        'path',
        'category',
        'is_system',
        'system_key',
        'file_size',
        'dimensions',
        'mime_type',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * URL completa del recurso con control de caché.
     */
    public function getUrlAttribute(): string
    {
        $version = $this->updated_at ? $this->updated_at->timestamp : time();
        return asset($this->path) . '?v=' . $version;
    }

    /**
     * Formateo legible del tamaño de archivo.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '—';
        }

        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 1) . ' MB';
        }

        return round($this->file_size / 1024) . ' KB';
    }

    /**
     * Etiqueta amigable de categoría.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'system' => 'Sistema',
            'drinks' => 'Bebidas',
            'establishment' => 'Local & Discoteca',
            'staff' => 'Personal & Equipo',
            default => 'General',
        };
    }
}
