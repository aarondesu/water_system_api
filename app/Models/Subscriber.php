<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function meter(): HasOne
    {
        return $this->hasOne(Meter::class, 'subscriber_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscriber_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, "subscriber_id", "id");
    }

    public function getArrearsAttribute()
    {
        $total_paid   = $this->transactions()->sum("amount_paid");
        $total_amount = $this->invoices()->where('subscriber_id', '=', $this->id)->sum('amount_due');

        return max($total_amount - $total_paid, 0);
    }

    public function arrears()
    {
        $total_paid   = $this->transactions()->sum("amount_paid");
        $total_amount = $this->invoices()->where('subscriber_id', '=', $this->subscriber_id)->sum('amount_due');

        return max($total_amount - $total_paid, 0);
    }
}
