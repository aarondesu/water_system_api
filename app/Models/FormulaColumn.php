<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulaColumn extends Model
{
    //
    protected $fillable = [
        'formula_id',
        'header',
        'value',
    ];

    public function formula()
    {
        return $this->belongsTo(Formulas::class, 'formula_id', 'formulas', 'id');
    }
}
