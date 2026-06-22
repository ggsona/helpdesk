// Monitor de inactividad de sesión (Optimizado para pestañas en segundo plano y múltiples pestañas)
const idleLimitMinutes = window.config?.sesion_timeout || 30; // Minutos, tomado de la config global
const idleLimitMs = idleLimitMinutes * 60000;
let countdown = 60; // Segundos
let isModalShowing = false;

// Inicializar el tiempo de última actividad en localStorage si no existe
if (!localStorage.getItem('lastActivityTime')) {
    localStorage.setItem('lastActivityTime', Date.now());
}

function checkIdleTime() {
    if (isModalShowing) return;
    
    const lastActivityTime = parseInt(localStorage.getItem('lastActivityTime') || Date.now());
    const currentTime = Date.now();
    const elapsedTime = currentTime - lastActivityTime;
    
    if (elapsedTime >= idleLimitMs) {
        showIdleModal();
    }
}

// Resetear contador al detectar actividad, guardándolo en localStorage para sincronizar pestañas
function resetTimer() {
    if (!isModalShowing) {
        localStorage.setItem('lastActivityTime', Date.now());
    }
}

// Eventos de actividad del usuario
window.addEventListener('mousemove', resetTimer);
window.addEventListener('keypress', resetTimer);
window.addEventListener('scroll', resetTimer);
window.addEventListener('click', resetTimer);

// Los navegadores modernos pausan los setInterval largos en pestañas inactivas.
// Por eso, revisamos frecuentemente (cada 10s) la diferencia de tiempo real (Date.now()).
setInterval(checkIdleTime, 10000);

// Al volver a la pestaña, comprobar inmediatamente por si la inactividad ocurrió mientras estábamos fuera
document.addEventListener("visibilitychange", function() {
    if (document.visibilityState === 'visible') {
        checkIdleTime();
    }
});

const idleModalElement = document.getElementById('idleModal');
const idleModal = new bootstrap.Modal(idleModalElement);

function showIdleModal() {
    if (document.querySelector('.modal.show') || isModalShowing) return;
    
    console.log('Mostrando modal por inactividad');
    isModalShowing = true;
    idleModal.show();
    countdown = 60;
    
    if (window.idleInterval) clearInterval(window.idleInterval);

    window.idleInterval = setInterval(() => {
        countdown--;
        const countdownEl = document.getElementById('countdown');
        if (countdownEl) countdownEl.innerText = countdown;
        if (countdown <= 0) {
            clearInterval(window.idleInterval);
            const logoutUrl = window.config?.logout_url || '/logout';
            fetch(logoutUrl, {
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
    isModalShowing = false;
    localStorage.setItem('lastActivityTime', Date.now()); // Resetear para todas las pestañas
    console.log('Sesión extendida');
    
    // Llamada al servidor para refrescar la sesión backend
    const keepAliveUrl = window.config?.keep_alive_url || '/keep-alive';
    fetch(keepAliveUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Content-Type': 'application/json'
        }
    });
}
