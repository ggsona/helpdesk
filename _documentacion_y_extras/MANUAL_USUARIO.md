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

## 6. MANUAL DEL ADMINISTRADOR GLOBAL (SÚPER USUARIO)

El Administrador tiene el control total sobre la seguridad, los accesos y la configuración del sistema.

### A. Bandeja de Aprobaciones (Nuevos Registros)
Por motivos de seguridad, cuando un colaborador nuevo se registra en el Helpdesk, su cuenta entra a una **Sala de Espera**. No podrá crear tickets ni ver el panel hasta que sea validado.
1. Ingresa a la sección **Aprobación de Usuarios** en el menú lateral.
2. Verás una lista de usuarios "Pendientes" con la ruta organizacional que declararon al registrarse (ej. Sede Central > División de Tecnología).
3. Evalúa si los datos son legítimos y haz clic en **Aprobar Acceso**. En caso de un registro erróneo o fraudulento, puedes **Rechazar**.

### B. Directorio Dinámico de Usuarios
Desde la sección **Usuarios** (Bajo *Configuración y Activos*), puedes administrar a toda la plantilla del sistema.
*   **Buscador Rápido**: Utiliza la barra de búsqueda superior para encontrar inmediatamente a un colaborador tecleando su nombre, correo o número de cédula. La tabla se filtrará instantáneamente.
*   **Cambiar Roles (Privilegios)**: Al hacer clic en el botón de edición (el lápiz azul) junto al rol actual de un usuario, puedes ascenderlo o reasignarlo. *Ejemplo: Convertir a un 'usuario' en 'técnico'.*
*   **Interruptor de Acceso (Activar/Desactivar)**: En la columna "Acceso", puedes apagar el interruptor azul para desactivar a un usuario instantáneamente (por ejemplo, por despido o suspensión). El sistema expulsará al usuario inmediatamente y no le permitirá iniciar sesión hasta que vuelvas a encender el interruptor.

### C. Catálogo de Activos de Hardware
Desde el menú **Catálogo de Activos**, los gestores y administradores pueden organizar el inventario lógico de hardware del sistema a través de tres pestañas operativas:
1.  **Tipos de Equipos**: Categorización base del hardware (Ej: Laptops, Desktops, Impresoras, Tarjetas Gráficas).
2.  **Fabricantes / Marcas**: Marcas del hardware, vinculadas opcionalmente a un tipo de equipo compatible (Ej: *NVIDIA* como fabricante asociado a *Tarjeta Gráfica*).
3.  **Modelos**: Línea de producto específica vinculada a una marca (Ej: *GeForce RTX 4070*).
*   **Buscadores y Paginación**: Cada una de las pestañas cuenta con su propia barra de búsqueda y paginador para manejar listados de forma ágil.
*   **Smart Deletes (Eliminación Inteligente)**: Para proteger la integridad de tus datos, el sistema no te permitirá eliminar un tipo de equipo si tiene marcas o modelos asignados, ni una marca si tiene modelos activos en uso.

---

## 7. MÓDULOS DE CONTROL INTERNO Y AUDITORÍA (GESTORES Y ADMINISTRADORES)

Para garantizar la seguridad lógica, transparencia del sistema y el control de la productividad, se incorporaron tres paneles avanzados:

### A. Preservación del Historial de Categorías en Tickets
Si editas el nombre de una categoría o la desactivas, los tickets antiguos **nunca verán alterada su información**. Cada ticket congela permanentemente el nombre de la categoría asignada al crearse, lo que asegura reportes e historiales consistentes a través del tiempo.

### B. Bitácora de Auditorías (Logs de Actividades)
Ubicada en **Bitácora de Auditorías** en el menú lateral, esta interfaz permite monitorear todos los movimientos del sistema en tiempo real:
*   **¿Qué registra?**:
    *   Inicios de sesión (`login`) y salidas (`logout`) del sistema.
    *   Intentos de accesos fallidos (`login_failed`) para alertar sobre actividad sospechosa.
    *   Sincronización de permisos sobre roles de seguridad.
    *   Creaciones, modificaciones y eliminaciones de cualquier elemento del catálogo.
*   **Detalles JSON de Cambios**: Puedes expandir cada registro para ver con exactitud los datos anteriores (`old_values`) y los datos nuevos (`new_values`) en formato legible.
*   **Filtros de Búsqueda**: Filtra logs por tipo de acción, componente afectado o responsable de la acción.
*   **Exportar Reportes**: Cuenta con dos botones principales debajo del título de la bitácora:
    *   **Excel (CSV)**: Descarga un reporte completo en formato compatible con hojas de cálculo.
    *   **Descargar PDF**: Genera un documento PDF formal, limpio y en formato horizontal para revisiones de control interno.
    *   *Nota: Ambas exportaciones respetan automáticamente los filtros de búsqueda que tengas aplicados en pantalla.*

