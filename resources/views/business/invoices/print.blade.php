<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #1e40af; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 4px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f8f9fa; padding: 10px; text-align: left; border: 1px solid #dee2e6; }
        .items-table td { padding: 10px; border: 1px solid #dee2e6; }
        .totals { margin-left: auto; width: 300px; }
        .totals td { padding: 6px 0; }
        .totals .total { font-size: 16px; font-weight: bold; border-top: 2px solid #333; padding-top: 10px; }
        @media print { body { padding: 20px; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div>
            <h1 style="margin-bottom: 10px;">{{ $sale->business->name }}</h1>
            <p>{{ $sale->business->email }}</p>
            <p>{{ $sale->business->phone }}</p>
        </div>
        <div style="text-align: right;">
            <div class="invoice-title">INVOICE</div>
            <p style="margin-top: 10px;"><strong>Invoice No:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $sale->created_at->format('d M Y') }}</p>
            @if($sale->due_date)<p><strong>Due Date:</strong> {{ $sale->due_date->format('d M Y') }}</p>@endif
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Bill To:</strong><br>
                {{ $sale->customer->full_name ?? 'Walk-in Customer' }}<br>
                @if($sale->customer->email){{ $sale->customer->email }}<br>@endif
                @if($sale->customer->phone){{ $sale->customer->phone }}@endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal:</td><td style="text-align: right;">{{ number_format($sale->subtotal, 2) }}</td></tr>
        @if($sale->discount_amount > 0)<tr><td>Discount:</td><td style="text-align: right;">-{{ number_format($sale->discount_amount, 2) }}</td></tr>@endif
        @if($sale->tax_amount > 0)<tr><td>Tax:</td><td style="text-align: right;">{{ number_format($sale->tax_amount, 2) }}</td></tr>@endif
        <tr class="total"><td>Total:</td><td style="text-align: right;">{{ number_format($sale->total_amount, 2) }}</td></tr>
        <tr><td>Amount Paid:</td><td style="text-align: right;">{{ number_format($sale->amount_paid, 2) }}</td></tr>
        <tr><td>Balance Due:</td><td style="text-align: right;">{{ number_format($sale->amount_due, 2) }}</td></tr>
    </table>

    <div style="margin-top: 60px; text-align: center; color: #666;">
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
