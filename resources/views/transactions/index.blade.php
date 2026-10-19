<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Transactions</h2>
            <a href="{{ route('transactions.create') }}" class="bg-gray-800 text-white px-4 py-2 rounded">New Transaction</a>
        </div>

        @if (session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <table class="w-full border text-left">
            <tr class="bg-gray-100">
                <th class="p-2">Ref #</th><th class="p-2">Account</th><th class="p-2">Type</th>
                <th class="p-2">Amount</th><th class="p-2">Date</th>
            </tr>
            @foreach ($transactions as $t)
                <tr class="border-t">
                    <td class="p-2">{{ $t->reference_number }}</td>
                    <td class="p-2">{{ $t->account->account_number }}</td>
                    <td class="p-2">{{ ucfirst($t->type) }}</td>
                    <td class="p-2">{{ number_format($t->amount, 2) }}</td>
                    <td class="p-2">{{ $t->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @endforeach
        </table>

        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>