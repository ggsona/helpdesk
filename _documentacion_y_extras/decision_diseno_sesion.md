# Decisión de Diseño: Seguridad de Sesión

Para mejorar la experiencia de usuario y la seguridad al expirar la sesión por inactividad, se ha tomado la siguiente decisión:

## Opción Elegida: Redirección Directa al Login

Tras analizar si mostrar una página de error estética (409) o redirigir directamente al login, se ha decidido **redirigir directamente al login**.

### Razones:
1.  **Fluidez**: Al redirigir directamente al login con un mensaje explicativo (ej: "Tu sesión ha expirado por inactividad"), el usuario puede reingresar sus credenciales inmediatamente sin tener que navegar desde una página intermedia (error 409).
2.  **Seguridad**: Evita exponer innecesariamente mensajes de error del servidor. El usuario solo necesita volver a autenticarse para continuar trabajando.
3.  **Simplicidad**: Reduce la complejidad de mantenimiento de vistas de error adicionales.

## Configuración del Tiempo de Respuesta
Se considera altamente recomendable permitir configurar el tiempo de respuesta (cuenta regresiva antes del cierre). Esto otorga flexibilidad: en entornos de alta seguridad (bancos, servidores críticos) un tiempo breve (ej. 30s) es mejor, mientras que en entornos operativos estándar, un minuto (60s) permite al usuario reaccionar sin interrupciones abruptas.

### Nota sobre el Tiempo de Inactividad en pruebas
En el archivo `public/js/idle-monitor.js`, el límite de inactividad (`idleLimit`) se fijó temporalmente en `1` minuto para facilitar las pruebas de desarrollo y verificación de la funcionalidad del modal. Para producción, este valor debe ser ajustado (por ejemplo, a `30` minutos) para reflejar la política de seguridad deseada del sistema.
