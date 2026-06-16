// Monitor de inactividad de sesión
let idleTime = 0;
const idleLimit = 30; // Minutos (debería ser dinámico desde DB)
let countdown = 60; // Segundos

function timerIncrement() {
    idleTime++;
    if (idleTime >= idleLimit) {
        showIdleModal();
    }
}

// Resetear contador al detectar actividad
window.onload = resetTimer;
document.onmousemove = resetTimer;
document.onkeypress = resetTimer;

function resetTimer() {
    idleTime = 0;
}

setInterval(timerIncrement, 60000); // 1 minuto

const idleModal = new bootstrap.Modal(document.getElementById('idleModal'));

function showIdleModal() {
    idleModal.show();
    let interval = setInterval(() => {
        countdown--;
        document.getElementById('countdown').innerText = countdown;
        if (countdown <= 0) {
            clearInterval(interval);
            window.location.href = '/logout'; // O realizar llamada fetch
        }
    }, 1000);
}

function keepAlive() {
    idleModal.hide();
    countdown = 60;
    idleTime = 0;
    // Opcional: Hacer llamada fetch para refrescar sesión en servidor
}
