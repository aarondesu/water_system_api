<?php
namespace App\Http\Controllers;

use App\Models\MeterReading;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $readings = MeterReading::with('meter.subscriber')->get();
        return response()->json(['success' => true, 'data' => $readings]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meter_id' => 'required',
            'reading'  => 'required',
        ]);

        try {
            $reading           = new MeterReading();
            $reading->meter_id = $request->meter_id;
            $reading->reading  = $request->reading;
            $reading->note     = $request->note;

            $reading->save();

        } catch (QueryException $queryException) {
            return response()->json(['success' => false, 'errors' => [
                'message' => 'Unhandled Query Error',
                'code'    => $queryException->getCode()]],
                500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reading = MeterReading::find($id);
        if ($reading) {
            return response()->json(['success' => true, 'data' => $reading]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Reading not found']], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reading = MeterReading::find($id);
        if ($reading) {
            $reading->reading = $request->reading;
            $reading->note    = $request->note;
            $reading->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Reading not found']], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reading = MeterReading::find($id);
        if ($reading) {
            $reading->delete();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Reading not found']], 404);
        }
    }
}
