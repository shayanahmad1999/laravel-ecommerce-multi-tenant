<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - {{ now()->format('M d, Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 24px; font-weight: bold; color: #28a745; }
        .summary-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f8f9fa; font-weight: bold; }
        .total-row { background: #e9ecef; font-weight: bold; }
        .status-good { color: #28a745; }
        .status-low { color: #ffc107; }
        .status-out { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <h3>As of: {{ now()->format('M d, Y H:i') }}</h3>
    </div>

    <div class="summary">
        <h4>Inventory Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_products']) }}</div>
                <div class="summary-label">Total Products</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['total_value'], 2) }}</div>
                <div class="summary-label">Total Value</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['low_stock']) }}</div>
                <div class="summary-label">Low Stock Items</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['out_of_stock']) }}</div>
                <div class="summary-label">Out of Stock</div>
            </div>
        </div>
    </div>

    <h4>Product Inventory Details</h4>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Stock Qty</th>
                <th>Min Level</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Inventory Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->sku ?? 'N/A' }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>{{ $product->stock_quantity }}</td>
                <td>{{ $product->min_stock_level }}</td>
                <td>${{ number_format($product->cost_price, 2) }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>${{ number_format($product->stock_quantity * $product->cost_price, 2) }}</td>
                <td class="status-{{ $product->stock_quantity <= $product->min_stock_level ? ($product->stock_quantity == 0 ? 'out' : 'low') : 'good' }}">
                    @if($product->stock_quantity == 0)
                        Out of Stock
                    @elseif($product->stock_quantity <= $product->min_stock_level)
                        Low Stock
                    @else
                        In Stock
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7"><strong>TOTAL INVENTORY VALUE</strong></td>
                <td colspan="2"><strong>${{ number_format($summary['total_value'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>