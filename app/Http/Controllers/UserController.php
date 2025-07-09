<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $users = User::all();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required | unique:users',
            'password' => 'required',
        ]);

        if (! $validator->fails()) {
            $user = new User([
                'username'   => $request->username,
                'password'   => $request->password,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
            ]);

            $user->save();

            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
    }
    public function retrieve()
    {
        return $this->notYetImplementedResponse();
    }
    public function update()
    {
        return $this->notYetImplementedResponse();
    }
    public function delete($id)
    {
        try {
            $user = User::find($id);
            if (! $user && ! $user->exists()) {
                return response()->json(['success' => false, 'errors' => ['User does not exist']]);
            }

            $user->delete();
            return response()->json(['success' => true]);

        } catch (QueryException $queryException) {
            return response()->json(['success' => false, 'errors' => [
                'code'    => $queryException->getCode(),
                'message' => $queryException->getMessage(),
            ]], 400);
        }
    }
}
