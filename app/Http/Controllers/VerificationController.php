<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{



    public function verify(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        abort(404);
    }

    $user->status = 1; // ✅ Only update status
    $user->save();

    return redirect('/auth/login')->with('verified', true);
}

    

    
}
