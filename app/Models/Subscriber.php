<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscriber extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriberFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'email',
        'mobile_number',
    ];

    public function meter(): HasOne
    {
        return $this->hasOne(Meter::class, 'subscriber_id', 'id');
    }
}
