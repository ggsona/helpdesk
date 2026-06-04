# Manual de Desarrollo: Sistema Helpdesk Laravel

Este documento describe la arquitectura y el funcionamiento del sistema.

## 1. Arquitectura General
El sistema sigue el patrón MVC (Modelo-Vista-Controlador) de Laravel, potenciado con [Livewire](https://laravel-livewire.com/) para interfaces dinámicas y [Tailwind CSS](https://tailwindcss.com/) para el estilo.

## 2. Capas del Sistema

### A. Migraciones (`database/migrations/`)
*   **Qué son**: Definen la estructura de la base de datos.
*   **Cómo editar**: Crea una nueva migración con `php artisan make:migration ...` para modificar tablas existentes o crear nuevas. **Nunca** edites migraciones antiguas que ya fueron ejecutadas.

### B. Modelos (`app/Models/`)
*   **Qué son**: Representan las tablas de la base de datos.
*   **Cómo editar**: Define relaciones (`belongsTo`, `hasMany`) y atributos `$fillable` para permitir asignación masiva de datos.

### C. Lógica (Livewire/Controladores) (`app/Livewire/`)
*   **Qué son**: Manejan los datos y la lógica de interacción del usuario.
*   **Cómo editar**: Modifica los métodos de clase para cambiar el comportamiento ante eventos de usuario (clics, envíos de formularios).

### D. Vistas y Layouts (`resources/views/`)
*   **Qué son**: Interfaz visual.
*   **Cómo editar**:
    *   `layouts/`: Estructura base (Admin, Usuario, Invitado).
    *   `livewire/`: Componentes dinámicos.
    *   `soporte/` o `usuario/`: Vistas de páginas específicas.

## 3. ¿Cómo extender el sistema?

1.  **Nueva Funcionalidad**:
    *   Crear migración.
    *   Crear/Actualizar modelo.
    *   Crear componente Livewire (si es dinámico).
    *   Crear/Editar vista.
2.  **Permisos**:
    *   Ver `database/seeders/PermissionSeeder.php` para añadir nuevos roles/permisos.
