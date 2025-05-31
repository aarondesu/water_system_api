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
    protected $hidden = [
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:m-d-Y',
            'updated_at' => 'datetime:m-d-Y',
        ];
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
