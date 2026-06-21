import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.addEventListener('beforeunload', function () {
    const data = new FormData();
    data.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    navigator.sendBeacon('/logout', data);
});
