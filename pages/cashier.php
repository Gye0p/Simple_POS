<?php
$pageTitle = 'Cashier';
$pageScripts = ['pos.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="pos-layout" id="posApp" data-base-url="<?= htmlspecialchars($base) ?>">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card pos-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-upc-scan"></i> Scan Items</h6>
                    <small class="text-muted">Scanner ready — scan barcode or type + Enter</small>
                </div>
                <div class="card-body">
                    <input
                        type="text"
                        id="barcodeInput"
                        class="form-control form-control-lg barcode-input"
                        placeholder="Scan barcode here..."
                        autocomplete="off"
                    >

                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center" style="width: 140px;">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center" style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody"></tbody>
                        </table>
                        <div id="cartEmpty" class="text-center text-muted py-5">
                            <i class="bi bi-cart-x fs-1"></i>
                            <p class="mb-0 mt-2">Cart is empty. Scan a product to begin.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card pos-card pos-summary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-calculator"></i> Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotalDisplay">₱0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Discount</label>
                        <div class="input-group">
                            <select class="form-select" id="discountType" style="max-width: 110px;">
                                <option value="amount">₱ Peso</option>
                                <option value="percent">% Percent</option>
                            </select>
                            <input type="number" class="form-control" id="discountValue" value="0" min="0" step="0.01">
                        </div>
                        <div class="summary-row mt-1 text-danger">
                            <span>Discount</span>
                            <span id="discountDisplay">-₱0.00</span>
                        </div>
                    </div>

                    <hr>

                    <div class="summary-row total-row">
                        <span>TOTAL</span>
                        <span id="totalDisplay">₱0.00</span>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="cashTendered" class="form-label">Cash Tendered</label>
                        <input type="number" class="form-control form-control-lg" id="cashTendered" min="0" step="0.01" placeholder="0.00">
                    </div>

                    <div class="summary-row change-row">
                        <span>Change</span>
                        <span id="changeDisplay" class="text-success">₱0.00</span>
                    </div>

                    <button type="button" class="btn btn-success btn-lg w-100 mt-3" id="btnCheckout" disabled>
                        <i class="bi bi-check-circle"></i> Checkout
                    </button>
                    <button type="button" class="btn btn-outline-danger w-100 mt-2" id="btnClearCart">
                        <i class="bi bi-trash"></i> Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="receiptFrame" title="Receipt" style="width:100%; height:420px; border:none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnPrintReceipt">
                    <i class="bi bi-printer"></i> Print Again
                </button>
                <button type="button" class="btn btn-primary" id="btnNewSale" data-bs-dismiss="modal">
                    New Sale
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
