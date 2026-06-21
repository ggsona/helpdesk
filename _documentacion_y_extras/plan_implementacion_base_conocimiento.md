# Plan de Implementación: Base de Conocimiento e Investigación (Estilo Blog)

## 📌 Visión General

Crear un **Centro de Conocimiento Técnico** integrado al Helpdesk, que funcione como:

1. **Repositorio de Soluciones Pasadas** — Cada ticket resuelto alimenta automáticamente la base.
2. **Blog Técnico de Formación** — Los técnicos/gestores pueden crear artículos independientes (guías, investigaciones, tutoriales).
3. **Motor de Búsqueda de Soluciones** — Antes de trabajar en un ticket, el técnico busca si ya existe una solución documentada.
4. **Biblioteca de Herramientas** — Adjuntar ejecutables, scripts, utilidades (.exe, .bat, .msi, .zip) hasta 1 GB para descarga directa.

---

## 🏗️ A. Arquitectura del Módulo

### A1. Componentes Principales

```
┌──────────────────────────────────────────────────────────┐
│                  BASE DE CONOCIMIENTO                     │
├──────────────┬──────────────────┬────────────────────────┤
│  Artículos   │   Soluciones     │   Herramientas         │
│  (Blog)      │   (de Tickets)   │   (Archivos/Tools)     │
├──────────────┼──────────────────┼────────────────────────┤
│ Creados      │ Generados auto   │ Archivos adjuntos      │
│ manualmente  │ al resolver un   │ .exe .msi .zip .bat    │
│ por técnicos │ ticket (ya       │ hasta 1 GB por         │
│ o gestores   │ existe con       │ artículo/solución      │
│              │ SolucionTecnica) │                        │
└──────────────┴──────────────────┴────────────────────────┘
```

### A2. Flujos de Uso

**Flujo 1: Técnico resuelve un ticket**
1. Técnico abre el formulario de "Resolver Incidente" (ya existe: `resolver.blade.php`).
2. Llena el **Resumen para el Usuario** (campo simple, público).
3. Llena el **Informe Técnico Detallado** con editor Quill mejorado (HTML, imágenes, código).
4. **NUEVO:** Puede adjuntar **herramientas/archivos** usados en la solución (.exe, .zip, etc.).
5. **NUEVO:** Selecciona **etiquetas/tags** (ej: "RAM", "BIOS", "Windows 11", "Impresoras").
6. **NUEVO:** Marca un checkbox "📚 Publicar en la Base de Conocimiento" para que la solución sea visible como artículo público interno.
7. Al guardar, el ticket se cierra y la solución queda disponible en el Centro de Conocimiento.

**Flujo 2: Técnico crea un artículo independiente (Blog)**
1. Desde el menú lateral, accede a "Base de Conocimiento" > "Nuevo Artículo".
2. Escribe un artículo tipo blog con título, contenido enriquecido (Quill/TinyMCE), categoría, tags.
3. Puede adjuntar archivos descargables (herramientas, scripts, manuales PDF).
4. El artículo queda publicado y es buscable por otros técnicos.

**Flujo 3: Técnico busca soluciones existentes**
1. Desde la bandeja del técnico o desde la vista del ticket, hace clic en "🔍 Buscar en Base de Conocimiento".
2. Se abre una interfaz con buscador de texto completo + filtros (categoría, tags, fecha, autor).
3. Encuentra un artículo relevante y puede **copiar la solución** directamente al ticket actual.
4. También puede navegar por las soluciones más populares, más recientes o mejor calificadas.

---

## 🗃️ B. Modelo de Base de Datos

### B1. Nueva tabla: `articulos_conocimiento`
Esta es la tabla central del blog/base de conocimiento.

