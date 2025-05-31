<?php
namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\Subscriber;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meters = Meter::all();
        return response()->json(['success' => true, 'data' => $meters]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'number' => 'required|unique:meters',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
            }

            $meter                = new Meter();
            $meter->subscriber_id = $request->subscriber_id;
            $meter->number        = $request->number;
            $meter->note          = $request->note;
            $meter->save();

            return response()->json(['success' => true]);
        } catch (Exception $error) {
            return response()->json(['success' => false, $error->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $meter = Meter::find($id);
        if ($meter) {
            return response()->json(['success' => true, 'data' => $meter]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Meter not found']], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'number' => 'required|unique:meters,number,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            $meter = Meter::find($id);
            if ($meter) {
                $meter->subscriber_id = $request->subscriber_id;
                $meter->number        = $request->number;
                $meter->note          = $request->note;
                $meter->save();

                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'errors' => ['Meter not found']], 404);
            }
        } catch (QueryException $queryException) {
            if ($queryException->getCode() === "23000") {
                return response()->json(['success' => false, 'errors' => ['Subscriber is already assigned to another meter']], 400);
            } else {
                return response()->json(['success' => false, 'errors' => [
                    'message' => 'Unhandled Query Error',
                    'code'    => $queryException->getCode()]],
                    500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meter = Meter::find($id);
        if ($meter) {
            $meter->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Meter not found']]);
        }
    }

    /**
     * Assigns meter to subscriber
     */
    public function assign($id, $subscriber)
    {

        try {
            $meter      = Meter::find($id);
            $subscriber = Subscriber::find($subscriber);

            if (! $meter) {
                return response()->json(['success' => false, 'errors' => ['Meter not found']]);
            }

            if (! $subscriber) {
                return response()->json(['success' => false, 'errors' => ['Subscriber not found']]);
            }

            $meter->subscriber_id = $subscriber->id;
            $meter->save();

            return response()->json(['success' => true]);
        } catch (QueryException $queryException) {
            if ($queryException->getCode() === "23000") {
                return response()->json(['success' => false, 'errors' => ['Subscriber is already assigned to another meter']], 400);
            } else {
                return response()->json(['success' => false, 'errors' => [
                    'message' => 'Unhandled Query Error',
                    'code'    => $queryException->getCode()]],
                    500);
            }
        }
    }

    /**
     * Clears the assigned subscriber from the meter
     */
    public function clear($id)
    {
        $meter = Meter::find($id);
        if ($meter) {
            $meter->subscriber_id = null;
            $meter->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Meter Not found']], 404);
        }
    }
}
