(function () {
    'use strict';

    const form = document.getElementById('settingsForm');
    if (!form) return;

    const baseUrl = form.dataset.baseUrl;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        fetch(baseUrl + '/ajax/save_settings.php', {
            method: 'POST',
            body: new FormData(form),
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: data.message, timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save settings' });
            });
    });
})();