```php
Schema::create('articulos_conocimiento', function (Blueprint $table) {
    $table->id('id_articulo');
    
    // Origen del artículo
    $table->enum('origen', ['manual', 'ticket'])->default('manual');
    // Si viene de un ticket, referencia a la solución
    $table->unsignedBigInteger('id_solucion')->nullable();
    $table->foreign('id_solucion')->references('id_solucion')->on('soluciones_tecnicas')->nullOnDelete();
    
    // Contenido
    $table->string('titulo', 255);
    $table->string('slug', 300)->unique(); // URL amigable
    $table->text('extracto')->nullable(); // Preview/resumen corto (para cards)
    $table->longText('contenido'); // HTML enriquecido (Quill/TinyMCE)
    
    // Clasificación
    $table->unsignedBigInteger('id_categoria')->nullable();
    $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->nullOnDelete();
    
    // Autoría
    $table->foreignId('id_autor')->constrained('users', 'id');
    $table->foreignId('id_editor')->nullable()->constrained('users', 'id'); // Último editor
    
    // Estado y visibilidad
    $table->enum('estado', ['borrador', 'publicado', 'archivado'])->default('borrador');
    $table->boolean('es_destacado')->default(false); // Artículo fijado/pinned
    $table->boolean('es_interno')->default(true); // true = solo staff, false = también usuarios
    
    // Métricas
    $table->unsignedInteger('vistas')->default(0);
    $table->unsignedInteger('veces_usado')->default(0); // Cuántas veces se usó para resolver un ticket
    
    $table->timestamp('fecha_publicacion')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### B2. Nueva tabla: `articulo_tags` (Sistema de Etiquetas)

```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 80)->unique();
    $table->string('slug', 100)->unique();
    $table->string('color', 7)->default('#6366f1'); // Hex color para el badge
    $table->timestamps();
});

