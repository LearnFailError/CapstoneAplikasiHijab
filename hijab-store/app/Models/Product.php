<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'material',
        'color',
        'price',
        'stock',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'category_id' => (int) $this->category_id,
            'name' => $this->name,
            'description' => $this->description ?? '',
            'material' => $this->material ?? '',
            'color' => $this->color ?? '',
            'category' => $this->category?->name ?? '',
            'stock' => (int) $this->stock,
            'price' => (float) $this->price,
            'is_active' => (int) $this->is_active,
            'created_at' => $this->created_at?->timestamp ?? time(),
        ];
    }

    public function searchableAs(): string
    {
        return 'products_index';
    }
}
