<?php

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['label', 'recipient_name', 'phone_country', 'phone', 'street', 'postal_code', 'city', 'is_default'])]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Ex.: +351 912345678
    public function formattedPhone(): string
    {
        return config("countries.{$this->phone_country}.dial").' '.$this->phone;
    }

    // Morada em texto, para ficar guardada na encomenda mesmo que depois seja alterada ou apagada.
    public function toShippingText(): string
    {
        return implode("\n", [
            $this->recipient_name,
            $this->street,
            "{$this->postal_code} {$this->city}",
            $this->formattedPhone(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Só pode haver uma morada principal por cliente.
    public function makeDefault(): void
    {
        $this->user->addresses()->whereKeyNot($this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
