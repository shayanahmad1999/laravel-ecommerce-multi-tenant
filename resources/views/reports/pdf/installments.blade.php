<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Installments Report - {{ now()->format('M d, Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .summary { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .summary-grid { display: table; width: 100%; margin: 20px 0; }
        .summary-item { display: table-cell; padding: 10px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #fd7e14; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .status-pending { color: #ffc107; }
        .status-paid { color: #28a745; }
        .status-overdue { color: #dc3545; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Installments Report</h1>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <div class="summary">
        <h4>Installments Summary</h4>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['total_installments']) }}</div>
                <div class="summary-label">Total Installments</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['pending_amount'], 2) }}</div>
                <div class="summary-label">Pending Amount</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($summary['paid_amount'], 2) }}</div>
                <div class="summary-label">Paid Amount</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($summary['overdue_count']) }}</div>
                <div class="summary-label">Overdue Count</div>
            </div>
        </div>
    </div>

    <h4>Installment Details</h4>
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Installment #</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Days Overdue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($installments as $installment)
            <tr>
                <td>{{ $installment['order_number'] ?? 'N/A' }}</td>
                <td>{{ $installment['customer_name'] ?? 'N/A' }}</td>
                <td>{{ $installment['installment_number'] }}</td>
                <td>${{ number_format($installment['total_amount'], 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($installment['due_date'])->format('M d, Y') }}</td>
                <td class="status-{{ $installment['status'] }}">
                    @if($installment['status'] === 'pending' && \Carbon\Carbon::parse($installment['due_date'])->isPast())
                        Overdue
                    @else
                        {{ ucfirst($installment['status']) }}
                    @endif
                </td>
                <td>
                    @php
                        $dueDate = \Carbon\Carbon::parse($installment['due_date']);
                        $daysOverdue = $installment['status'] === 'pending' && $dueDate->isPast() ? $dueDate->diffInDays(now()) : 0;
                    @endphp
                    {{ $daysOverdue > 0 ? $daysOverdue . ' days' : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the E-commerce System</p>
    </div>
</body>
</html>