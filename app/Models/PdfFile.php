<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PdfFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'original_name',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
        'is_active',
        'uploaded_by',
    ];

    // Laravel 12: تحسينات في Casts
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'file_size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Laravel 12: Computed properties using accessors
    protected function fileSizeFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->file_size;
                $units = ['B', 'KB', 'MB', 'GB'];

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::url($this->file_path)
        );
    }

    public static function getLatest(): ?self
    {
        return self::where('is_active', true)
            ->latest()
            ->first();
    }

    // Laravel 12: Scopes محسنة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('original_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
