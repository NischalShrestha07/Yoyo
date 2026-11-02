<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: monospace; font-size: 12px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        table { width: 100%; }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <strong>{{ $business->name }}</strong><br>
        Receipt<br>
    </div>
    <hr>
    Invoice: {{ $sale->invoice_number }}<br>
    Date: {{ $sale->created_at }}<br>
    Cashier: {{ $sale->user->name ?? '-' }}<br>
    <hr>
    <table>
        @foreach($sale->items as $item)
        <tr>
            <td>{{ $item->product_name }} x{{ $item->quantity }}</td>
            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
        </tr>
        @endforeach
    </table>
    <hr>
    Subtotal: {{ number_format($sale->subtotal, 2) }}<br>
    Discount: {{ number_format($sale->discount_amount, 2) }}<br>
    Tax: {{ number_format($sale->tax_amount, 2) }}<br>
    <strong>Total: {{ number_format($sale->total_amount, 2) }}</strong><br>
    Paid: {{ number_format($sale->amount_paid, 2) }}<br>
    Due: {{ number_format($sale->amount_due, 2) }}<br>
</body>
</html>
