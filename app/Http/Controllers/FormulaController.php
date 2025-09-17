<?php
namespace App\Http\Controllers;

use App\Models\Formulas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'name'                    => 'required | unique:formulas',
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $formula = Formulas::with('variables')->with('columns')->find($id);

        if ($formula) {
            return response()->json(['success' => true, 'data' => $formula]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Failed to get formula ' . $id . '. Does not exist.']], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

            $formula = Formulas::find($id);
            if (! $formula) {
                return response()->json(['success' => false, 'errors' => ['Failed to get ' . $id . '. Does not exist']]);
            }

            DB::transaction(function () use ($request, $formula) {
                $formula->update([
                    'name'        => $request->name,
                    'expression'  => $request->expression,
                    'description' => $request->description,
                ]);

                // Create, update, delete variables
                $variables = $request->variables;
                foreach ($variables as $variable) {
                    if (isset($variable["id"]) && $variable["id"] !== 0) {
                        // Get variable
                        $var = $formula->variables()->where('id', $variable["id"]);

                        // Check if variable exists
                        if ($var) {
                            // Check if delete variable
                            if (isset($variable["delete"])) {
                                $var->delete();
                            } else {
                                $var->update($variable);
                            }
                        }
                    } else {
                        // Create new variable
                        $formula->variables()->create($variable);
                    }
                }

                // Create, update, delete columns
                $columns = $request->columns;
                foreach ($columns as $column) {
                    if (isset($column["id"]) && $column["id"] !== 0) {
                        // Get column
                        $col = $formula->columns()->where("id", $column["id"]);

                        // Check if column exists
                        if ($col) {
                            if (isset($column["delete"])) {
                                $col->delete();
                            } else {
                                $col->update($column);
                            }
                        }
                    } else {
                        // Create new table_column
                        $formula->columns()->create($column);
                    }
                }

            });

            $formula->name        = $request->name;
            $formula->expression  = $request->expression;
            $formula->description = $request->description;
            $formula->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $formula = Formulas::find($id);
        if ($formula) {
            $formula->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'errors' => ['Formula does not exist.']], 422);
    }

    public function bulkDestroy(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'required|array',
        ]);

        if (! $validate->fails()) {
            $ids = $request->ids;

            Formulas::whereIn('id', $ids)->delete();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => $validate->errors()], 422);
        }

    }
}
