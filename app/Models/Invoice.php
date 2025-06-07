<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = [
        'subscriber_id',
        'meter_id',
        'previous_reading_id',
        'current_reading_id',
        'consumption',
        'rate_per_unit',
        'amount_due',
        'status',
        'due_date',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function previous_reading(): HasOne
    {
        return $this->hasOne(MeterReading::class, 'id', 'previous_reading_id');
    }

    public function current_reading(): HasOne
    {
        return $this->hasOne(MeterReading::class, 'id', 'current_reading_id');
    }
}
