<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulaVariable extends Model
{
    protected $table = 'formula_variables';

    protected $fillable = [
        'name',
        'description',
        'value',
        'unit',
        'is_required',
        'formula_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function formula()
    {
        return $this->belongsTo(Formulas::class, 'formula_id', 'formulas', 'id');
    }
}
