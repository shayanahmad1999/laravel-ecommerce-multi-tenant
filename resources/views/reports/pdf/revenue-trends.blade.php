<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Trends Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #20c997; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .growth-positive { color: #28a745; }
        .growth-negative { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Revenue Trends Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Period Type: {{ ucfirst($period['type']) }} | Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Revenue Trends Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_periods']) }}</div>
                <div class="summary-label">Total Periods</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_orders']) }}</div>
                <div class="summary-label">Total Orders</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['average_revenue_per_period'], 2) }}</div>
                <div class="summary-label">Avg Revenue/Period</div>
            </div>
        </div>
    </div>

    <h4>Revenue Trends Details</h4>
    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th>Order Count</th>
                <th>Revenue</th>
                <th>Avg Order Value</th>
                <th>Growth %</th>
            </tr>
        </thead>
        <tbody>
            @php $previousRevenue = 0; @endphp
            @foreach($trends as $index => $trend)
            <tr>
                <td>{{ $trend['period'] }}</td>
                <td>{{ number_format($trend['order_count']) }}</td>
                <td>${{ number_format($trend['revenue'], 2) }}</td>
                <td>${{ number_format($trend['avg_order_value'], 2) }}</td>
                <td>
                    @php
                        $growth = $index > 0 && $previousRevenue > 0 ? (($trend['revenue'] - $previousRevenue) / $previousRevenue) * 100 : 0;
                        $previousRevenue = $trend['revenue'];
                    @endphp
                    <span class="{{ $growth >= 0 ? 'growth-positive' : 'growth-negative' }}">
                        {{ $growth >= 0 ? '↑' : '↓' }} {{ number_format(abs($growth), 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
        <p><strong>Best Period:</strong> {{ $summary['best_period'] ? $summary['best_period']['period'] . ' ($' . number_format($summary['best_period']['revenue'], 2) . ')' : 'N/A' }}</p>
    </div>
</body>
</html>