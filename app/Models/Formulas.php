<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulas extends Model
{
    protected $table = 'formulas';

    protected $fillable = ['name', 'expression', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    public function variables()
    {
        return $this->hasMany(FormulaVariable::class, 'formula_variables.formula_id', 'id');
    }
}
