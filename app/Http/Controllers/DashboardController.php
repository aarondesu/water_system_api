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
        // Get Dates
        $startDate = Carbon::now()->subYear();
        $endDate   = Carbon::now();

        // Get number of subscribers
        $subscribers = Subscriber::get()->count();

        // Get number of meters
        $meters        = Meter::get()->count();
        $meters_active = Meter::where('status', 'active')->count();

        // Get total consumption of water
        $monthly_consumption = DB::select("
                WITH month_series AS (
                    SELECT
                        generate_series(
                            date_trunc('month', ?::date),
                            date_trunc('month', ?::date),
                            interval '1 month'
                        ) AS month_start
                ),
                consumption_per_row AS (
                    SELECT
                        meter_id,
                        created_at,
                        TO_CHAR(created_at, 'FMMonth') as month,
                        EXTRACT(month from created_at) as month_date,
                        COALESCE(reading - LAG(reading) OVER (PARTITION BY meter_id ORDER BY created_at), reading) as consumption
                    FROM meter_readings
                ),
                monthly_consumption AS (
                    SELECT
                        date_trunc('month', created_at) as month_start,
                        SUM(consumption) AS total_consumption
                    FROM consumption_per_row
                    GROUP BY month_start
                )
                SELECT
                    TO_CHAR(month_series.month_start, 'FMMonth') as month,
                    EXTRACT(month from month_series.month_start) as month_date,
                    COALESCE(monthly_consumption.total_consumption, 0) AS total_consumption
                FROM month_series
                LEFT JOIN monthly_consumption ON month_series.month_start = monthly_consumption.month_start
                ORDER BY month_series.month_start;
        ", [Carbon::now()->subYear()->startOfMonth(), Carbon::now()]);

        $current_total_reading = DB::table(DB::raw("
                (
                    SELECT
                        meter_id,
                        created_at,
                        date_trunc('month', created_at) as month_date,
                        COALESCE(reading - LAG(reading) OVER (PARTITION BY meter_id ORDER BY created_at),0) as consumption
                    FROM meter_readings
                ) as sub
        "))
            ->select([
                DB::raw("TO_CHAR(month_date, 'FMMonth') as month"),
                DB::raw("COALESCE(SUM(consumption), 0) as total_consumption"),
            ])
            ->whereYear('month_date', Carbon::now()->year)
            ->whereMonth('month_date', Carbon::now()->month)
            ->groupBy("month_date")
            ->first();
        $current_total_reading = $current_total_reading ?: (object) [
            'month'             => Carbon::now()->format('F'),
            'total_consumption' => 0,
        ];

        $previous_total_reading = DB::table(DB::raw("
                (
                    SELECT
                        meter_id,
                        created_at,
                        date_trunc('month', created_at) as month_date,
                        COALESCE(reading - LAG(reading) OVER (PARTITION BY meter_id ORDER BY created_at),0) as consumption
                    FROM meter_readings
                ) as sub
        "))
            ->select([
                DB::raw("TO_CHAR(month_date, 'FMMonth') as month"),
                DB::raw("SUM(consumption) as total_consumption"),
            ])
            ->whereYear('month_date', Carbon::now()->subMonth()->year)
            ->whereMonth('month_date', Carbon::now()->subMonth()->month)
            ->groupBy("month_date")
            ->first();
        $previous_total_reading = $previous_total_reading ?: (object) [
            'month'             => Carbon::now()->format('F'),
            'total_consumption' => 0,
        ];

        $latest_readings = MeterReading::select(['id', 'reading', 'created_at', 'meter_id'])->with(['meter' => function ($query) {
            $query->select(['id', 'number', 'subscriber_id']);
        }])->with(['meter.subscriber' => function ($query) {
            $query->select(['id', 'first_name', 'last_name']);
        }])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get Invoices
        // $monthly_invoice = Invoice::select(
        //     DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
        //     DB::raw('SUM(amount_due) as total_amount_due')
        // )
        //     ->groupBy('month')
        //     ->whereYear('created_at', Carbon::now()->year)
        //     ->get();
        $monthly_invoice = DB::select("
            WITH month_series AS (
                SELECT
                    generate_series(
                        date_trunc('month', ?::date),
                        date_trunc('month', ?::date),
                        interval '1 month'
                    ) AS month_start
            ),
            monthly_invoice AS (
                SELECT
                    date_trunc('month', created_at) as month,
                    SUM(amount_due) as total_amount_due
                FROM invoices
                GROUP BY month
            )
            SELECT
                TO_CHAR(month_series.month_start, 'FMMonth') as month,
                EXTRACT(month from month_series.month_start) as month_date,
                COALESCE(monthly_invoice.total_amount_due, 0) AS total_amount_due
            FROM month_series
            LEFT JOIN monthly_invoice ON month_series.month_start = monthly_invoice.month
            ORDER BY month_series.month_start
        ",
            [Carbon::now()->subYear()->startOfMonth(), Carbon::now()]
        );

        $current_total_invoice = Invoice::select(
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
            DB::raw('COALESCE(SUM(amount_due), 0) as total_amount_due'),
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy("month")
            ->first();

        if (! $current_total_invoice) {
            $current_total_invoice = (object) [
                'month'             => Carbon::now()->format('F'),
                'totatl_amount_due' => 0,
            ];
        }

        $previous_total_balance = Invoice::select(
            DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
            DB::raw('COALESCE(SUM(amount_due), 0) as total_amount_due'),
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->groupBy("month")
            ->first();

        if (! $previous_total_balance) {
            $previous_total_balance = (object) [
                'month'            => Carbon::now()->subMonth()->format('F'),
                'total_amount_due' => 0,
            ];
        }

        return response()->json(['success' => true, 'data' => [
            'total_subscribers' => $subscribers,
            'meters'            => [
                'total'  => $meters,
                'active' => $meters_active,
            ],
            'readings'          => [
                'current_total_reading'  => $current_total_reading,
                'previous_total_reading' => $previous_total_reading,
                'monthly_consumption'    => $monthly_consumption,
                'latest'                 => $latest_readings,
            ],
            'invoice'           => [
                'current_balance'  => $current_total_invoice,
                'previous_balance' => $previous_total_balance,
                'monthly_invoice'  => $monthly_invoice,
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
