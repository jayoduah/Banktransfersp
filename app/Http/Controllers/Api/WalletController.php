<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    // 1. Create a wallet or bank account
    public function createWallet(Request $request)
    {
        $request->validate([
            'type' => 'required|in:naira,dollar,bank'
        ]);

        $wallet = $request->user()->wallets()->firstOrCreate(
            ['type' => $request->type],
            ['balance' => 0.00]
        );

        return response()->json(['message' => 'Account ready', 'wallet' => $wallet]);
    }

    // 2. See balances
    public function getBalance(Request $request)
    {
        $query = $request->user()->wallets();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json(['balances' => $query->get()]);
    }

    // 3. Fund a specific account
    public function fundOne(Request $request)
    {
        $request->validate([
            'type' => 'required|in:naira,dollar,bank',
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = $request->user()->wallets()->where('type', $request->type)->firstOrFail();
        
        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json(['message' => 'Funded successfully', 'wallet' => $wallet]);
    }

    // 4. Fund all accounts at once
    public function fundAll(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $wallets = $request->user()->wallets()->get();
            foreach ($wallets as $wallet) {
                $wallet->balance += $request->amount;
                $wallet->save();
            }
        });

        return response()->json([
            'message' => 'All accounts funded successfully', 
            'balances' => $request->user()->wallets()->get()
        ]);
    }

    // 5. Withdraw from an account
    public function withdraw(Request $request)
    {
        $request->validate([
            'type' => 'required|in:naira,dollar,bank',
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = $request->user()->wallets()->where('type', $request->type)->firstOrFail();

        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        return response()->json(['message' => 'Withdrawal successful', 'wallet' => $wallet]);
    }
}