<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4">Bank Dashboard</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="border rounded p-4 bg-white">
                <p class="text-gray-500 text-sm">Total Accounts</p>
                <p id="total-accounts" class="text-2xl font-bold">—</p>
            </div>
            <div class="border rounded p-4 bg-white">
                <p class="text-gray-500 text-sm">Total Money in Bank</p>
                <p id="total-money" class="text-2xl font-bold">—</p>
            </div>
            <div class="border rounded p-4 bg-white">
                <p class="text-gray-500 text-sm">Transactions Today</p>
                <p id="transactions-today" class="text-2xl font-bold">—</p>
            </div>
        </div>

        <p id="last-updated" class="text-gray-500 text-sm mt-4"></p>

        <div class="mt-6 space-x-2">
            <a href="{{ route('transactions.create') }}" class="bg-gray-800 text-white px-4 py-2 rounded">New Transaction</a>
            <a href="{{ route('transactions.index') }}" class="border px-4 py-2 rounded">View Transactions</a>
        </div>
    </div>

    <script>
        async function loadStats() {
            try {
                const res = await fetch('/api/dashboard/stats', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                document.getElementById('total-accounts').textContent = data.total_accounts;
                document.getElementById('total-money').textContent =
                    Number(data.total_money).toLocaleString(undefined, { minimumFractionDigits: 2 });
                document.getElementById('transactions-today').textContent = data.transactions_today;
                document.getElementById('last-updated').textContent =
                    'Last updated: ' + new Date().toLocaleTimeString();
            } catch (err) {
                console.error('Failed to load stats:', err);
            }
        }

        loadStats();                    // load immediately
        setInterval(loadStats, 30000);  // then every 30 seconds
    </script>
</x-app-layout>