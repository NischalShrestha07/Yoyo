@extends('layouts.app')

@section('title', 'POS - '.$business->name)

@section('content')
@include('business._nav')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-grid"></i> Products</span>
                <div class="input-group input-group-sm w-50">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="productSearch" class="form-control" placeholder="Search by name, SKU, barcode">
                </div>
            </div>
            <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row g-2" id="productGrid">
                    @foreach($products as $product)
                        <div class="col-md-3 col-sm-4 product-card" data-name="{{ strtolower($product->name) }}" data-sku="{{ strtolower($product->sku) }}" data-barcode="{{ strtolower($product->barcode ?? '') }}">
                            <button class="btn btn-outline-primary w-100 text-start add-product h-100" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-sku="{{ $product->sku }}">
                                <div class="fw-bold text-truncate">{{ $product->name }}</div>
                                <small class="text-muted d-block">{{ $product->sku }}</small>
                                <div class="fw-bold text-primary mt-1">{{ number_format($product->selling_price, 2) }}</div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-cart3"></i> Cart</div>
            <div class="card-body" style="max-height: 35vh; overflow-y: auto;">
                <div id="cartItems"></div>
                <div id="emptyCart" class="text-center text-muted py-3">
                    <i class="bi bi-cart"></i><br>Cart is empty
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between"><span>Subtotal</span><span id="subtotal">0.00</span></div>
                <div class="d-flex justify-content-between"><span>Discount</span><span id="discount">0.00</span></div>
                <div class="d-flex justify-content-between"><span>Tax</span><span id="tax">0.00</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span id="total">0.00</span></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><i class="bi bi-credit-card"></i> Payment & Checkout</div>
            <div class="card-body">
                <form id="checkoutForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Location</label>
                        <select name="stock_location_id" class="form-select form-select-sm">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" name="amount_paid" id="amountPaid" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Complete Sale</button>
                </form>
                <div id="posMessage" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const taxRate = {{ $taxRate }} / 100;
let cart = [];
const businessId = {{ $business->id }};

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    cartItems.innerHTML = '';
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
    } else {
        emptyCart.style.display = 'none';
    }
    let subtotal = 0;
    cart.forEach((item, idx) => {
        subtotal += item.price * item.qty;
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between align-items-center mb-2 pb-2 border-bottom';
        div.innerHTML = `<div class="flex-grow-1"><div class="fw-bold small">${item.name}</div><small class="text-muted">${item.price.toFixed(2)} x ${item.qty} = ${(item.price*item.qty).toFixed(2)}</small></div>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" onclick="changeQty(${idx}, -1)"><i class="bi bi-dash"></i></button>
            <button class="btn btn-outline-secondary" disabled>${item.qty}</button>
            <button class="btn btn-outline-secondary" onclick="changeQty(${idx}, 1)"><i class="bi bi-plus"></i></button>
        </div>`;
        cartItems.appendChild(div);
    });
    const discount = parseFloat(document.getElementById('discountInput').value || 0);
    const taxable = Math.max(0, subtotal - discount);
    const tax = taxable * taxRate;
    const total = taxable + tax;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('discount').textContent = discount.toFixed(2);
    document.getElementById('tax').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
    if (document.getElementById('amountPaid').value == '0' || document.activeElement?.id !== 'amountPaid') {
        if (total > 0) {
            document.getElementById('amountPaid').value = total.toFixed(2);
        }
    }
}

window.changeQty = function(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
};

document.querySelectorAll('.add-product').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id);
        const existing = cart.find(c => c.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({id, name: btn.dataset.name, price: parseFloat(btn.dataset.price), qty: 1, sku: btn.dataset.sku});
        }
        renderCart();
    });
});

document.getElementById('discountInput').addEventListener('input', renderCart);
document.getElementById('productSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.dataset.name || '';
        const sku = card.dataset.sku || '';
        const barcode = card.dataset.barcode || '';
        card.style.display = (name.includes(term) || sku.includes(term) || barcode.includes(term)) ? '' : 'none';
    });
});

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const items = cart.map(c => ({product_id: c.id, quantity: c.qty}));
    const payload = Object.fromEntries(formData.entries());
    payload.items = items;
    payload.amount_paid = parseFloat(payload.amount_paid || 0);
    payload.discount_amount = parseFloat(payload.discount_amount || 0);
    
    const msg = document.getElementById('posMessage');
    msg.innerHTML = '';
    
    try {
        const res = await fetch(`{{ route('business.pos.checkout', $business) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok && data.success) {
            msg.innerHTML = '<div class="alert alert-success">Sale completed. <a href="{{ route('business.pos.receipt', [$business, ':id']) }}'.replace(':id', data.sale.id) + '">View Receipt</a></div>';
            cart = [];
            renderCart();
        } else if (data.errors) {
            msg.innerHTML = '<div class="alert alert-danger">' + Object.values(data.errors).flat().join('<br>') + '</div>';
        } else {
            msg.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Checkout failed') + '</div>';
        }
    } catch (err) {
        msg.innerHTML = '<div class="alert alert-danger">Checkout failed</div>';
    }
});

renderCart();
</script>
@endpush
