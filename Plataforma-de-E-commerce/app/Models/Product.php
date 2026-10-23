<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable(['category_id', 'name', 'slug', 'description', 'price_cents', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
        public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $term ? $query->where('name', 'like', "%{$term}%") : $query;
    }

    // A mesma variante tem de cumprir tamanho, cor e stock ao mesmo tempo.
    public function scopeWithVariant(Builder $query, ?string $size, ?string $color, bool $inStock): Builder
    {
        if (! $size && ! $color && ! $inStock) {
            return $query;
        }

        return $query->whereHas('variants', fn ($q) => $q
            ->when($size, fn ($q) => $q->where('size', $size))
            ->when($color, fn ($q) => $q->where('color', $color))
            ->when($inStock, fn ($q) => $q->where('stock', '>', 0)));
    }

    public function scopePriceBetween(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query
            ->when($min !== null, fn ($q) => $q->where('price_cents', '>=', (int) round($min * 100)))
            ->when($max !== null, fn ($q) => $q->where('price_cents', '<=', (int) round($max * 100)));
    }

    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'newest' => $query->latest(),
            default => $query->orderBy('name'),
        };
    }
}