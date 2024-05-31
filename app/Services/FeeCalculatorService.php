<?php

namespace App\Services;

use Carbon\Carbon;

class FeeCalculatorService
{
    public function calculateWithdrawalFee($user, $amount)
    {
        $fee = 0;
        $accountType = $user->account_type;
        $today = Carbon::now();

        if ($accountType == 'Individual') {
            $fee = $this->calculateIndividualFee($user, $amount, $today);
        } elseif ($accountType == 'Business') {
            $fee = $this->calculateBusinessFee($user, $amount);
        }

        return $fee;
    }

    private function calculateIndividualFee($user, $amount, $today)
    {
        $monthlyWithdrawals = $user->transactions()
            ->where('transaction_type', 'Withdrawal')
            ->whereMonth('date', $today->month)
            ->sum('amount');

        $remainingFreeLimit = max(5000 - $monthlyWithdrawals, 0);

        if ($today->isFriday() || $amount <= 1000 || $remainingFreeLimit >= $amount) {
            return 0;
        } else {
            $amountSubjectToFee = max($amount - $remainingFreeLimit, 0);

            if ($amountSubjectToFee > 1000) {
                return ($amountSubjectToFee - 1000) * 0.015 / 100;
            } else {
                return $amountSubjectToFee * 0.015 / 100;
            }
        }
    }

    private function calculateBusinessFee($user, $amount)
    {
        $totalWithdrawals = $user->transactions()
            ->where('transaction_type', 'Withdrawal')
            ->sum('amount');

        $feeRate = $totalWithdrawals > 50000 ? 0.015 : 0.025;
        return $amount * $feeRate / 100;
    }
}
