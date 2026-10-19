<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('account')
            ->whereHas('account', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        $accounts = $request->user()->accounts;

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => [
                'required',
                // exists in accounts AND belongs to the logged-in user
                Rule::exists('accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'type'   => ['required', Rule::in(['deposit', 'withdrawal'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        // Custom validation: sufficient funds check BEFORE attempting to save
        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty() || $request->type !== 'withdrawal') {
                return;
            }

            $account = Account::find($request->account_id);

            if (bcsub($account->balance, (string) $request->amount, 2) < $account->minimum_balance) {
                $validator->errors()->add(
                    'amount',
                    'Insufficient funds. You must keep at least '
                    . number_format($account->minimum_balance, 2) . ' in the account.'
                );
            }
        });

        $data = $validator->validate();

        try {
            DB::transaction(function () use ($data) {
                Transaction::create([
                    'account_id'       => $data['account_id'],
                    'type'             => $data['type'],
                    'amount'           => $data['amount'],
                    'reference_number' => 'TXN-' . Str::upper(Str::random(10)),
                    'notes'            => $data['notes'] ?? null,
                ]);
                // The observer's creating() hook updates the balance inside this same DB transaction
            });
        } catch (InsufficientFundsException $e) {
            // Safety net (e.g. race condition after validation passed)
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction completed.');
    }
}