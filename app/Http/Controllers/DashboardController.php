<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Subscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get number of subscribers
        $subscribers = Subscriber::get()->count();

        // Get number of meters
        $meters        = Meter::get()->count();
        $meters_active = Meter::where('status', 'active')->count();

        // Get total consumption of water
        $consumption = DB::select("
                WITH consumption_per_row AS (
                    SELECT
                        meter_id,
                        created_at,
                        TO_CHAR(created_at, 'FMMonth') as month,
                        EXTRACT(month from created_at) as month_date,
                        COALESCE(reading - LAG(reading) OVER (PARTITION BY meter_id ORDER BY created_at), reading) as consumption
                    FROM meter_readings
                )
                SELECT
                    month,
                    month_date,
                    SUM(consumption) AS total_consumption
                FROM consumption_per_row
                GROUP BY month, month_date
                ORDER BY month_date
        ");

        $current_reading = MeterReading::select(
            DB::raw('SUM(reading) as total_reading'),
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('month')
            ->first();

        $previous_reading = MeterReading::select(
            DB::raw('SUM(reading) as total_reading'),
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->groupBy('month')
            ->first();

        $latest_readings = MeterReading::select(['id', 'reading', 'created_at', 'meter_id'])->with(['meter' => function ($query) {
            $query->select(['id', 'number', 'subscriber_id']);
        }])->with(['meter.subscriber' => function ($query) {
            $query->select(['id', 'first_name', 'last_name']);
        }])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get Invoices
        $invoices = Invoice::select(
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
            DB::raw('SUM(amount_due) as total_amount_due')
        )
            ->groupBy('month')
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        $current_invoice = Invoice::select(
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
            DB::raw('SUM(amount_due) as total_amount_due'),
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy("month")
            ->first();

        return response()->json(['success' => true, 'data' => [
            'total_subscribers' => $subscribers,
            'meters'            => [
                'total'  => $meters,
                'active' => $meters_active,
            ],
            'readings'          => [
                'current_reading'  => $current_reading,
                'previous_reading' => $previous_reading,
                'list'             => $consumption,
                'latest'           => $latest_readings,
            ],
            'invoice'           => [
                'current_invoice' => $current_invoice,
                'list'            => $invoices,
            ],
        ]]);

        // try {

        // } catch (QueryException $queryException) {
        //     return response()->json(['success' => false, 'errors' => [
        //         'code'     => $queryException->getCode(),
        //         'message'  => $queryException->getMessage(),
        //         'bindings' => $queryException->getBindings(),
        //     ]], 500);
        // }
    }
}
