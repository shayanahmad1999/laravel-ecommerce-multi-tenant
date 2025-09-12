<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 24px; font-weight: bold; color: #007bff; }
        .summary-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .total-row { background: #e9ecef; font-weight: bold; }
        .status-pending { color: #ffc107; }
        .status-processing { color: #17a2b8; }
        .status-shipped { color: #007bff; }
        .status-delivered { color: #28a745; }
        .status-cancelled { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Report Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_orders']) }}</div>
                <div class="summary-label">Total Orders</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_items']) }}</div>
                <div class="summary-label">Items Sold</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['average_order_value'], 2) }}</div>
                <div class="summary-label">Avg Order Value</div>
            </div>
        </div>
    </div>

    <h4>Sales Details</h4>
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td>{{ $order->orderItems->sum('quantity') }}</td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
                <td class="status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3"><strong>TOTALS</strong></td>
                <td><strong>{{ $summary['total_items'] }}</strong></td>
                <td><strong>${{ number_format($summary['total_revenue'], 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>