<?php
namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\Subscriber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    public function __construct()
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $order       = $request->get('order');
        $subscribers = Subscriber::when($order, function ($query) use ($order) {
            $query->orderBy('last_name', $order)->with('meter');
        })->get()->all();
        return response()->json(['success' => true, 'data' => $subscribers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required',
            'last_name'     => "required",
            'address'       => 'required',
            'mobile_number' => 'required',
            'email'         => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $subscriber = new Subscriber([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'address'       => $request->address,
            'mobile_number' => $request->mobile_number,
            'email'         => $request->email,
        ]);

        $subscriber->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subscriber = Subscriber::with(['meter.readings' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->with('invoices')->find($id);

        if ($subscriber) {
            return response()->json(['success' => true, 'data' => $subscriber]);
        } else {
            return response()->json(['success' => false, 'errors' => ['Subscriber does not exists.']]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subscriber = Subscriber::find($id);
        if ($subscriber) {
            $subscriber->first_name    = $request->first_name;
            $subscriber->last_name     = $request->last_name;
            $subscriber->address       = $request->address;
            $subscriber->email         = $request->email;
            $subscriber->mobile_number = $request->mobile_number;
            $subscriber->save();

            return response()->json(['success' => true]);

        } else {
            return response()->json(['success' => false, 'errors' => ['Subscriber not found']], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subscriber = Subscriber::find($id);

            if ($subscriber) {
                // Unassign subscriber from meter, if no meter is found ignore
                $meter = Meter::where('subscriber_id', '=', $id)->first();
                if ($meter && $meter->exists()) {
                    $meter->subscriber_id = null;
                    $meter->save();
                }

                $subscriber->delete();

                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'errors' => ['Subscriber not found.']], 404);
            }
        } catch (Exception $error) {
            return response()->json(['success' => false, 'error' => $error->getMessage()]);
        }
    }

    public function meter($id)
    {
        $subscriber = Subscriber::find($id);
        if ($subscriber) {
            $meter = $subscriber->meter()->get();

            return response()->json(['data' => $meter]);
        }
    }

    public function unassigned()
    {
        $subscribers = Subscriber::leftJoin('meters', 'subscribers.id', '=', 'meters.subscriber_id')
            ->where('meters.subscriber_id', '=', '0')->get();
        dd($subscribers);
    }
}
