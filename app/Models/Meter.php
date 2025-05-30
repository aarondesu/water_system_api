<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    //
    protected $fillable = [
        'number',
        'note',
        'subscriber_id',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id', 'id');
    }
}
