<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransactionRequest;


class TransactionRequestController extends Controller
{
     public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed',
            'transaction_type' => 'required|string|max:255',
        ]);

        $transaction = TransactionRequest::create($validated);

        return response()->json([
            'message' => 'Transaction request sent successfully.',
            'data' => $transaction,
        ], 201);
    }
}
