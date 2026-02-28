<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Investor Profit Analysis</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14px;
            margin-top: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .summary-box {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }
        .summary-item {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-indigo {
            color: #4f46e5;
        }
        .text-green {
            color: #16a34a;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Investor Profit Analysis</h1>
    <p class="text-center">Generated on: {{ now()->format('F d, Y h:i A') }}</p>

    @foreach($rates as $index => $rate)
        @php
            $groupLoans = $loanGroups[$rate] ?? collect();
            $groupSummary = $summary["rate_{$rate}"] ?? null;
        @endphp

        <h2>{{ $rate }}% Interest Rate Loans</h2>
        <div class="summary-box">
            <div class="summary-item"><strong>Total Interest Generated:</strong> {{ number_format($groupSummary['total_interest'], 2) }}</div>
            <div class="summary-item text-indigo"><strong>Investor 1 Profit ({{ $groupSummary['investor1_rate'] }}%):</strong> {{ number_format($groupSummary['investor1'], 2) }}</div>
            <div class="summary-item text-green"><strong>Investor 2 Profit ({{ $groupSummary['investor2_rate'] }}%):</strong> {{ number_format($groupSummary['investor2'], 2) }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Principal</th>
                    <th>Term</th>
                    <th>Monthly Profit</th>
                    <th>Total Profit</th>
                    <th>Total Return</th>
                    <th>Inv 1 ({{ $groupSummary['investor1_rate'] }}%)</th>
                    <th>Inv 2 ({{ $groupSummary['investor2_rate'] }}%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupLoans as $loan)
                    <tr>
                        <td>#{{ $loan->id }}</td>
                        <td>{{ number_format($loan->amount, 2) }}</td>
                        <td>{{ $loan->payment_term }} mo</td>
                        <td>{{ number_format($loan->amount * ($rate / 100), 2) }}</td>
                        <td>{{ number_format($loan->interest_amount, 2) }} ({{ $loan->payment_term * $rate }}%)</td>
                        <td>{{ number_format($loan->total_payable, 2) }}</td>
                        <td class="text-indigo">{{ number_format($loan->investor1_interest, 2) }}</td>
                        <td class="text-green">{{ number_format($loan->investor2_interest, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No {{ $rate }}% loans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($index < count($rates) - 1)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

</body>
</html>
