<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Methods Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #6f42c1; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Methods Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Payment Methods Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_methods']) }}</div>
                <div class="summary-label">Total Methods</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_transactions']) }}</div>
                <div class="summary-label">Total Transactions</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_amount'], 2) }}</div>
                <div class="summary-label">Total Amount</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $summary['most_used'] ? ucwords(str_replace('_', ' ', $summary['most_used']['payment_method'])) : 'N/A' }}</div>
                <div class="summary-label">Most Used</div>
            </div>
        </div>
    </div>

    <h4>Payment Methods Details</h4>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th>Transaction Count</th>
                <th>Total Amount</th>
                <th>Average Amount</th>
                <th>Order Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = collect($payment_methods)->sum('total_amount'); @endphp
            @foreach($payment_methods as $method)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $method['payment_method'])) }}</td>
                <td>{{ number_format($method['transaction_count']) }}</td>
                <td>${{ number_format($method['total_amount'], 2) }}</td>
                <td>${{ number_format($method['average_amount'], 2) }}</td>
                <td>{{ number_format($method['order_count']) }}</td>
                <td>{{ $totalAmount > 0 ? number_format(($method['total_amount'] / $totalAmount) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>