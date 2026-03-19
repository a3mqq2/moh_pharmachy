<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SharedFile extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'original_name',
        'file_extension',
        'file_size',
        'notes',
        'shared_by',
    ];

    public function sharer()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'shared_file_users')
            ->withPivot('seen_at')
            ->withTimestamps();
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
