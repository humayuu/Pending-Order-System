<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_name',
        'po_number',
        'notes',
        'quantity',
        'po_pdf_path',
        'image_path',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryChallanLines(): HasMany
    {
        return $this->hasMany(DeliveryChallanLine::class);
    }

    public function deliveredQuantity(): int
    {
        return (int) $this->deliveryChallanLines()->sum('quantity');
    }

    public function pendingQuantity(): int
    {
        return max(0, $this->quantity - $this->deliveredQuantity());
    }
}
