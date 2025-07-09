<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $appends = ["arrears"];

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

    protected $casts = [
        'created_at' => 'datetime:F d, Y',
        'due_date'   => 'datetime:F d, Y',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getArrearsAttribute()
    {

        $total_paid   = $this->transactions()->sum("amount_paid");
        $total_amount = $this->where('subscriber_id', '=', $this->subscriber_id)->sum('amount_due');

        return max($total_amount - $total_paid, 0);
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