### C. Módulo de Rendimiento Técnico (KPIs de Productividad)
Accediendo a **Rendimiento Técnico**, los supervisores pueden monitorear el desempeño del equipo de soporte de forma gráfica e interactiva:
*   **Tarjetas de KPIs Globales**: Muestra la cantidad total de tickets en el sistema, resueltos, en proceso y la tasa de resolución global de la organización.
*   **Métricas por Especialista**: Muestra la productividad individual de cada técnico:
    *   Cantidad de tickets asignados, activos y resueltos.
    *   **Tasa de Cierre (Barra de progreso)**: Porcentaje de casos solucionados.
    *   **Tiempo Promedio de Cierre**: Horas de respuesta promedio calculadas automáticamente desde el momento de la asignación del ticket hasta su resolución técnica en el spooler.

---

---

## 8. SEGURIDAD DE SESIÓN (NUEVO V3.1)

El sistema ahora cuenta con medidas avanzadas de seguridad para proteger tu información en caso de que olvides la sesión abierta:

1. **Cierre Automático por Inactividad**: Si no realizas ninguna acción en el sistema (clics, teclado, navegación) durante el tiempo configurado por los administradores (ej. 30 minutos), aparecerá automáticamente un mensaje de aviso en pantalla preguntando si deseas continuar. Si no confirmas tu actividad en 60 segundos, la sesión se cerrará de forma segura para proteger tus datos.
2. **Cierre al Cerrar Pestaña**: Para mayor seguridad, si cierras la pestaña del navegador o el navegador completo, el sistema detectará el evento y cerrará tu sesión automáticamente de forma inmediata en el servidor, evitando accesos no autorizados si otra persona utiliza tu equipo después.


## Actualizaciones Recientes: Nueva Interfaz y Panel Flotante

El sistema ha recibido una actualización de interfaz y funcionalidad para mejorar tu experiencia:

### 1. Interfaz Moderna y Animada
- **Diseño Transparente (Glassmorphism):** La barra lateral izquierda ahora posee un diseño cristalino moderno, adaptándose fluidamente al modo claro y oscuro.
- **Animaciones Interactivas:** Notarás que las tarjetas y botones reaccionan de manera suave al posar el ratón, mejorando la inmersión en la plataforma.
- **Búsqueda Avanzada:** En la Base de Conocimiento, al hacer clic en el buscador, la pantalla se enfocará en tu búsqueda para evitar distracciones.

### 2. Chat de Soporte Flotante
- En el detalle de un Ticket, el chat ya no ocupa gran parte de la pantalla principal.
- En su lugar, encontrarás un **botón circular flotante** en la esquina inferior derecha. Al hacer clic, se deslizará un panel con toda la conversación. Esto te permite leer el ticket y chatear simultáneamente.

### 3. Tablero de Control Inteligente (Dashboard)
- El Dashboard principal ha sido habilitado con gráficas y datos en tiempo real.
- Dependiendo de tu rol, verás tu propio rendimiento o las métricas generales de todo el equipo de soporte.



## Guía de Uso de Nuevos Módulos (V2.0)

### 1. Panel de Chat Flotante
Al ingresar al detalle de un Ticket asignado, en lugar de ver todo el historial de chat interrumpiendo la lectura de los detalles, verás un botón circular azul (FAB) en la esquina inferior derecha con un icono de mensaje.
- **Abrir Chat:** Haz clic en el botón flotante. Se desplegará un panel lateral derecho.
- **Scroll Automático:** El panel se desplazará automáticamente hacia el mensaje más reciente para que no tengas que bajar manualmente.
- **Cerrar Chat:** Haz clic en la "X" en la parte superior del panel o en cualquier zona oscura fuera de él.

### 2. Dashboard Interactivo
El panel de inicio (Dashboard) ahora cuenta con información generada en tiempo real.
- **Métricas:** En la parte superior verás tarjetas animadas que indican "Nuevos Tickets", "Tickets en Gestión" y "Tickets Cerrados Hoy". Al pasar el ratón, las tarjetas se elevarán ligeramente.
- **Gráfica de Barras (Rendimiento por Técnico):** Muestra cuántos tickets ha resuelto cada técnico versus los que tiene pendientes. Si pasas el cursor por encima de las barras, se mostrará el número exacto.
- **Gráfica de Dona (Por Categoría):** Visualiza la distribución de tickets. Puedes hacer clic en las leyendas de colores para ocultar/mostrar categorías específicas en el gráfico.


