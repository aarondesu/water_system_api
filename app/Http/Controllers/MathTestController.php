<?php
namespace App\Http\Controllers;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class MathTestController extends Controller
{
    public function test()
    {
        $language = new ExpressionLanguage();

        $variables = [
            'consumption'   => 40.10,
            'rate_per_unit' => 80,
            'minimum'       => 555,
            't11'           => 60.75,
            't21'           => 69.30,
            't31'           => 78.50,
            't41'           => 88.55,
        ];

        $formula = 'minimum + max(0, min(consumption, 20) - 10) * t11 + max(0, min(consumption, 30) - 20) * t21 + max(0, min(consumption, 40) - 30) * t31 + max(0, consumption - 40) * t41';
        $result  = $language->evaluate($formula, $variables);

        return response()->json([
            'success' => true,
            'data'    => [
                $result,
            ],
        ]);
    }
}
