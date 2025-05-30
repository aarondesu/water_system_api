<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    //
    protected $fillable = [
        'meter_id',
        'reading',
        'note',
    ];

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
