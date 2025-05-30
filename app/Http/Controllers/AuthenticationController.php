<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (! $validator->fails()) {
            if (auth()->attempt(['username' => $request->username, 'password' => $request->password])) {
                $token = auth()->user()->createToken('water_system', ['admin'], now()->addWeek());

                return response()->json(['success' => true, 'data' =>
                    [
                        'user'  => auth()->user(),
                        'token' => $token->plainTextToken,
                    ],
                ]);
            } else {
                return response()->json(['success' => false, 'errors' => [
                    'Invalid Username and/or Password.',
                ]], 401);
            }
        } else {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
    }

    public function logout(Request $request)
    {
        if (auth()->check()) {
            $request->user()->currentAccessToken()->delete();
        }
    }
}
