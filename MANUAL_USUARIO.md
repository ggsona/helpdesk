# MANUAL DE USUARIO: HELPDESK GDC

Bienvenido al **Manual de Usuario de Helpdesk GDC**. Esta guía está diseñada para explicar de forma detallada, visual y sencilla todas las funcionalidades y elementos del sistema de mesa de ayuda (Helpdesk) estructurados por los diferentes roles de usuario.

---

## 1. MATRIZ DE ROLES Y ACCESOS

El sistema cuenta con un control de accesos que divide las funciones según las tareas operativas de cada miembro de la organización:

| Rol | Usuario Objetivo | Vista Principal | Funciones Clave |
| :--- | :--- | :--- | :--- |
| **Usuario (Cliente)** | Personal de oficinas que reporta incidentes. | `mis-tickets` / `Inicio` | Crear borradores, adjuntar archivos, enviar a soporte, y chatear con el técnico. |
| **Gestor (Despachador)** | Coordinador del área de TI / Soporte. | `Gestión de Casos` | Ver tickets abiertos, asignar técnicos, definir prioridades, reasignar casos y chatear (público/interno). |
| **Técnico (Especialista)**| Resolutor técnico del problema. | `Casos Asignados` | Ver bandeja ordenada por prioridad, chatear (público/interno), resolver tickets y editar soluciones. |
| **Admin (Administrador)** | Jefe de sistemas / Soporte técnico global. | `Dashboard` | Acceso global del sistema y configuraciones. |

---

## 2. EL CICLO DE VIDA DE UN TICKET (ESTATUS)

Los tickets en el Helpdesk GDC atraviesan 4 estados lógicos bien definidos. Comprender este flujo es fundamental para el uso correcto de la plataforma:

```
[ Borrador (Estatus 0) ]  --> Solo visible para el Cliente (Editable/Eliminable)
         │
         ▼ (Acción: Enviar)
[ Abierto (Estatus 1) ]   --> Visible para el Gestor "Por Asignar"
         │
         ▼ (Acción: Asignar Especialista)
[ En Proceso (Estatus 2) ] --> Asignado al Técnico (Chat y Notas activas)
         │
         ▼ (Acción: Resolver Caso)
[ Resuelto (Estatus 3) ]  --> Ticket Cerrado (Publicación de Solución Técnica)
```

---

## 3. MANUAL DEL USUARIO FINAL (CLIENTE)

Este módulo está optimizado para que cualquier colaborador de la empresa reporte fallas de forma simple y amigable.

### A. Pantalla de Inicio
Al iniciar sesión, el cliente es recibido por un panel interactivo con dos acciones principales:
1.  **Abrir Ticket**: Formulario para reportar un nuevo incidente.
2.  **Mis Solicitudes**: Acceso directo al historial y estado de sus tickets anteriores.

### B. Crear un Ticket como Borrador
El sistema utiliza una lógica de **Borrador**. Al llenar el formulario, el ticket no se envía inmediatamente al soporte técnico, lo que permite revisarlo, corregirlo o adjuntar más pruebas antes del envío definitivo.

#### Pasos para Crear un Reporte:
1.  Ingresa a **Nuevo Ticket** desde la barra de navegación superior o el botón de Inicio.
2.  **Asunto**: Escribe un título conciso (Ej: *"Impresora no enciende en administración"*).
3.  **Categoría**: Selecciona si es una falla de `Hardware` (físico), `Software` (programas) o `Redes` (internet/cables).
4.  **Equipo Afectado**: Elige el tipo de hardware (Ej: `Laptop`, `Desktop`, `Impresora`).
5.  **Descripción del Problema**: Detalla ampliamente la falla (qué estabas haciendo, códigos de error, etc.).
6.  **Archivos Adjuntos (Opcional)**: Sube capturas de pantalla, fotos tomadas con tu celular o archivos PDF (Tamaño máximo: 10MB por archivo).
7.  Haz clic en **Enviar Ticket**. El sistema guardará el registro en estado **Borrador**.

### C. Bandeja de "Mis Casos"
En esta sección puedes administrar todos tus reportes. Si un ticket está en estado **Borrador**, verás las siguientes acciones en la columna de la derecha:
*   **Detalles (Ojo)**: Permite entrar a ver el borrador completo.
*   **Enviar (Icono Avión/Flecha)**: Hace clic para enviar el ticket oficialmente a soporte técnico. Una vez enviado, cambia a **Abierto** y el equipo de soporte técnico podrá verlo y asignarle un especialista.
*   **Editar (Lápiz)**: Modifica el borrador (asunto, descripción o categoría).
*   **Eliminar (Basurero)**: Cancela definitivamente el reporte de forma permanente.

### D. Chat del Ticket y Seguimiento
Al entrar a los **Detalles** de un ticket enviado, tendrás acceso a:
*   **Ficha Técnica**: Ver quién es tu técnico asignado, la prioridad del caso y los archivos adjuntos que cargaste.
*   **Chat en Vivo**: Escribe mensajes directamente en la parte inferior para responder preguntas del técnico o consultar el estado de tu caso.

---

## 4. MANUAL DEL GESTOR (DESPACHADOR DE SOPORTE)

El Gestor actúa como el cerebro de la operación, coordinando las cargas de trabajo y distribuyendo los incidentes.

### A. Panel de Gestión de Casos
Es un tablero Kanban dinámico organizado en tres pestañas operativas (Pills):
1.  **Por Asignar**: Muestra todos los tickets en estado **Abierto** (enviados por los clientes) que no tienen técnico responsable.
2.  **En Gestión**: Muestra todos los tickets en estado **En Proceso** que están siendo atendidos por los técnicos.
3.  **Resueltos**: Historial completo de tickets que ya fueron cerrados con éxito.

