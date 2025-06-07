<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MeterReading;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rows     = $request->get('rows') ?? 10;
        $order    = $request->get('order') ?? "asc";
        $invoices = Invoice::with('subscriber')->paginate($rows);

        return response()->json(['success' => true, 'data' => [
            'items' => $invoices->items(),
            'pages' => $invoices->lastPage(),
        ]]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subscriber_id'       => 'required|exists:subscribers,id',
                'meter_id'            => 'required|exists:meters,id',
                'previous_reading_id' => 'required|exists:meter_readings,id',
                'current_reading_id'  => 'required|exists:meter_readings,id',
                'rate_per_unit'       => 'required',
                'due_date'            => 'required',
            ]);

            // if validation fails, return with errors of validation
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            $previous_reading = MeterReading::find($request->previous_reading_id);
            if (! $previous_reading) {
                return response()->json(['success' => false, 'errors' => ['Failed to get previous reading']]);
            }

            $current_reading = MeterReading::find($request->current_reading_id);
            if (! $current_reading) {
                return response()->json(['success' => false, 'errors' => ['Failed to get current reading']]);
            }

            $invoice                      = new Invoice();
            $invoice->subscriber_id       = $request->subscriber_id;
            $invoice->meter_id            = $request->meter_id;
            $invoice->previous_reading_id = $previous_reading->id;
            $invoice->current_reading_id  = $current_reading->id;
            $invoice->consumption         = $current_reading->reading - $previous_reading->reading;
            $invoice->rate_per_unit       = $request->rate_per_unit;
            $invoice->amount_due          = ($current_reading->reading - $previous_reading->reading) * $request->rate_per_unit;
            $invoice->status              = 'unpaid';
            $invoice->due_date            = $request->due_date;
            $invoice->save();

            return response()->json(['success' => true]);

        } catch (QueryException $queryException) {
            return response()->json(['success' => false, 'errors' => [
                'code'    => $queryException->getCode(),
                'message' => $queryException->getMessage(),
            ]], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::find($id);

        if ($invoice) {
            return response()->json(['success' => true, 'data' => $invoice]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Invoice does not exists.']]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $invoice = Invoice::find($id);
            if (! $invoice) {
                return response()->json(['success' => false, 'erros' => ['Invoice does not exist']]);
            }

            $validator = Validator::make($request->all(), [
                'previous_reading_id' => 'required|exists:meter_readings,id',
                'current_reading_id'  => 'required|exists:meter_readings,id',
                'rate_per_unit'       => 'required',
                'due_date'            => 'required',
            ]);

            // if validation fails, return with errors of validation
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            $previous_reading = MeterReading::find($request->previous_reading_id);
            if (! $previous_reading) {
                return response()->json(['success' => false, 'errors' => ['Failed to get previous reading']]);
            }

            $current_reading = MeterReading::find($request->current_reading_id);
            if (! $current_reading) {
                return response()->json(['success' => false, 'errors' => ['Failed to get current reading']]);
            }

            $invoice->previous_reading_id = $previous_reading->id;
            $invoice->current_reading_id  = $current_reading->id;
            $invoice->consumption         = $current_reading->reading - $previous_reading->reading;
            $invoice->rate_per_unit       = $request->rate_per_unit;
            $invoice->amount_due          = ($current_reading->reading - $previous_reading->reading) * $request->rate_per_unit;
            $invoice->due_date            = $request->due_date;
            $invoice->save();

        } catch (QueryException $queryException) {
            return response()->json(['success' => false, 'errors' => [
                'code'    => $queryException->getCode(),
                'message' => $queryException->getMessage(),
            ]], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
