(function () {
    'use strict';

    const baseUrl = document.getElementById('productsApp').dataset.baseUrl;
    let productTable;
    let productModal;
    let stockModal;
    let productsCache = [];

    function loadProducts(categoryId) {
        let url = baseUrl + '/ajax/get_products.php';
        if (categoryId) {
            url += '?category_id=' + categoryId;
        }

        return fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message);
                }
                return data.products;
            });
    }

    function renderTable(products) {
        productsCache = products;

        if (productTable) {
            productTable.destroy();
        }

        const tbody = document.querySelector('#productsTable tbody');
        tbody.innerHTML = products.map(function (p) {
            const stockBadge = p.low_stock
                ? '<span class="badge bg-danger">' + p.stock + '</span>'
                : '<span class="badge bg-success">' + p.stock + '</span>';

            return (
                '<tr>' +
                    '<td><code>' + escapeHtml(p.barcode) + '</code></td>' +
                    '<td>' + escapeHtml(p.name) + '</td>' +
                    '<td>' + escapeHtml(p.category_name || '—') + '</td>' +
                    '<td class="text-end">' + formatMoney(p.price) + '</td>' +
                    '<td class="text-end">' + formatMoney(p.cost_price) + '</td>' +
                    '<td class="text-center">' + stockBadge + ' ' + escapeHtml(p.unit) + '</td>' +
                    '<td class="text-center">' +
                        (p.is_active
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>') +
                    '</td>' +
                    '<td class="text-center">' +
                        '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' + p.id + '">' +
                            '<i class="bi bi-pencil"></i>' +
                        '</button> ' +
                        '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + p.id + '" data-name="' + escapeHtml(p.name) + '">' +
                            '<i class="bi bi-trash"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );
        }).join('');

        productTable = $('#productsTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatMoney(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openProductModal(product) {
        document.getElementById('productId').value = product ? product.id : '';
        document.getElementById('productBarcode').value = product ? product.barcode : '';
        document.getElementById('productName').value = product ? product.name : '';
        document.getElementById('productCategory').value = product && product.category_id ? product.category_id : '';
        document.getElementById('productPrice').value = product ? product.price : '';
        document.getElementById('productCost').value = product ? product.cost_price : '';
        document.getElementById('productStock').value = product ? product.stock : '0';
        document.getElementById('productUnit').value = product ? product.unit : 'pcs';
        document.getElementById('productActive').checked = product ? product.is_active === 1 : true;
        document.getElementById('productModalTitle').textContent = product ? 'Edit Product' : 'Add Product';
        productModal.show();
    }

    function refresh() {
        const categoryId = document.getElementById('filterCategory').value;
        loadProducts(categoryId).then(renderTable);
    }

    document.getElementById('btnAddProduct').addEventListener('click', function () {
        openProductModal(null);
    });

    document.getElementById('productForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('is_active', document.getElementById('productActive').checked ? '1' : '0');

        fetch(baseUrl + '/ajax/save_product.php', { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    productModal.hide();
                    Swal.fire({ icon: 'success', title: 'Saved', text: data.message, timer: 1500, showConfirmButton: false });
                    refresh();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
    });

    document.querySelector('#productsTable').addEventListener('click', function (e) {
        const editBtn = e.target.closest('.btn-edit');
        const deleteBtn = e.target.closest('.btn-delete');

        if (editBtn) {
            const product = productsCache.find(function (item) {
                return item.id === parseInt(editBtn.dataset.id, 10);
            });
            if (product) {
                openProductModal(product);
            }
        }

        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const name = deleteBtn.dataset.name;
            Swal.fire({
                title: 'Delete product?',
                text: name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('id', id);
                    fetch(baseUrl + '/ajax/delete_product.php', { method: 'POST', body: fd })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                                refresh();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                            }
                        });
                }
            });
        }
    });

    document.getElementById('filterCategory').addEventListener('change', refresh);

    document.getElementById('btnBulkStock').addEventListener('click', function () {
        loadProducts(document.getElementById('filterCategory').value).then(function (products) {
            const tbody = document.querySelector('#stockTable tbody');
            tbody.innerHTML = products.map(function (p) {
                return (
                    '<tr>' +
                        '<td>' + escapeHtml(p.name) + '</td>' +
                        '<td class="text-center">' + p.stock + '</td>' +
                        '<td><input type="number" class="form-control form-control-sm stock-adjust" data-id="' + p.id + '" value="0"></td>' +
                    '</tr>'
                );
            }).join('');
            stockModal.show();
        });
    });

    document.getElementById('btnSaveStock').addEventListener('click', function () {
        const items = [];
        document.querySelectorAll('.stock-adjust').forEach(function (input) {
            const adjustment = parseInt(input.value, 10) || 0;
            if (adjustment !== 0) {
                items.push({ id: parseInt(input.dataset.id, 10), adjustment: adjustment });
            }
        });

        if (items.length === 0) {
            Swal.fire({ icon: 'info', title: 'No changes', text: 'Enter stock adjustments first' });
            return;
        }

        fetch(baseUrl + '/ajax/update_stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: items }),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    stockModal.hide();
                    Swal.fire({ icon: 'success', title: 'Stock updated', timer: 1500, showConfirmButton: false });
                    refresh();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            });
    });

    productModal = new bootstrap.Modal(document.getElementById('productModal'));
    stockModal = new bootstrap.Modal(document.getElementById('stockModal'));
    refresh();
})();
