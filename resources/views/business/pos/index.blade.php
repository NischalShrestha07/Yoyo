@extends('layouts.app')

@section('title', 'POS Terminal - '.$business->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-cart-check text-primary"></i> POS Terminal</h4>
            <small class="text-muted">{{ $business->name }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('business.sales.index', $business) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt"></i> Sales History</a>
        <a href="{{ route('business.customers.index', $business) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-people"></i> Customers</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7 col-xl-8">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid text-primary"></i>
                    <span class="fw-semibold">Products</span>
                    <span class="badge bg-light text-dark">{{ count($products) }}</span>
                </div>
                <div class="input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="productSearch" class="form-control border-start-0" placeholder="Search by name, SKU, or barcode...">
                </div>
            </div>
            <div class="card-body p-3" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="row g-2" id="productGrid">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4 col-xl-3 product-card" data-name="{{ strtolower($product->name) }}" data-sku="{{ strtolower($product->sku) }}" data-barcode="{{ strtolower($product->barcode ?? '') }}">
                            <button class="btn btn-light border w-100 text-start add-product h-100 p-2" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-sku="{{ $product->sku }}">
                                <div class="d-flex flex-column h-100">
                                    <div class="fw-semibold text-truncate mb-1" style="font-size: 0.875rem;">{{ $product->name }}</div>
                                    <small class="text-muted mb-1">{{ $product->sku }}</small>
                                    <div class="mt-auto fw-bold text-primary">{{ number_format($product->selling_price, 2) }}</div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 col-xl-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cart3 text-primary"></i>
                    <span class="fw-semibold">Current Order</span>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display:none;"><i class="bi bi-trash"></i> Clear</button>
            </div>
            <div class="card-body p-2" style="max-height: 280px; overflow-y: auto;">
                <div id="cartItems"></div>
                <div id="emptyCart" class="text-center text-muted py-4">
                    <i class="bi bi-cart display-6"></i>
                    <p class="mb-0 mt-2 small">Cart is empty</p>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between mb-1"><span class="text-muted small">Subtotal</span><span class="small" id="subtotal">0.00</span></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted small">Discount</span><span class="small text-danger" id="discount">-0.00</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Tax ({{ number_format($taxRate, 1) }}%)</span><span class="small" id="tax">0.00</span></div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fw-semibold">Total Amount</span>
                    <span class="fw-bold fs-4 text-primary" id="total">0.00</span>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card text-primary"></i>
                    <span class="fw-semibold">Payment Details</span>
                </div>
            </div>
            <div class="card-body p-3">
                <form id="checkoutForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small mb-1">Customer</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Stock Location</label>
                        <select name="stock_location_id" class="form-select form-select-sm">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Discount</label>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Amount Paid</label>
                            <input type="number" step="0.01" name="amount_paid" id="amountPaid" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2" id="checkoutBtn">
                        <i class="bi bi-check-circle"></i> Complete Sale (F12)
                    </button>
                </form>
                <div id="posMessage" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<style>
.add-product:hover {
    background-color: #eff6ff !important;
    border-color: #2563eb !important;
    color: #1e40af;
}
.product-card button:active {
    transform: scale(0.98);
}
</style>
@endsection

@push('scripts')
<script>
const taxRate = {{ $taxRate }} / 100;
let cart = [];
const businessId = {{ $business->id }};

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const clearCartBtn = document.getElementById('clearCartBtn');
    cartItems.innerHTML = '';
    if (cart.length === 0) {
        emptyCart.style.display = 'block';
        clearCartBtn.style.display = 'none';
    } else {
        emptyCart.style.display = 'none';
        clearCartBtn.style.display = 'inline-block';
    }
    let subtotal = 0;
    cart.forEach((item, idx) => {
        subtotal += item.price * item.qty;
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded';
        div.innerHTML = `<div class="flex-grow-1 me-2"><div class="fw-semibold small text-truncate" style="max-width: 140px;">${item.name}</div><small class="text-muted">${item.price.toFixed(2)} × ${item.qty}</small></div>
        <div class="d-flex align-items-center gap-1">
            <button class="btn btn-sm btn-outline-secondary p-0" style="width: 22px; height: 22px;" onclick="changeQty(${idx}, -1)"><i class="bi bi-dash"></i></button>
            <span class="small fw-semibold" style="min-width: 20px; text-align: center;">${item.qty}</span>
            <button class="btn btn-sm btn-outline-secondary p-0" style="width: 22px; height: 22px;" onclick="changeQty(${idx}, 1)"><i class="bi bi-plus"></i></button>
            <span class="fw-semibold small ms-2">${(item.price*item.qty).toFixed(2)}</span>
        </div>`;
        cartItems.appendChild(div);
    });
    const discount = parseFloat(document.getElementById('discountInput').value || 0);
    const taxable = Math.max(0, subtotal - discount);
    const tax = taxable * taxRate;
    const total = taxable + tax;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('discount').textContent = '-' + discount.toFixed(2);
    document.getElementById('tax').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
    if ((document.getElementById('amountPaid').value == '0' || document.activeElement?.id !== 'amountPaid') && total > 0) {
        document.getElementById('amountPaid').value = total.toFixed(2);
    }
}

window.clearCart = function() {
    cart = [];
    renderCart();
};

window.changeQty = function(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
};

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
    if (cart.length === 0) {
        document.getElementById('posMessage').innerHTML = '<div class="alert alert-warning py-2">Cart is empty</div>';
        return;
    }
    const formData = new FormData(this);
    const items = cart.map(c => ({product_id: c.id, quantity: c.qty}));
    const payload = Object.fromEntries(formData.entries());
    payload.items = items;
    payload.amount_paid = parseFloat(payload.amount_paid || 0);
    payload.discount_amount = parseFloat(payload.discount_amount || 0);
    
    const msg = document.getElementById('posMessage');
    const btn = document.getElementById('checkoutBtn');
    msg.innerHTML = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
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
            msg.innerHTML = '<div class="alert alert-success py-2">Sale completed! <a href="{{ route('business.pos.receipt', [$business, ':id']) }}'.replace(':id', data.sale.id) + '" class="alert-link">Print Receipt</a></div>';
            cart = [];
            renderCart();
            document.getElementById('discountInput').value = 0;
            document.getElementById('amountPaid').value = 0;
        } else if (data.errors) {
            msg.innerHTML = '<div class="alert alert-danger py-2">' + Object.values(data.errors).flat().join('<br>') + '</div>';
        } else {
            msg.innerHTML = '<div class="alert alert-danger py-2">' + (data.message || 'Checkout failed') + '</div>';
        }
    } catch (err) {
        msg.innerHTML = '<div class="alert alert-danger py-2">Checkout failed</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Complete Sale (F12)';
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'F12') {
        e.preventDefault();
        document.getElementById('checkoutForm').dispatchEvent(new Event('submit'));
    }
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('productSearch').focus();
    }
});

renderCart();
</script>
@endpush
