<?php
namespace App\Http\Controllers;

use App\Models\Formulas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormulaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formulas = Formulas::with('variables')->with('columns')->get();

        return response()->json(['success' => true, 'data' => $formulas]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                    => 'required',
            'expression'              => 'required',
            'description'             => 'sometimes',
            'variables'               => 'required',
            'variables.*.name'        => 'required',
            'variables.*.value'       => 'required',
            'variables.*.description' => 'sometimes',
            'columns'                 => 'required',
            'columns.*.header'        => 'required',
            'columns.*.value'         => 'required',
        ]);

        if (! $validator->fails()) {

            $formula              = new Formulas();
            $formula->name        = $request->name;
            $formula->expression  = $request->expression;
            $formula->description = $request->description;
            $formula->save();

            $formula->variables()->createMany($request->variables);
            $formula->columns()->createMany($request->columns);

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $formula = Formulas::find($id);
        if ($formula) {
            $formula->delete();
        }
    }
}
