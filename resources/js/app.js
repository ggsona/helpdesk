import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.addEventListener('beforeunload', function () {
    navigator.sendBeacon('/logout');
});
