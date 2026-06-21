# Plan de Implementación: Seguridad de Sesión Avanzada

## Objetivos
1. Permitir al administrador configurar el tiempo de inactividad de sesión (tiempo y unidad: seg/min/hora).
2. Implementar un aviso visual antes de cerrar sesión por inactividad.
3. Actualizar la documentación del sistema.

## Pasos
1. **Configuración Dinámica**:
    - Crear UI en `admin/configuraciones/index.blade.php` con selector de unidad y tiempo.
    - Actualizar `ConfiguracionController` para persistir configuración.
2. **Middleware Dinámico**:
    - Ajustar `CheckIdleSession` para calcular la inactividad basado en la configuración guardada.
3. **Frontend Inactividad**:
    - Crear script JS para monitorear actividad del usuario.
    - Mostrar modal de aviso utilizando componentes existentes.
    - Implementar cierre de sesión automático mediante fetch/post tras cuenta regresiva.
4. **Documentación**:
    - Actualizar `MANUAL.md` con detalles técnicos (código, lógica).
    - Actualizar `MANUAL_USUARIO.md` con instrucciones de uso.