### B. Proceso de Asignación Inicial
Cuando un caso ingresa a **Por Asignar**, el gestor debe:
1.  Hacer clic en el botón **Asignar** del ticket correspondiente.
2.  Se abrirá una ventana emergente (modal) con los siguientes campos:
    *   **Técnico Especialista**: Un listado desplegable que muestra únicamente al personal calificado con el rol de `Técnico`.
    *   **Prioridad del Ticket**: Define el tiempo de atención obligatorio (`Baja`, `Media`, `Alta`, `Crítica`).
    *   **Nota de Instrucción (Opcional)**: Indicaciones específicas del gestor para el técnico (Ej: *"Revisar tarjeta de red física, el usuario reporta que ya reinició el equipo"*).
3.  Confirmar la asignación. El ticket automáticamente pasa a la pestaña **En Gestión** (estatus 2: En Proceso) y se crea un comentario en el chat del ticket notificando el cambio de estado.

### C. Reasignación de Casos
Si un técnico no puede atender un caso (por vacaciones, sobrecarga de tareas o porque requiere otra especialidad), el gestor puede reasignarlo:
1.  Ve a la pestaña **En Gestión**.
2.  Haz clic en **Reasignar** sobre el ticket correspondiente.
3.  Selecciona al nuevo técnico especialista, redefine la prioridad si es necesario, y escribe el motivo de la reasignación en el campo de notas.
4.  Al confirmar, el sistema actualiza el responsable en la base de datos y publica un comentario automático en el chat notificando la reasignación para mantener la transparencia del proceso.

### D. Notas Internas (Chat Privado)
En el detalle del ticket, el gestor puede escribir mensajes. Tiene una casilla de verificación llamada **"Mensaje Interno"**:
*   Si se marca, el mensaje se guarda como **Nota Interna** (con un fondo distintivo en la vista).
*   **Importante**: Las notas internas son visibles **únicamente** para los técnicos y gestores. El cliente final **no** podrá ver este contenido bajo ninguna circunstancia.

---

## 5. MANUAL DEL TÉCNICO (ESPECIALISTA EN RESOLUCIÓN)

El Técnico es responsable de ejecutar el trabajo técnico en sitio o de forma remota y dejar registro formal del proceso.

### A. Tablero de Control Técnico
Muestra una bandeja de entrada adaptada a su carga personal de trabajo, dividida en dos secciones:
1.  **Mis Casos Asignados**: Agrupados y clasificados visualmente con semáforos de prioridad de color para destacar el trabajo urgente:
    *   🔴 **Prioridad Crítica**: Casos que detienen la operación de la empresa.
    *   🟡 **Prioridad Alta**: Fallas severas pero no paralizantes.
    *   🔵 **Prioridad Media**: Incidentes estándar.
    *   🟢 **Prioridad Baja**: Consultas, solicitudes menores o insumos.
2.  **Historial de Resueltos**: Casos cerrados recientemente por él.

### B. Comunicación Mixta (Chat y Notas)
El técnico puede chatear en tiempo real con el cliente desde la vista de detalles del ticket. Al igual que el gestor:
*   Si redacta un comentario normal, el cliente lo recibe en su pantalla de inmediato.
*   Si activa la casilla **"Mensaje Interno"**, entabla una conversación privada con los coordinadores y gestores (Ej: *"Necesito aprobación para comprar repuesto de disco duro"*), oculta para el cliente.

### C. Cierre y Resolución del Ticket
Cuando el técnico ha solucionado el incidente:
1.  Hace clic en el botón **Resolver** en su listado o dentro del ticket.
2.  Deberá completar el **Formulario de Resolución**, el cual está dividido estratégicamente en dos partes:
    *   **Resumen para el Usuario**: Explicación sencilla, en lenguaje no técnico, de lo que causó el problema y cómo se solucionó (Ej: *"Se configuró nuevamente el controlador de la impresora y se realizó prueba de impresión exitosa"*). Este texto se le mostrará al cliente en su bandeja de entrada.
    *   **Procedimiento Detallado**: Bitácora técnica exhaustiva para control de la base de conocimientos del equipo de TI (Ej: *"Se reinstaló driver v4.2.1, se reparó puerto TCP/IP asignado 192.168.1.55 y se limpió cola de impresión atascada en spooler de Windows"*).
3.  Al guardar, el ticket pasa a **Resuelto (3)**, se registra la fecha y hora exacta de cierre y se notifica al usuario.

### D. Editar la Solución Técnica
Si posterior al cierre el técnico cometió un error de redacción en el reporte o requiere aportar más detalles técnicos del procedimiento, puede hacerlo:
1.  Ve a la pestaña **Historial de Resueltos**.
2.  Haz clic en **Editar Solución** sobre el ticket correspondiente.
3.  Actualiza los campos necesarios y confirma los cambios. La solución se actualizará sin modificar el estatus del ticket cerrado.

---

> [!TIP]
> **Recomendación para un Soporte de Alta Calidad**
> *   **Clientes**: Utilicen siempre los borradores para verificar que la descripción sea clara. Esto ahorra tiempo valioso al técnico.
> *   **Gestores**: Revisen la pestaña "Por Asignar" constantemente y aprovechen las notas de instrucción al asignar especialistas.
> *   **Técnicos**: Utilicen las Notas Internas para debatir problemas con los gestores antes de dar una respuesta formal al cliente final.
