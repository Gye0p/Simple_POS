(function () {
    'use strict';

    const app = document.getElementById('categoriesApp');
    if (!app) return;

    const baseUrl = app.dataset.baseUrl;
    const modalEl = document.getElementById('categoryModal');
    const modal = new bootstrap.Modal(modalEl);
    let table = null;

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function load() {
        fetch(baseUrl + '/ajax/get_categories.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to load categories' });
                    return;
                }

                if (table) {
                    table.destroy();
                    table = null;
                }

                const tbody = document.querySelector('#categoriesTable tbody');
                tbody.innerHTML = data.categories.map(function (c) {
                    return (
                        '<tr>' +
                            '<td>' + escapeHtml(c.name) + '</td>' +
                            '<td class="text-center">' + c.product_count + '</td>' +
                            '<td class="text-center">' +
                                '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="' + c.id + '" data-name="' + escapeHtml(c.name) + '">' +
                                    '<i class="bi bi-pencil"></i>' +
                                '</button> ' +
                                '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="' + c.id + '" data-name="' + escapeHtml(c.name) + '">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</td>' +
                        '</tr>'
                    );
                }).join('');

                table = $('#categoriesTable').DataTable({ pageLength: 25, order: [[0, 'asc']] });
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load categories' });
            });
    }

    document.getElementById('btnAddCategory').addEventListener('click', function () {
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryModalTitle').textContent = 'Add Category';
        modal.show();
    });

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();

        fetch(baseUrl + '/ajax/save_category.php', {
            method: 'POST',
            body: new FormData(e.target),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    modal.hide();
                    Swal.fire({ icon: 'success', title: 'Saved', text: data.message, timer: 1500, showConfirmButton: false });
                    load();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save category' });
            });
    });

    document.querySelector('#categoriesTable').addEventListener('click', function (e) {
        const edit = e.target.closest('.btn-edit');
        const del = e.target.closest('.btn-delete');

        if (edit) {
            document.getElementById('categoryId').value = edit.dataset.id;
            document.getElementById('categoryName').value = edit.dataset.name;
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            modal.show();
        }

        if (del) {
            Swal.fire({
                title: 'Delete category?',
                text: del.dataset.name,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const fd = new FormData();
                fd.append('id', del.dataset.id);

                fetch(baseUrl + '/ajax/delete_category.php', { method: 'POST', body: fd })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                            load();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                        }
                    });
            });
        }
    });

    load();
})();
