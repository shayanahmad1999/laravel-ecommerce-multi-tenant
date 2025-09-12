<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert Report - {{ now()->format('M d, Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #dc3545; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .status-low { color: #ffc107; }
        .status-out { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Low Stock Alert Report</h1>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Stock Alert Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_low_stock']) }}</div>
                <div class="summary-label">Low Stock Items</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_out_of_stock']) }}</div>
                <div class="summary-label">Out of Stock Items</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_value_at_risk'], 2) }}</div>
                <div class="summary-label">Value at Risk</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_low_stock'] + $summary['total_out_of_stock']) }}</div>
                <div class="summary-label">Total Items</div>
            </div>
        </div>
    </div>

    <h4>Low Stock Products</h4>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Min Stock Level</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Stock Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['sku'] ?? 'N/A' }}</td>
                <td>{{ $product['category'] ? $product['category']['name'] : 'N/A' }}</td>
                <td>{{ $product['stock_quantity'] }}</td>
                <td>{{ $product['min_stock_level'] }}</td>
                <td>${{ number_format($product['cost_price'], 2) }}</td>
                <td>${{ number_format($product['price'], 2) }}</td>
                <td>${{ number_format($product['stock_quantity'] * $product['cost_price'], 2) }}</td>
                <td class="{{ $product['stock_quantity'] === 0 ? 'status-out' : 'status-low' }}">
                    {{ $product['stock_quantity'] === 0 ? 'Out of Stock' : 'Low Stock' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
        <p><strong>Action Required:</strong> Please restock the items listed above to avoid sales interruptions.</p>
    </div>
</body>
</html>