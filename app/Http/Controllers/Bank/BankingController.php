<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FeeCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankingController extends Controller
{
    protected FeeCalculatorService $feeCalculatorService;

    public function __construct(FeeCalculatorService $feeCalculatorService)
    {
        $this->feeCalculatorService = $feeCalculatorService;
    }
    public function show_transactions()
    {
        $user = auth()->user();
        $transactions = $user->transactions;
        return response()->json([
            'balance' => $user->balance,
            'transactions' => $transactions
        ]);
    }

    public function show_deposits()
    {
        $user = auth()->user();
        $deposits = $user->transactions()->where('transaction_type','deposit')->paginate(9);
        return response()->json($deposits);
    }

    public function showWithdrawals()
    {
        $user = auth()->user();
        $withdrawals = $user->transactions()->where('transaction_type', 'withdrawal')->paginate(9);

        return response()->json($withdrawals, 200);
    }

    public function deposit(DepositRequest $request)
    {
        $user = User::find($request->user_id);
        $amount = $request->amount;

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->transaction_type = 'deposit';
        $transaction->amount = $amount;
        $transaction->fee = 0;
        $transaction->date = Carbon::now();
        $transaction->save();

        $user->balance += $amount;
        $user->update();

        return response()->json($transaction, 201);
    }

    public function withdraw (WithdrawRequest $request)
    {
        $user = User::find($request->user_id);
        $amount = $request->amount;

        if ($user->balance < $amount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        $fee = $this->feeCalculatorService->calculateWithdrawalFee($user, $amount);
        $totalAmount = $amount + $fee;

        if ($user->balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient funds including fee'], 400);
        }

        $user->balance -= $totalAmount;
        $user->save();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->transaction_type = 'withdrawal';
        $transaction->amount = $amount;
        $transaction->fee = $fee;
        $transaction->date = Carbon::now();
        $transaction->save();
        return response()->json(['message' => 'Withdrawal successful'], 200);
    }

    private function calculateWithdrawalFee($user, $amount)
    {
        $fee = 0;
        $accountType = $user->account_type;
        $today = Carbon::now();

        if ($accountType == 'Individual') {
            // Check how much has already been withdrawn this month
            $monthlyWithdrawals = $user->transactions()
                ->where('transaction_type', 'Withdrawal')
                ->whereMonth('date', $today->month)
                ->sum('amount');

            // Calculate how much of the current withdrawal is free
            $remainingFreeLimit = max(5000 - $monthlyWithdrawals, 0);

            if ($today->isFriday() || $amount <= 1000 || $remainingFreeLimit >= $amount) {
                $fee = 0;
            } else {
                // Calculate the fees
                $amountSubjectToFee = max($amount - $remainingFreeLimit, 0);

                // Fee calculation for amount above 1000 if not within free limits
                if ($amountSubjectToFee > 1000) {
                    $fee = ($amountSubjectToFee - 1000) * 0.015 / 100;
                } else {
                    $fee = $amountSubjectToFee * 0.015 / 100;
                }
            }
        } elseif ($accountType == 'Business') {
            // For Business accounts, we need to check the total withdrawals
            $totalWithdrawals = $user->transactions()
                ->where('transaction_type', 'withdrawal')
                ->sum('amount');

            $feeRate = $totalWithdrawals > 50000 ? 0.015 : 0.025;

            // Calculate the fee
            $fee = $amount * $feeRate / 100;
        }

        return $fee;
    }

}
