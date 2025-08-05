<?php
namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Search Params
        $rows    = $request->get("rows", 10);
        $order   = $request->get('order', 'desc');
        $meter   = $request->get('meter');
        $reading = $request->get('reading');

        // Query
        $readings = MeterReading::with(['meter'])
            ->whereHas('meter', function ($query) use ($meter) {
                // Check if meter has a parameter then do a query
                $query->when($meter, function ($query) use ($meter) {
                    $query->where('number', 'LIKE', '%' . $meter . '%');
                });
            })
            ->when($reading, function ($query) use ($reading) {
                // Filter reading based on search param
                $query->where('reading', 'LIKE', '%' . $reading . '%');
            })
            ->with(['meter.subscriber' => function ($query) {
                $query->select(['id', 'first_name', 'last_name']);
            }])
            ->orderBy('created_at', $order)
            ->paginate($rows);

        return response()->json(['success' => true, 'data' => [
            'items' => $readings->items(),
            'pages' => $readings->lastPage(),
        ]]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meter_id'   => 'required',
            'reading'    => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',

        ]);

        try {
            $reading             = new MeterReading();
            $reading->meter_id   = $request->meter_id;
            $reading->reading    = $request->reading;
            $reading->start_date = $request->start_date;
            $reading->end_date   = $request->end_date;
            $reading->note       = $request->note;

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
            $reading->reading    = $request->reading;
            $reading->start_date = $request->start_date;
            $reading->end_date   = $request->end_date;
            $reading->note       = $request->note;

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

    /**
     * Get the latest readings per meter along with the assigned subscriber
     * @return void
     */
    public function latestReadingsMeter()
    {
        try {
            $meter_readings = Meter::with(['readings' => function ($query) {
                $query->select(['id', 'meter_id', 'reading'])->orderByDesc('created_at')->limit(1);
            }, 'subscriber'])->get();

            return response()->json(['success' => true, 'data' => $meter_readings]);
        } catch (QueryException $queryException) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => [
                        'message' => 'Unhandled Query Error',
                        'code'    => $queryException->getCode(),
                    ],
                ],
                500
            );
        }
    }

    public function latest()
    {
        try {
            $readings = Meter::with([
                'subscriber' => function ($query) {
                    $query->select("id", "first_name", "last_name");
                },
                'readings'   => function ($query) {
                    $query->orderByDesc('created_at')->latest('created_at')->limit(2);
                }])->get();

            return response()->json(['success' => true, 'data' => $readings]);

        } catch (QueryException $queryException) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => [
                        'message' => 'Unhandled Query Error',
                        'code'    => $queryException->getCode(),
                    ],
                ],
                500
            );
        }
    }

    public function bulkStore(Request $request)
    {
        try {

            $readings = $request->readings;

            foreach ($readings as $r) {
                $reading             = new MeterReading();
                $reading->meter_id   = $r["meter_id"];
                $reading->reading    = $r["reading"];
                $reading->start_date = $r["start_date"];
                $reading->end_date   = $r["end_date"];

                $reading->save();
            }

            return response()->json(['success' => true]);

        } catch (QueryException $queryException) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => [
                        'message' => 'Unhandled Query Error',
                        'stack'   => $queryException->getMessage(),
                        'code'    => $queryException->getCode(),
                    ],
                ],
                500
            );
        }
    }
}
