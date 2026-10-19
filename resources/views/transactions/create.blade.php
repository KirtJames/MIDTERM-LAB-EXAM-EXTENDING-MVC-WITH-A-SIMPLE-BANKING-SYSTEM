<x-app-layout>
    <div class="max-w-xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4">New Transaction</h2>

        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1">Account</label>
                <select name="account_id" class="w-full border rounded p-2">
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                            {{ $account->account_number }} (Balance: {{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('account_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Type</label>
                <select name="type" class="w-full border rounded p-2">
                    <option value="deposit" @selected(old('type') === 'deposit')>Deposit</option>
                    <option value="withdrawal" @selected(old('type') === 'withdrawal')>Withdrawal</option>
                </select>
                @error('type') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="w-full border rounded p-2">
                @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Notes (optional)</label>
                <textarea name="notes" class="w-full border rounded p-2">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded">Submit</button>
        </form>
    </div>
</x-app-layout>