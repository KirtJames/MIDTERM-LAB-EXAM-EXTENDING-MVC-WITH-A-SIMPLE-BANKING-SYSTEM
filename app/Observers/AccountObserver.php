<?php

namespace App\Observers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use App\Notifications\LowBalanceNotification;
use Illuminate\Support\Facades\DB;

class AccountObserver
{
    public function creating(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Lock the row so two simultaneous requests can't both pass the balance check
            $account = Account::lockForUpdate()->findOrFail($transaction->account_id);

            $oldBalance = $account->balance;

            $newBalance = $transaction->type === 'deposit'
                ? bcadd($oldBalance, $transaction->amount, 2)
                : bcsub($oldBalance, $transaction->amount, 2);

            // Critical rule: a debit may not push the balance below the minimum (or negative)
            if ($transaction->type !== 'deposit'
                && (bccomp($newBalance, '0', 2) < 0
                    || bccomp($newBalance, $account->minimum_balance, 2) < 0)) {
                throw new InsufficientFundsException(
                    'Insufficient funds: this withdrawal would drop the balance below the minimum of '
                    . number_format($account->minimum_balance, 2) . '.'
                );
            }

            $account->update(['balance' => $newBalance]);

            // Notify only when the balance CROSSES down to/below the minimum (prevents duplicates)
            $crossedThreshold = $transaction->type !== 'deposit'
                && bccomp($oldBalance, $account->minimum_balance, 2) > 0
                && bccomp($newBalance, $account->minimum_balance, 2) <= 0;

            if ($crossedThreshold) {
                // Runs only after the outermost DB transaction commits successfully
                DB::afterCommit(fn () => $account->user->notify(new LowBalanceNotification($account)));
            }
        });
    }
}