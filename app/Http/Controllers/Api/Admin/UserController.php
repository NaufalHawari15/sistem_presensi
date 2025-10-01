<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function activateUser($id)
    {
        return response()->json([
            'message' => "User {$id} berhasil diaktifkan (dummy response)."
        ]);
    }
}
