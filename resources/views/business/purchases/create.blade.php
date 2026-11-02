@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
@include('business._nav')
<div class="card">
    <div class="card-header">Create Purchase Order</div>
    <div class="card-body">
        <form method="POST" action="{{ route('business.purchases.store', $business) }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expected Date</label>
                    <input type="date" name="expected_date" class="form-control">
                </div>
            </div>
            <table class="table" id="itemsTable">
                <thead><tr><th>Product</th><th>Quantity</th><th>Unit Cost</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td><select name="items[0][product_id]" class="form-select" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_cost]" class="form-control" value="0" required></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-secondary mb-2" onclick="addItem()">Add Item</button>
            <div class="mb-2"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
            <button class="btn btn-primary">Create PO</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let idx = 1;
function addItem() {
    const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    row.innerHTML = `<td><select name="items[${idx}][product_id]" class="form-select" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></td><td><input type="number" name="items[${idx}][quantity]" class="form-control" value="1" required></td><td><input type="number" step="0.01" name="items[${idx}][unit_cost]" class="form-control" value="0" required></td><td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>`;
    idx++;
}
</script>
@endpush
