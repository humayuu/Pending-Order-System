<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryChallan extends Model
{
    protected $fillable = [
        'client_id',
        'challan_number',
        'issued_on',
        'vehicle_no',
        'remarks',
    ];

    protected $casts = [
        'issued_on' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryChallanLine::class);
    }
}
