<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Performance Report - {{ $period['from'] }} to {{ $period['to'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #28a745; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .total-row { background: #e9ecef; font-weight: bold; }
        .profit-positive { color: #28a745; }
        .profit-negative { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Performance Report</h1>
        <h3>Period: {{ \Carbon\Carbon::parse($period['from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($period['to'])->format('M d, Y') }}</h3>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Performance Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_products']) }}</div>
                <div class="summary-label">Total Products</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_profit'], 2) }}</div>
                <div class="summary-label">Total Profit</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['average_profit_margin'], 2) }}%</div>
                <div class="summary-label">Avg Profit Margin</div>
            </div>
        </div>
    </div>

    <h4>Product Performance Details</h4>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Price</th>
                <th>Cost</th>
                <th>Units Sold</th>
                <th>Total Revenue</th>
                <th>Total Cost</th>
                <th>Profit</th>
                <th>Profit Margin</th>
                <th>Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['sku'] }}</td>
                <td>{{ $product['category'] }}</td>
                <td>${{ number_format($product['price'], 2) }}</td>
                <td>${{ number_format($product['cost_price'], 2) }}</td>
                <td>{{ number_format($product['total_sold']) }}</td>
                <td>${{ number_format($product['total_revenue'], 2) }}</td>
                <td>${{ number_format($product['total_cost'], 2) }}</td>
                <td class="{{ $product['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    ${{ number_format($product['profit'], 2) }}
                </td>
                <td class="{{ $product['profit_margin'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    {{ number_format($product['profit_margin'], 2) }}%
                </td>
                <td>{{ number_format($product['order_count']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>