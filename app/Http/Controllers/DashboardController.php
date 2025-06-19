<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get number of subscribers
            $subscribers = Subscriber::get()->count();

            // Get number of meters
            $meters        = Meter::get()->count();
            $meters_active = Meter::where('status', 'active')->count();

            // Get total consumption of water
            $consumption = MeterReading::select(
                DB::raw('SUM(reading) as total_reading'),
                                                                    //DB::raw("MONTHNAME(created_at) as month"), MYSQL
                DB::raw("TO_CHAR(created_at, 'FMMonth') as month"), // POSTGRES,
                DB::raw('EXTRACT(month from created_at) as month_date')
            )
                ->whereBetween('created_at', [Carbon::now()->subMonths(12), Carbon::now()])
                ->groupBy('month', 'month_date')
                ->orderBy('month_date')
                ->get();

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

            // Get Invoices
            $invoices = Invoice::select(
                DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
                DB::raw('SUM(amount_due) as total_amount_due')
            )
                ->groupBy('month')
                ->whereYear('created_at', Carbon::now()->year)
                ->get();

            $total_amount_due = Invoice::select(
                DB::raw("TO_CHAR(created_at, 'FMMonth') as month"),
                DB::raw('SUM(amount_due) as total_amount_due'),
            )
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->groupBy("month")
                ->get();

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
                ],
                'invoice'           => [
                    'current_amount_due' => $total_amount_due,
                    'lsit'               => $invoices,
                ],
            ]]);
        } catch (QueryException $queryException) {
            return response()->json(['success' => false, 'errors' => [
                'code'     => $queryException->getCode(),
                'message'  => $queryException->getMessage(),
                'bindings' => $queryException->getBindings(),
            ]], 500);
        }
    }
}
