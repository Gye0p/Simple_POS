(function () {
    'use strict';

    const posApp = document.getElementById('posApp');
    const baseUrl = posApp ? posApp.dataset.baseUrl : '';
    let cart = [];
    let settings = {
        low_stock_threshold: 5,
    };

    const els = {
        barcodeInput: document.getElementById('barcodeInput'),
        cartBody: document.getElementById('cartBody'),
        cartEmpty: document.getElementById('cartEmpty'),
        subtotalDisplay: document.getElementById('subtotalDisplay'),
        discountDisplay: document.getElementById('discountDisplay'),
        totalDisplay: document.getElementById('totalDisplay'),
        changeDisplay: document.getElementById('changeDisplay'),
        discountValue: document.getElementById('discountValue'),
        discountType: document.getElementById('discountType'),
        cashTendered: document.getElementById('cashTendered'),
        btnCheckout: document.getElementById('btnCheckout'),
        btnClearCart: document.getElementById('btnClearCart'),
        receiptFrame: document.getElementById('receiptFrame'),
        receiptModal: document.getElementById('receiptModal'),
    };

    function formatMoney(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function loadSettings() {
        return fetch(baseUrl + '/ajax/get_settings.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    settings = data.settings;
                }
            })
            .catch(function () {});
    }

    function getDiscountAmount(subtotal) {
        const value = parseFloat(els.discountValue.value) || 0;
        if (els.discountType.value === 'percent') {
            return Math.round(subtotal * Math.min(value, 100) / 100 * 100) / 100;
        }
        return Math.min(value, subtotal);
    }

    function calculateTotals() {
        const subtotal = cart.reduce(function (sum, item) {
            return sum + item.price * item.qty;
        }, 0);

        const discount = getDiscountAmount(subtotal);
        const total = Math.round(Math.max(subtotal - discount, 0) * 100) / 100;
        const cash = parseFloat(els.cashTendered.value) || 0;
        const change = cash >= total ? Math.round((cash - total) * 100) / 100 : 0;

        els.subtotalDisplay.textContent = formatMoney(subtotal);
        els.discountDisplay.textContent = '-' + formatMoney(discount);
        els.totalDisplay.textContent = formatMoney(total);
        els.changeDisplay.textContent = formatMoney(change);

        els.btnCheckout.disabled = cart.length === 0 || cash < total;

        return { subtotal, discount, total, cash, change };
    }

    function renderCart() {
        if (cart.length === 0) {
            els.cartBody.innerHTML = '';
            els.cartEmpty.classList.remove('d-none');
            calculateTotals();
            return;
        }

        els.cartEmpty.classList.add('d-none');

        els.cartBody.innerHTML = cart.map(function (item, index) {
            const lineTotal = item.price * item.qty;
            const lowStockBadge = item.low_stock
                ? '<span class="badge bg-warning text-dark ms-1">Low Stock</span>'
                : '';

            return (
                '<tr data-index="' + index + '">' +
                    '<td>' +
                        '<div class="fw-semibold">' + escapeHtml(item.name) + lowStockBadge + '</div>' +
                        '<small class="text-muted">' + escapeHtml(item.barcode) + '</small>' +
                    '</td>' +
                    '<td class="text-end">' + formatMoney(item.price) + '</td>' +
                    '<td class="text-center">' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-outline-secondary btn-qty-minus" data-index="' + index + '">-</button>' +
                            '<span class="btn btn-light disabled px-3">' + item.qty + '</span>' +
                            '<button type="button" class="btn btn-outline-secondary btn-qty-plus" data-index="' + index + '">+</button>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-end fw-semibold">' + formatMoney(lineTotal) + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-index="' + index + '">' +
                            '<i class="bi bi-trash"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );
        }).join('');

        calculateTotals();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addToCart(product) {
        const existing = cart.find(function (item) { return item.id === product.id; });

        if (existing) {
            if (existing.qty >= product.stock) {
                Swal.fire({ icon: 'warning', title: 'Stock limit', text: 'Cannot add more than available stock (' + product.stock + ')' });
                return;
            }
            existing.qty += 1;
            existing.low_stock = product.low_stock;
            existing.stock = product.stock;
        } else {
            cart.push({
                id: product.id,
                barcode: product.barcode,
                name: product.name,
                price: product.price,
                qty: 1,
                stock: product.stock,
                unit: product.unit,
                low_stock: product.low_stock,
            });
        }

        renderCart();
    }

    function scanBarcode(barcode) {
        if (!barcode) return;

        fetch(baseUrl + '/ajax/scan.php?barcode=' + encodeURIComponent(barcode))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    addToCart(data.product);
                } else {
                    Swal.fire({ icon: 'error', title: 'Scan failed', text: data.message });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to scan product' });
            })
            .finally(function () {
                els.barcodeInput.value = '';
                els.barcodeInput.focus();
            });
    }

    function showReceipt(saleId) {
        els.receiptFrame.src = baseUrl + '/receipt/print.php?sale_id=' + saleId;

        const modal = new bootstrap.Modal(els.receiptModal);
        modal.show();

        els.receiptFrame.onload = function () {
            setTimeout(function () {
                try {
                    els.receiptFrame.contentWindow.focus();
                    els.receiptFrame.contentWindow.print();
                } catch (e) {
                    window.print();
                }
            }, 500);
        };
    }

    function checkout() {
        const totals = calculateTotals();

        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Empty cart', text: 'Scan items before checkout' });
            return;
        }

        if (totals.cash < totals.total) {
            Swal.fire({ icon: 'warning', title: 'Insufficient cash', text: 'Cash tendered is less than total' });
            return;
        }

        els.btnCheckout.disabled = true;

        fetch(baseUrl + '/ajax/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: cart.map(function (item) {
                    return { id: item.id, qty: item.qty, price: item.price };
                }),
                discount_type: els.discountType.value,
                discount_value: parseFloat(els.discountValue.value) || 0,
                cash_tendered: totals.cash,
            }),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale complete',
                        text: 'OR# ' + data.or_number,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    cart = [];
                    els.discountValue.value = '0';
                    els.cashTendered.value = '';
                    renderCart();
                    showReceipt(data.sale_id);
                } else {
                    Swal.fire({ icon: 'error', title: 'Checkout failed', text: data.message });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Checkout request failed' });
            })
            .finally(function () {
                els.btnCheckout.disabled = false;
                els.barcodeInput.focus();
            });
    }

    els.barcodeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            scanBarcode(this.value.trim());
        }
    });

    els.cartBody.addEventListener('click', function (e) {
        const minusBtn = e.target.closest('.btn-qty-minus');
        const plusBtn = e.target.closest('.btn-qty-plus');
        const removeBtn = e.target.closest('.btn-remove');

        if (minusBtn) {
            const index = parseInt(minusBtn.dataset.index, 10);
            if (cart[index].qty > 1) {
                cart[index].qty -= 1;
            } else {
                cart.splice(index, 1);
            }
            renderCart();
        }

        if (plusBtn) {
            const index = parseInt(plusBtn.dataset.index, 10);
            if (cart[index].qty >= cart[index].stock) {
                Swal.fire({ icon: 'warning', title: 'Stock limit', text: 'Maximum stock reached (' + cart[index].stock + ')' });
                return;
            }
            cart[index].qty += 1;
            renderCart();
        }

        if (removeBtn) {
            const index = parseInt(removeBtn.dataset.index, 10);
            cart.splice(index, 1);
            renderCart();
        }
    });

    els.discountValue.addEventListener('input', calculateTotals);
    els.discountType.addEventListener('change', calculateTotals);
    els.cashTendered.addEventListener('input', calculateTotals);

    els.btnCheckout.addEventListener('click', checkout);

    els.btnClearCart.addEventListener('click', function () {
        if (cart.length === 0) return;

        Swal.fire({
            title: 'Clear cart?',
            text: 'All items will be removed',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, clear it',
        }).then(function (result) {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
                els.barcodeInput.focus();
            }
        });
    });

    document.getElementById('btnPrintReceipt').addEventListener('click', function () {
        try {
            els.receiptFrame.contentWindow.focus();
            els.receiptFrame.contentWindow.print();
        } catch (e) {
            window.print();
        }
    });

    document.getElementById('btnNewSale').addEventListener('click', function () {
        bootstrap.Modal.getInstance(els.receiptModal).hide();
        els.barcodeInput.focus();
    });

    loadSettings().then(function () {
        renderCart();
        els.barcodeInput.focus();
    });

    document.addEventListener('click', function () {
        if (!els.receiptModal.classList.contains('show')) {
            els.barcodeInput.focus();
        }
    });
})();