### 3. Búsqueda Spotlight en Base de Conocimiento
- Al dirigirte a "Base de Conocimiento", verás una barra de búsqueda más grande y estilizada.
- Al hacer clic dentro de la caja de texto para buscar un artículo, el resto de la interfaz se oscurecerá levemente (efecto Spotlight). Esto está diseñado para mejorar el enfoque y evitar distracciones visuales.

### 4. Modo Oscuro Glassmorphism
- Si tu sistema operativo o tus preferencias están en "Modo Oscuro", notarás que la barra lateral izquierda adquiere una apariencia semitransparente que difumina el fondo, dándole un acabado "Premium" similar al de aplicaciones nativas de alto nivel.

### 5. Auditoría Total e Historial de Eventos (Bitácora Avanzada)
El módulo de Bitácora de Auditorías ha sido ampliado y ahora es capaz de registrar automáticamente todos los procesos críticos de la plataforma:
- **Gestión de Hardware:** Se registran creaciones, ediciones y eliminaciones de todo el catálogo de hardware (`Equipos`, `Marcas`, `Modelos`, `Tipos de Equipo`).
- **Seguimiento de Registros de Usuarios:** El sistema audita desde que un usuario envía su solicitud de registro (marcada con etiqueta azul de **Reg. Solicitado**) hasta que un Administrador aprueba el acceso (**Reg. Aprobado** en etiqueta verde) o lo rechaza (**Reg. Rechazado** en etiqueta roja).

### 6. Chat Flotante Universal (Técnicos y Clientes)
- El panel de chat lateral (Offcanvas) que se implementó para el panel de los técnicos ahora también está disponible en la **Vista del Usuario (Cliente)**.
- Esto significa que al entrar a los detalles de tu ticket, contarás con un botón circular de acceso rápido al chat para conversar con tu técnico sin perder de vista los adjuntos o descripciones originales del problema.

## Actualizaciones de Rendimiento y Diseño (V3.0)

### 1. Migración a Single Page Application (Livewire 3.8)
Se ha actualizado el motor interno de la Mesa de Despacho y la Bitácora de Auditorías a **Livewire 3.8**.
- **Cero Recargas:** Ahora puedes buscar tickets, filtrar estados, o revisar cientos de logs de auditoría al instante sin que la página web tenga que recargarse. Todo ocurre en tiempo real.
- **Auditoría Transparente:** La librería *Spatie Activitylog* se encarga de auditar cada movimiento silenciosamente en segundo plano, sin importar si los datos se modifican desde la nueva vista reactiva o desde controladores antiguos.

### 2. Estética "Premium" en Tablas de Datos
Se rediseñó por completo la apariencia de las tablas de datos (Tickets y Bitácora):
- **Botones Flotantes (Pill Design):** Los rígidos grupos de botones fueron reemplazados por filtros independientes y redondeados que reaccionan de manera inteligente al tamaño de la pantalla, evitando superposiciones.
- **Alineación Responsiva:** Ahora, si tu pantalla es pequeña, los elementos se acomodarán limpiamente con scroll horizontal nativo en lugar de encimarse o cortar texto.
- **Colores Dinámicos Inteligentes:** Las etiquetas de prioridad ahora cuentan con una paleta de colores de alto contraste: Baja (Verde), Media (Azul Claro), Alta (Amarillo) y Crítica (Rojo).

### 3. Indicador de Carga no Invasivo (Overlay Loader)
Anteriormente, al escribir en el buscador, aparecía un texto de carga que empujaba la tabla hacia abajo, creando un "salto" brusco en la pantalla. 
- **Nuevo Overlay Flotante:** Se diseñó una capa de carga absoluta (`position-absolute`) con desenfoque de fondo (`backdrop-filter: blur`). Cuando realizas una búsqueda, esta elegante capa semitransparente flota *por encima* de la tabla de resultados, congelándola visualmente por un segundo sin mover su estructura ni un solo milímetro.

### 4. Corrección de Animaciones en Menú Lateral
- Se arregló el "parpadeo" horizontal (glitch de Flexbox) que ocurría al desplegar el menú "Base de Conocimiento" en el panel lateral de navegación, dotándolo ahora de una expansión fluida y vertical estricta.

### 6. Actualizaci�n de Estabilidad en Sesiones (21/06/2026)
- **Estabilidad del Temporizador de Inactividad:** Se implement� una mejora t�cnica para garantizar que tu trabajo no se interrumpa. Ahora, al recibir la advertencia de que la sesi�n est� por expirar, si seleccionas **"Continuar sesi�n"**, el sistema asegurar� firmemente la extensi�n de tu tiempo en el servidor, permiti�ndote cambiar de secci�n o recargar la p�gina sin riesgo a ser desconectado.
