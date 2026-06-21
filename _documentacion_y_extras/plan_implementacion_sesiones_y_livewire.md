# Plan de Implementación: Seguridad de Sesión y Expansión Livewire

## 1. Módulos actuales con Livewire
El sistema ya utiliza Livewire en:
*   `TicketsTable` (Soporte)
*   `AuditoriasTable` (Administración)

## 2. Módulos sugeridos para integrar Livewire
Para mejorar la dinámica sin recargas, sugiero:
*   **Gestión de Inventario (Equipos)**: Listado dinámico con filtros rápidos (Tipo Equipo, Marca, Modelo).
*   **Configuraciones del Sistema**: Panel para gestionar parámetros, incluyendo la duración de sesión.
*   **Usuarios**: Panel administrativo para ver, editar y asignar roles.

## 3. Implementación de Seguridad de Sesión

### A. Sesión por inactividad configurable
1.  **Configuración**: Crear una tabla `configuraciones` en la DB para almacenar el tiempo de inactividad.
2.  **Middleware**: Crear `CheckIdleSession` que compare `last_activity` contra el valor en DB.
3.  **Frontend**: Script JS en `admin.blade.php` y `usuario.blade.php` que detecte inactividad y cierre sesión.

### B. Cierre de sesión al cerrar pestaña
1.  **Evento JS**: Escuchar `beforeunload` en `resources/js/app.js`.
2.  **Acción**: Ejecutar `navigator.sendBeacon('/logout')` para invalidar la sesión al cerrar la pestaña.

## 4. Hoja de Ruta
- [ ] **Fase 1**: Crear tabla `configuraciones` y migraciones necesarias.
- [ ] **Fase 2**: Implementar lógica de `CheckIdleSession` (middleware).
- [ ] **Fase 3**: Implementar script JS de `beforeunload`.
- [ ] **Fase 4**: Refactorizar módulos (Equipos, Config, Usuarios) a Livewire.