Schema::create('articulo_tag', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_articulo');
    $table->foreignId('id_tag')->constrained('tags')->cascadeOnDelete();
    $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
    $table->unique(['id_articulo', 'id_tag']);
});
```

### B3. Nueva tabla: `articulo_adjuntos` (Herramientas y Archivos)

```php
Schema::create('articulo_adjuntos', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_articulo');
    $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
    
    $table->string('nombre_original');      // "HWMonitor_v1.49.exe"
    $table->string('ruta_archivo');          // "articulos/adjuntos/2026/06/hwmonitor.exe"
    $table->string('tipo_mime', 100);       // "application/x-msdownload"
    $table->unsignedBigInteger('tamano');   // En bytes (máx 1 GB = 1073741824)
    $table->text('descripcion')->nullable(); // "Monitor de temperaturas del CPU/GPU"
    $table->unsignedInteger('descargas')->default(0); // Contador de descargas
    
    $table->foreignId('subido_por')->constrained('users', 'id');
    $table->timestamps();
});
```

### B4. Nueva tabla: `articulo_valoraciones` (Utilidad / Rating)

```php
Schema::create('articulo_valoraciones', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_articulo');
    $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
    $table->foreignId('id_usuario')->constrained('users', 'id');
    
    $table->boolean('es_util'); // true = 👍, false = 👎
    $table->text('comentario')->nullable();
    
    $table->unique(['id_articulo', 'id_usuario']); // Un voto por usuario por artículo
    $table->timestamps();
});
```

### B5. Modificaciones a `soluciones_tecnicas` (tabla existente)

Agregar campos para vincular con la base de conocimiento y enriquecer la documentación:

```php
Schema::table('soluciones_tecnicas', function (Blueprint $table) {
    // Nuevos campos para enriquecer la solución
    $table->text('diagnostico')->nullable()->after('procedimiento_detallado');
    // ↑ Qué encontró el técnico (ej: "RAM defectuosa en slot 2")
    
    $table->text('causa_raiz')->nullable()->after('diagnostico');
    // ↑ Por qué ocurrió (ej: "Sobrecalentamiento por ventilador obstruido")
    
    $table->text('acciones_preventivas')->nullable()->after('causa_raiz');
    // ↑ Cómo evitarlo en el futuro (ej: "Programar limpieza cada 6 meses")
    
    $table->string('tiempo_resolucion', 50)->nullable()->after('acciones_preventivas');
    // ↑ "45 minutos", "2 horas" (registro manual del técnico)
    
    $table->enum('dificultad', ['basica', 'intermedia', 'avanzada'])->default('intermedia')->after('tiempo_resolucion');
    // ↑ Nivel de complejidad para formación de nuevos técnicos
    
    $table->boolean('publicar_en_kb')->default(false)->after('dificultad');
    // ↑ Si se marca, se crea automáticamente un artículo en la Base de Conocimiento
});
```

---

## 🎨 C. Diseño de la Interfaz (UI/UX)

### C1. Vista Principal: "Centro de Conocimiento" (Index - Estilo Blog)

**Ruta:** `/conocimiento`
**Layout:** Estilo blog/magazine moderno con tarjetas.

```
┌─────────────────────────────────────────────────────────┐
│  🔍 Buscar en la Base de Conocimiento...    [Filtros ▼] │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📌 ARTÍCULOS DESTACADOS (Carousel horizontal)          │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                │
│  │ Guía de  │ │ Cómo     │ │ Reset de │                │
│  │ formateo │ │ config.  │ │ BIOS en  │                │
│  │ Windows  │ │ VPN corp │ │ Dell     │                │
│  │ 11 Pro   │ │ empresa  │ │ Latitude │                │
│  └──────────┘ └──────────┘ └──────────┘                │
│                                                         │
│  ─────────────────────────────────────────────────────  │
│                                                         │
│  FILTROS:  [Todos] [Hardware] [Software] [Redes]        │
│  TAGS:     🏷️ Windows  🏷️ Impresoras  🏷️ VPN          │
│                                                         │
│  ┌─ ARTÍCULO ────────────────────────────────────────┐  │
│  │ 📖 Reemplazo de disco duro en HP ProDesk 400 G7   │  │
│  │ Por: Técnico Pedro  •  Hace 3 días  •  👁 24 vistas│  │
│  │ Tags: [HDD] [HP] [Hardware]                       │  │
│  │ "Se detectó disco con sectores dañados mediante   │  │
│  │  CrystalDiskInfo. Se procedió a clonar..."        │  │
│  │                                    [Leer más →]   │  │
│  │ 👍 12 útil  •  📎 2 archivos  •  Dificultad: ⭐⭐ │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ ARTÍCULO (desde ticket) ─────────────────────────┐  │
│  │ 🎫 Configuración de impresora en red Canon MF440   │  │
│  │ Ticket #89  •  Por: Técnico Ana  •  Hace 1 semana │  │
│  │ Tags: [Impresoras] [Canon] [Red]                  │  │
│  │ "El usuario no podía imprimir. Se verificó..."    │  │
│  │                                    [Leer más →]   │  │
│  │ 👍 8 útil  •  📎 1 archivo (.exe driver)          │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  [← Anterior]  Página 1 de 5  [Siguiente →]            │
└─────────────────────────────────────────────────────────┘
```

**Características del Index:**
- **Barra de búsqueda prominente** (full-text search) con filtro por categoría, tags, autor, fecha.
- **Artículos destacados** en un carousel horizontal (estilo Medium/Dev.to).
- **Cards de artículo** con: título, extracto (primeras 150 chars), autor con avatar, fecha relativa, contador de vistas, tags como badges de colores, indicador de archivos adjuntos, nivel de dificultad con estrellas.
- **Badge de origen:** 🎫 = viene de un ticket resuelto, 📖 = artículo manual/investigación.
- **Ordenamiento:** Más recientes, Más vistos, Más útiles, Más usados en tickets.
- **Vista rápida (Quick Preview):** Al hacer hover o clic en un botón, se abre un panel lateral (drawer) con el contenido completo sin cambiar de página.

### C2. Vista de Artículo Completo (Show - Estilo Blog Post)

**Ruta:** `/conocimiento/{slug}`

```
┌─────────────────────────────────────────────────────────┐
│  ← Volver al Centro de Conocimiento                     │
│                                                         │
│  ┌─ HEADER ──────────────────────────────────────────┐  │
│  │ 📖 Reemplazo de disco duro en HP ProDesk 400 G7   │  │
│  │                                                   │  │
│  │ 👤 Técnico Pedro  •  📅 28 Mayo 2026              │  │
│  │ 📂 Hardware  •  ⏱️ 45 min  •  Dificultad: ⭐⭐    │  │
│  │ 🏷️ [HDD] [HP] [Hardware] [CrystalDiskInfo]       │  │
│  │                                                   │  │
│  │ 🎫 Originado del Ticket #89                       │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ SECCIÓN: DIAGNÓSTICO ────────────────────────────┐  │
│  │ Se detectó que el disco presentaba 48 sectores    │  │
│  │ dañados usando CrystalDiskInfo (ver captura).     │  │
│  │ [📷 Captura de pantalla embebida]                 │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ SECCIÓN: PROCEDIMIENTO DETALLADO ────────────────┐  │
│  │ 1. Desmontar carcasa lateral (2 tornillos)        │  │
│  │ 2. Desconectar cable SATA y alimentación          │  │
│  │ 3. Clonar disco con Macrium Reflect Free          │  │
│  │    (ver archivo adjunto)                          │  │
│  │ 4. Instalar nuevo SSD Kingston A400 480GB         │  │
│  │ 5. Verificar arranque y SMART status              │  │
│  │ [📷 Foto del interior del equipo]                 │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ SECCIÓN: CAUSA RAÍZ ─────────────────────────────┐  │
│  │ Sobrecalentamiento sostenido por ventilador       │  │
│  │ obstruido con polvo. El disco WD Blue 1TB tiene   │  │
│  │ 4 años de uso y nunca recibió mantenimiento.      │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ SECCIÓN: ACCIONES PREVENTIVAS ───────────────────┐  │
│  │ • Programar limpieza preventiva cada 6 meses.     │  │
│  │ • Monitorear SMART con CrystalDiskInfo mensual.   │  │
│  │ • Considerar reemplazo masivo de HDDs > 3 años.   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ HERRAMIENTAS Y ARCHIVOS ADJUNTOS ────────────────┐  │
│  │ ┌────────────────────────────────────────────┐    │  │
│  │ │ 📦 CrystalDiskInfo_8.17.14.exe            │    │  │
│  │ │ Monitor de salud de discos duros           │    │  │
│  │ │ Tamaño: 5.2 MB  •  ⬇️ 14 descargas        │    │  │
│  │ │                        [Descargar ⬇️]      │    │  │
│  │ └────────────────────────────────────────────┘    │  │
│  │ ┌────────────────────────────────────────────┐    │  │
│  │ │ 📦 MacriumReflectFree_v8.1.7544.exe        │    │  │
│  │ │ Software de clonación de discos             │    │  │
│  │ │ Tamaño: 180 MB  •  ⬇️ 9 descargas          │    │  │
│  │ │                        [Descargar ⬇️]      │    │  │
│  │ └────────────────────────────────────────────┘    │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ ¿Te fue útil este artículo? ─────────────────────┐  │
│  │     [ 👍 Sí, me sirvió (12) ]  [ 👎 No (1) ]     │  │
│  │     Comentario: [____________________________]    │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ ARTÍCULOS RELACIONADOS ──────────────────────────┐  │
│  │ • Cómo verificar el estado SMART de un disco SSD  │  │
│  │ • Guía de limpieza preventiva de equipos desktop  │  │
│  │ • Actualización de firmware en SSD Kingston        │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### C3. Editor de Artículo (Create/Edit - Estilo CMS)

