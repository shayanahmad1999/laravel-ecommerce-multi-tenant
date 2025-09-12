<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 20px; font-weight: bold; color: #ffc107; }
        .summary-label { font-size: 11px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f8f9fa; font-weight: bold; }
        .total-row { background: #e9ecef; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customers Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Customer Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_customers']) }}</div>
                <div class="summary-label">Total Customers</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['active_customers']) }}</div>
                <div class="summary-label">Active Customers</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['average_order_value'], 2) }}</div>
                <div class="summary-label">Avg Order Value</div>
            </div>
        </div>
    </div>

    <h4>Customer Details</h4>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Total Orders</th>
                <th>Total Spent</th>
                <th>Last Order Date</th>
                <th>Avg Order Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer['name'] }}</td>
                <td>{{ $customer['email'] }}</td>
                <td>{{ $customer['phone'] ?? 'N/A' }}</td>
                <td>{{ $customer['total_orders'] }}</td>
                <td>${{ number_format($customer['total_spent'], 2) }}</td>
                <td>{{ $customer['last_order_date'] ? \Carbon\Carbon::parse($customer['last_order_date'])->format('M d, Y') : 'N/A' }}</td>
                <td>${{ $customer['total_orders'] > 0 ? number_format($customer['total_spent'] / $customer['total_orders'], 2) : '0.00' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4"><strong>TOTALS</strong></td>
                <td><strong>${{ number_format($summary['total_revenue'], 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>