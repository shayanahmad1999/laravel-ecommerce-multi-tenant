<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Category Performance Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #17a2b8; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .total-row { background: #e9ecef; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Category Performance Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Category Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_categories']) }}</div>
                <div class="summary-label">Total Categories</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_sold']) }}</div>
                <div class="summary-label">Total Items Sold</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $summary['best_category'] ? $summary['best_category']['name'] : 'N/A' }}</div>
                <div class="summary-label">Best Category</div>
            </div>
        </div>
    </div>

    <h4>Category Performance Details</h4>
    <table>
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Items Sold</th>
                <th>Total Revenue</th>
                <th>Product Count</th>
                <th>Order Count</th>
                <th>Avg Order Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $category['name'] }}</td>
                <td>{{ number_format($category['total_sold']) }}</td>
                <td>${{ number_format($category['total_revenue'], 2) }}</td>
                <td>{{ number_format($category['product_count']) }}</td>
                <td>{{ number_format($category['order_count']) }}</td>
                <td>${{ $category['order_count'] > 0 ? number_format($category['total_revenue'] / $category['order_count'], 2) : '0.00' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>