<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'subscriber_id',
        'invoice_id',
        'xendit_invoice_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'notes',
    ];

    public function invoices(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
