<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    // protected $appends = ["arrears"];
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

        $arrears = Invoice::with('transactions')
            ->whereDate('created_at', '<', $this->created_at)
            ->where('subscriber_id', '=', $this->subscriber_id)
            ->where('status', '!=', 'paid')
            ->get();

        // dd($arrears);
        return $arrears;
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
