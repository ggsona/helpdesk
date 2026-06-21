// Monitor de inactividad de sesión
let idleTime = 0;
const idleLimit = window.config?.sesion_timeout || 30; // Minutos, tomado de la config global
let countdown = 60; // Segundos

function timerIncrement() {
    idleTime++;
    console.log('Tiempo inactivo (min): ' + idleTime + ' / ' + idleLimit);
    if (idleTime >= idleLimit) {
        showIdleModal();
    }
}

// Resetear contador al detectar actividad
window.addEventListener('mousemove', resetTimer);
window.addEventListener('keypress', resetTimer);

function resetTimer() {
    idleTime = 0;
}

setInterval(timerIncrement, 60000); // 1 minuto

const idleModalElement = document.getElementById('idleModal');
const idleModal = new bootstrap.Modal(idleModalElement);

function showIdleModal() {
    if (document.querySelector('.modal.show')) return;
    console.log('Mostrando modal');
    idleModal.show();
    countdown = 60;
    
    if (window.idleInterval) clearInterval(window.idleInterval);

    window.idleInterval = setInterval(() => {
        countdown--;
        const countdownEl = document.getElementById('countdown');
        if (countdownEl) countdownEl.innerText = countdown;
        if (countdown <= 0) {
            clearInterval(window.idleInterval);
            fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: '_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')
            }).then(() => {
                window.location.href = '/login';
            });
        }
    }, 1000);
}

window.keepAlive = function() {
    idleModal.hide();
    clearInterval(window.idleInterval);
    countdown = 60;
    idleTime = 0;
    console.log('Sesión extendida');
    
    // Llamada al servidor para refrescar la sesión
    fetch('/keep-alive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Content-Type': 'application/json'
        }
    });
}
