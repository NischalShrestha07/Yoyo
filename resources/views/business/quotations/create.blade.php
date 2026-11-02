@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header">Create Quotation</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.quotations.store', $business) }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">Walk-in</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valid Until</label>
                    <input type="date" name="valid_until" class="form-control">
                </div>
            </div>
            <table class="table" id="itemsTable">
                <thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td><select name="items[0][product_id]" class="form-select" required onchange="setPrice(this, 0)">@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }}</option>@endforeach</select></td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_price]" id="price0" class="form-control" value="0" required></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-secondary mb-2" onclick="addItem()">Add Item</button>
            <div class="row mb-2">
                <div class="col-md-6"><label class="form-label">Discount Amount</label><input type="number" step="0.01" name="discount_amount" class="form-control" value="0"></div>
            </div>
            <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
            <button class="btn btn-primary">Create Quotation</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let idx = 1;
function setPrice(sel, i) {
    const price = sel.options[sel.selectedIndex].dataset.price || 0;
    document.getElementById('price'+i).value = price;
}
function addItem() {
    const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    row.innerHTML = `<td><select name="items[${idx}][product_id]" class="form-select" required onchange="setPrice(this, ${idx})">@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }}</option>@endforeach</select></td><td><input type="number" name="items[${idx}][quantity]" class="form-control" value="1" required></td><td><input type="number" step="0.01" name="items[${idx}][unit_price]" id="price${idx}" class="form-control" value="0" required></td><td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>`;
    idx++;
}
</script>
@endpush