**Ruta:** `/conocimiento/crear` y `/conocimiento/{slug}/editar`

```
┌─────────────────────────────────────────────────────────┐
│  ← Volver  │  ✏️ Nuevo Artículo de Conocimiento         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  PANEL IZQUIERDO (col-lg-8):                            │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Título: [_____________________________________]   │  │
│  │                                                   │  │
│  │ Extracto/Resumen:                                 │  │
│  │ [Breve descripción para la tarjeta del blog_____] │  │
│  │                                                   │  │
│  │ Contenido (Editor Quill Mejorado):                │  │
│  │ ┌─────────────────────────────────────────────┐   │  │
│  │ │ B I U | H1 H2 | • 1. | 🔗 📷 📹 | </> | ↩  │   │  │
│  │ ├─────────────────────────────────────────────┤   │  │
│  │ │                                             │   │  │
│  │ │  Aquí el técnico escribe con formato rico:  │   │  │
│  │ │  - Texto con negritas, cursivas, listas     │   │  │
│  │ │  - Imágenes embebidas (Ctrl+V de capturas)  │   │  │
│  │ │  - Bloques de código para comandos/scripts  │   │  │
│  │ │  - Links a recursos externos                │   │  │
│  │ │                                             │   │  │
│  │ │  Altura mínima: 500px (expandible)          │   │  │
│  │ │                                             │   │  │
│  │ └─────────────────────────────────────────────┘   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌─ ARCHIVOS Y HERRAMIENTAS ─────────────────────────┐  │
│  │ Arrastra archivos aquí o haz clic para subir       │  │
│  │ ┌──────────────────────────────────────────┐      │  │
│  │ │  📦 archivo.exe  (5.2 MB)   [🗑️ Eliminar]│      │  │
│  │ │  Descripción: [Monitor de temperaturas__]│      │  │
│  │ └──────────────────────────────────────────┘      │  │
│  │ Formatos: .exe .msi .zip .rar .bat .ps1 .pdf      │  │
│  │ Máximo por archivo: 1 GB                          │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  PANEL DERECHO (col-lg-4):                              │
│  ┌─ METADATOS ───────────────────────────────────────┐  │
│  │ Categoría: [▼ Hardware________________]           │  │
│  │                                                   │  │
│  │ Tags: [Windows] [HP] [x] [+ Agregar tag]         │  │
│  │                                                   │  │
│  │ Dificultad: ○ Básica  ● Intermedia  ○ Avanzada   │  │
│  │                                                   │  │
│  │ Visibilidad:                                      │  │
│  │   ○ Solo Staff (técnicos y gestores)              │  │
│  │   ○ Público (también visible para usuarios)       │  │
│  │                                                   │  │
│  │ ☐ Marcar como Artículo Destacado (📌)             │  │
│  │                                                   │  │
│  │ ─────────────────────────────────────────────     │  │
│  │ [Guardar como Borrador]                           │  │
│  │ [✅ Publicar Artículo]                             │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### C4. Mejora del Formulario de Resolver Ticket (Existente)

El formulario actual en `resolver.blade.php` se mejora con secciones estructuradas:

```
┌─────────────────────────────────────────────────────────┐
│  📝 Documentar Solución Técnica                          │
│                                                         │
│  PASO 1: Resumen para el Usuario (Público)              │
│  [Reemplazo de RAM dañada y limpieza interna_________]  │
│                                                         │
│  PASO 2: Diagnóstico (¿Qué encontraste?)               │
│  [Editor Quill - "Se detectó módulo RAM DDR4 de 8GB    │
│   defectuoso en el slot A1. Error: WHEA_UNCORRECTABLE   │
│   confirmado con MemTest86+"]                           │
│                                                         │
│  PASO 3: Procedimiento Detallado (¿Qué hiciste?)       │
│  [Editor Quill con pasos numerados, capturas, etc.]     │
│                                                         │
│  PASO 4: Causa Raíz (¿Por qué ocurrió?)                │
│  [Textarea - "Módulo con 5 años de uso, sometido a     │
│   picos de voltaje frecuentes en la oficina"]           │
│                                                         │
│  PASO 5: Acciones Preventivas (¿Cómo evitarlo?)        │
│  [Textarea - "Instalar regulador de voltaje. Reemplazar│
│   módulos RAM mayores a 4 años proactivamente"]         │
│                                                         │
│  PASO 6: Metadatos                                      │
│  Tiempo de resolución: [45 min ▼]                       │
│  Dificultad: ○ Básica  ● Intermedia  ○ Avanzada        │
│  Tags: [RAM] [DDR4] [MemTest] [+ Agregar]               │
│                                                         │
│  PASO 7: Herramientas Utilizadas                        │
│  [Arrastra archivos o haz clic para subir]              │
│  📦 MemTest86_v10.7.exe (5 MB) — "Test de memoria RAM" │
│                                                         │
│  ☑ Publicar esta solución en la Base de Conocimiento    │
│                                                         │
│  [💾 Publicar y Finalizar]                               │
└─────────────────────────────────────────────────────────┘
```

---

## 📁 D. Estructura de Archivos (Nuevos)

```
app/
├── Http/Controllers/
│   └── Soporte/
│       └── ConocimientoController.php       [NUEVO] — CRUD de artículos
│
├── Models/
│   ├── ArticuloConocimiento.php             [NUEVO]
│   ├── Tag.php                              [NUEVO]
│   ├── ArticuloAdjunto.php                  [NUEVO]
│   └── ArticuloValoracion.php               [NUEVO]
│
resources/views/
├── conocimiento/
│   ├── index.blade.php                      [NUEVO] — Vista blog/grid
│   ├── show.blade.php                       [NUEVO] — Vista artículo completo
│   ├── create.blade.php                     [NUEVO] — Editor CMS
│   ├── edit.blade.php                       [NUEVO] — Editor CMS (edición)
│   └── _card.blade.php                      [NUEVO] — Partial: tarjeta de artículo
│
database/migrations/
│   ├── xxxx_create_articulos_conocimiento_table.php    [NUEVO]
│   ├── xxxx_create_tags_table.php                      [NUEVO]
│   ├── xxxx_create_articulo_tag_table.php              [NUEVO]
│   ├── xxxx_create_articulo_adjuntos_table.php         [NUEVO]
│   ├── xxxx_create_articulo_valoraciones_table.php     [NUEVO]
│   └── xxxx_add_fields_to_soluciones_tecnicas.php      [NUEVO]
```

---

## 🛤️ E. Rutas

```php
// --- CENTRO DE CONOCIMIENTO ---
Route::prefix('conocimiento')->name('conocimiento.')->middleware('can:ver-conocimiento')->group(function () {
    Route::get('/', [ConocimientoController::class, 'index'])->name('index');
    Route::get('/crear', [ConocimientoController::class, 'create'])->name('create')->middleware('can:crear-articulo');
    Route::post('/', [ConocimientoController::class, 'store'])->name('store')->middleware('can:crear-articulo');
    Route::get('/{slug}', [ConocimientoController::class, 'show'])->name('show');
    Route::get('/{slug}/editar', [ConocimientoController::class, 'edit'])->name('edit')->middleware('can:editar-articulo');
    Route::put('/{slug}', [ConocimientoController::class, 'update'])->name('update')->middleware('can:editar-articulo');
    Route::delete('/{id}', [ConocimientoController::class, 'destroy'])->name('destroy')->middleware('can:eliminar-articulo');
    
    // Valoraciones
    Route::post('/{id}/valorar', [ConocimientoController::class, 'valorar'])->name('valorar');
    
    // Descargas
    Route::get('/adjunto/{id}/descargar', [ConocimientoController::class, 'descargar'])->name('descargar');
    
    // API: Tags autocomplete
    Route::get('/api/tags/buscar', [ConocimientoController::class, 'buscarTags'])->name('tags.buscar');
});
```

---

## ⚙️ F. Configuraciones Necesarias

### F1. Subida de archivos grandes (hasta 1 GB)

En `php.ini`:
```ini
upload_max_filesize = 1024M
post_max_size = 1100M
max_execution_time = 600
memory_limit = 512M
```

En `config/filesystems.php`, crear un disco dedicado:
```php
'articulos' => [
    'driver' => 'local',
    'root' => storage_path('app/public/articulos'),
    'url' => env('APP_URL').'/storage/articulos',
    'visibility' => 'public',
],
```

### F2. Validación de archivos

```php
$request->validate([
    'adjuntos.*' => 'nullable|file|max:1048576', // 1 GB en KB
    'adjuntos.*' => 'mimes:exe,msi,zip,rar,7z,bat,ps1,pdf,doc,docx,xlsx,iso,img',
]);
```

### F3. Permisos (Spatie)

```php
// Nuevos permisos a crear en el seeder
'ver-conocimiento'      // Todos los roles de soporte
'crear-articulo'        // Técnicos y Gestores
'editar-articulo'       // Técnicos (propios) y Gestores (todos)
'eliminar-articulo'     // Solo Admin/Gestor
'gestionar-tags'        // Admin/Gestor
```

---

## 🔗 G. Integración con el Flujo Actual

### G1. Desde el Ticket → Base de Conocimiento

Cuando el técnico marca "☑ Publicar en KB" al resolver un ticket:
```php
// En TicketTecnicoController::guardarSolucion()
if ($request->publicar_en_kb) {
    ArticuloConocimiento::create([
        'origen' => 'ticket',
        'id_solucion' => $solucion->id_solucion,
        'titulo' => $request->resumen_usuario,
        'slug' => Str::slug($request->resumen_usuario) . '-' . $solucion->id_solucion,
        'extracto' => Str::limit(strip_tags($request->procedimiento_detallado), 200),
        'contenido' => $request->procedimiento_detallado,
        'id_categoria' => $ticket->id_categoria,
        'id_autor' => Auth::id(),
        'estado' => 'publicado',
        'fecha_publicacion' => now(),
    ]);
}
```

### G2. Desde la Base de Conocimiento → Ticket

En la vista `show.blade.php` del ticket (soporte), agregar un botón:
```html
<button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalBuscarKB">
    🔍 Buscar solución en Base de Conocimiento
</button>
```
Abre un modal con buscador. Al encontrar un artículo relevante, el técnico puede hacer clic en "Usar esta solución" y los campos se autocompletarán.

### G3. Menú Lateral (Sidebar)

Agregar al menú bajo la sección "Panel Operativo":
```html
@can('ver-conocimiento')
<li>
    <a href="{{ route('conocimiento.index') }}" class="nav-link">
        <i class="bi bi-book-half me-3"></i>Base de Conocimiento
    </a>
</li>
@endcan
```

---

## 🧩 H. Funcionalidades Extra que Agregan Valor

### H1. Artículos Relacionados Automáticos
Basado en los **tags** compartidos, sugerir artículos similares al final de cada artículo.

### H2. Historial de Revisiones
Guardar cada versión editada del artículo para poder revertir cambios:
```php
// Tabla: articulo_revisiones
$table->unsignedBigInteger('id_articulo');
$table->longText('contenido_anterior');
$table->foreignId('editado_por')->constrained('users');
$table->string('motivo_edicion')->nullable();
```

### H3. Exportar Artículo a PDF
Botón para descargar el artículo como PDF formateado (para impresión o envío al usuario). Usa `barryvdh/laravel-dompdf` que ya tienes instalado.

### H4. Vinculación de Artículos con Equipos
Si un artículo soluciona un problema recurrente con un equipo específico (ej. "HP ProDesk 400 G7"), vincularlo al `TipoEquipo` o `Modelo` para que cuando un ticket sobre ese equipo llegue, la sugerencia aparezca automáticamente.

### H5. Estadísticas del Centro de Conocimiento
Panel para el administrador con:
- Artículos más vistos del mes
- Artículos más usados para resolver tickets
- Técnicos que más contribuyen
- Tags más populares
- Gráfico de crecimiento de la base (artículos por mes)

### H6. Modo "Wiki" Colaborativo
Permitir que varios técnicos editen el mismo artículo (como una wiki interna). El sistema guarda quién editó y cuándo.

### H7. Sistema de Comentarios en Artículos
Otros técnicos pueden dejar notas o correcciones al pie del artículo:
- "En mi experiencia, el paso 3 funciona mejor si..."
- "Ojo: en el modelo G8 el tornillo es Torx T8, no Phillips"

### H8. Integración con el Chat Flotante
Desde el panel de chat flotante (sugerencia A7), agregar un botón "📚 Buscar en KB" que permita buscar y pegar un link al artículo directamente en la conversación con el usuario.

---

## 📋 I. Orden de Implementación Sugerido

| Fase | Componente | Esfuerzo | Descripción |
|------|-----------|----------|-------------|
| **1** | Migraciones BD + Modelos | Bajo | Crear las 5 tablas nuevas y modificar `soluciones_tecnicas` |
| **2** | CRUD de Artículos (Controller + Views) | Medio | Index (blog), Create, Show, Edit con Quill mejorado |
| **3** | Mejorar formulario de Resolver Ticket | Medio | Agregar campos de diagnóstico, causa raíz, preventivas, tags, adjuntos y checkbox KB |
| **4** | Sistema de Tags | Bajo | Modelo Tag, relación pivot, input con autocomplete |
| **5** | Subida de archivos grandes | Medio | Zona de drag-and-drop, validaciones, disco dedicado en storage |
| **6** | Buscador full-text | Medio | Búsqueda por título, contenido, tags con LIKE o MySQL FULLTEXT |
| **7** | Sistema de Valoraciones (👍/👎) | Bajo | Votos por artículo, contador, feedback |
| **8** | Integración Ticket ↔ KB | Medio | Auto-publicar desde ticket, buscar KB desde ticket |
| **9** | Rutas + Permisos + Sidebar | Bajo | Registrar rutas, crear permisos Spatie, agregar al menú |
| **10** | Funcionalidades Extra (H1-H8) | Alto | Artículos relacionados, historial, PDF, estadísticas |

---

## 🎯 J. Resultado Final Esperado

Al completar este módulo, el Helpdesk tendrá:

1. **Un Centro de Conocimiento visualmente atractivo** (estilo blog con cards, tags de colores, filtros) donde los técnicos encuentran soluciones pasadas en segundos.
2. **Documentación técnica enriquecida** en cada ticket resuelto: diagnóstico, procedimiento paso a paso con capturas, causa raíz, acciones preventivas y archivos descargables.
3. **Un repositorio de herramientas** donde los técnicos pueden descargar ejecutables, drivers, utilidades que ya fueron probados y validados por el equipo.
4. **Formación continua** para técnicos nuevos que pueden leer artículos clasificados por dificultad (básica → avanzada) y aprender de los casos reales del equipo.
5. **Métricas de efectividad** que muestran cuáles artículos son más útiles, cuáles técnicos aportan más, y cómo crece la base de conocimiento con el tiempo.
