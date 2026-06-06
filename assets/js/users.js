(function () {
    'use strict';

    const app = document.getElementById('usersApp');
    if (!app) return;

    const baseUrl = app.dataset.baseUrl;
    const modal = new bootstrap.Modal(document.getElementById('userModal'));

    document.getElementById('btnAddUser').addEventListener('click', function () {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userActive').checked = true;
        document.getElementById('userPassword').required = true;
        document.getElementById('passwordHint').textContent = '(required)';
        document.getElementById('userModalTitle').textContent = 'Add User';
        modal.show();
    });

    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const user = JSON.parse(btn.dataset.user);
            document.getElementById('userId').value = user.id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('passwordHint').textContent = '(leave blank to keep current)';
            document.getElementById('userRole').value = user.role;
            document.getElementById('userActive').checked = parseInt(user.is_active, 10) === 1;
            document.getElementById('userModalTitle').textContent = 'Edit User';
            modal.show();
        });
    });

    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.set('is_active', document.getElementById('userActive').checked ? '1' : '0');

        fetch(baseUrl + '/ajax/save_user.php', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: data.message }).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save user' });
            });
    });

    document.querySelectorAll('.btn-toggle-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const isActive = parseInt(btn.dataset.active, 10);
            const action = isActive ? 'deactivate' : 'activate';

            Swal.fire({
                title: action.charAt(0).toUpperCase() + action.slice(1) + ' user?',
                text: btn.dataset.name,
                icon: 'warning',
                showCancelButton: true,
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const fd = new FormData();
                fd.append('id', btn.dataset.id);
                fd.append('is_active', isActive ? '0' : '1');

                fetch(baseUrl + '/ajax/toggle_user.php', { method: 'POST', body: fd })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                        }
                    });
            });
        });
    });
})();
