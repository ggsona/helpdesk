# Historial de Planes de Implementación Realizados

Este documento compila de forma integral todos los planes de diseño y arquitectura técnica elaborados para el sistema **Helpdesk GDC**.

---

## Plan 1: Refactorización Multi-Institucional y Soporte de Directorios (AD/LDAP)

### Objetivo
Extender el soporte del sistema de soporte técnico para que sea multi-institucional, permitiendo jerarquías organizacionales dinámicas y la integración con proveedores de identidades como Windows Active Directory y OpenLDAP.

### Cambios Arquitectónicos
1. **Base de Datos y Modelos**:
   * Creación de `nivel_jerarquicos` y `unidad_administrativas` para modelar sedes, complejos, divisiones y departamentos de forma recursiva con un modelo padre-hijo (`parent_id`).
   * Adición de campos de auditoría `created_by` y `updated_by` en categorías del sistema para cumplir normativas de control.
2. **Integración AD/LDAP**:
   * Panel de control administrativo para guardar credenciales de servidores de dominio (Host, Puerto, Base DN, Usuario Bind, Contraseña).
   * Soporte dinámico para mapeo de atributos según el tipo de servidor (`sAMAccountName` para Active Directory y `uid` para OpenLDAP).
   * Botón asíncrono para comprobar la conectividad en vivo mediante llamadas AJAX (`/admin/configuraciones/ad/test`).

---

## Plan 2: Diseño Premium de Categorías e Inventario de Hardware

### Objetivo
Actualizar la experiencia de usuario (UX) del panel de categorías a un estándar moderno de alta gama y sentar las bases para la gestión física de activos.

### Cambios Arquitectónicos
1. **Premium Blade Design**:
   * Refactorización de las tablas utilizando el estilo responsivo premium, integrando el botón de cambio de tema (claro/oscuro) de manera que se hereden los fondos dinámicos sin bloques blancos.
   * Uso de clases `.card-premium`, botones outline estilizados con bordes redondeados y micro-interacciones.
2. **Módulo de Equipos (Scaffolding)**:
   * Migración de base de datos para la tabla `equipos` con campos para IP, dirección MAC, nombre del dispositivo y número de bien institucional.
   * Relación de pertenencia hacia un tipo de equipo (`id_tipo_equipo`) y asignación hacia un usuario responsable (`id_usuario_asignado`).

---

## Plan 3: Catálogo Relacional de Marcas/Modelos y Dropdowns AJAX Dinámicos

### Objetivo
Resolver problemas de visualización vertical de textos y botones de acción en pantallas medianas, agregar buscadores en los selectores de asignación y cambiar la especificación de Marca/Modelo de texto plano a entidades relacionales e interdependientes.

### Cambios Arquitectónicos
1. **Tablas Autoadaptables y Flexibles**:
   * Eliminación de anchos fijos y adición de clases `.text-nowrap` a cabeceras y celdas de acciones para impedir saltos de línea indeseados en botones.
2. **Asignación de Usuarios**:
   * Integración de la biblioteca **Select2** con motor de búsqueda en tiempo real sobre el dropdown de usuarios responsables (`#id_usuario_asignado`) y marcas.
3. **Marcas y Modelos Relacionales**:
   * Creación de las tablas `marcas` (id, nombre) y `modelos` (id, nombre, id_marca).
   * Actualización de la tabla `equipos` con llaves foráneas `id_marca` e `id_modelo`.
   * Implementación de un endpoint AJAX (`/admin/marcas/{id}/modelos`) para cargar de manera reactiva los modelos en el formulario únicamente cuando se ha seleccionado una marca padre.
4. **Pestañas de Catálogos Unificadas**:
   * Diseño de una interfaz de 3 pestañas (Categorías, Marcas, Modelos) dentro de la vista principal de categorías para unificar la administración de todos los insumos y activos del sistema en un solo lugar.
