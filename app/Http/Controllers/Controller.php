<?php
namespace App\Http\Controllers;

abstract class Controller
{
    public function notYetImplementedResponse()
    {
        return response()->json(['success' => false, 'errors' => [
            'Not Yet Implemented',
        ]], 400);
    }
}
