@extends('layouts.app')

@section('title', 'Add Bill')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-semibold">Add Bill</h4>
    </div>
    <a href="{{ route('business.bills.index', $business) }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('business.bills.store', $business) }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Vendor *</label>
                    <select name="vendor_id" class="form-select" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill Date *</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>
            </div>
            <table class="table" id="itemsTable">
                <thead><tr><th>Item</th><th width="100">Qty</th><th width="150">Price</th><th width="50"></th></tr></thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="items[0][item_name]" class="form-control" required></td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control" required></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-light mb-3" onclick="addItem()">Add Item</button>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            <button class="btn btn-primary">Create Bill</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let idx=1;
function addItem(){
    const tbody=document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const row=tbody.insertRow();
    row.innerHTML=`<td><input type="text" name="items[${idx}][item_name]" class="form-control" required></td><td><input type="number" name="items[${idx}][quantity]" class="form-control" value="1" required></td><td><input type="number" step="0.01" name="items[${idx}][unit_price]" class="form-control" required></td><td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>`;
    idx++;
}
</script>
@endpush
