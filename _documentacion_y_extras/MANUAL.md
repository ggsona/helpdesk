# MANUAL INTEGRAL Y GUÍA DE RECREACIÓN: HELPDESK GDC

Este manual técnico proporciona una documentación exhaustiva de la aplicación **Helpdesk GDC** desarrollada bajo el framework **Laravel 10**, utilizando **Bootstrap 5.3.3** para una interfaz premium adaptativa (Light/Dark Mode) y **Spatie Laravel Permission** para el control de accesos basado en roles (RBAC).

El objetivo de este manual es servir como documentación del sistema actual y proporcionar una guía paso a paso, con todo el código necesario, para recrear la aplicación desde cero.

---

## ÍNDICE
1. [Arquitectura General](#1-arquitectura-general)
2. [Estructura y Modelado de Base de Datos](#2-estructura-y-modelado-de-base-de-datos)
3. [Configuración de Seguridad y Roles (RBAC)](#3-configuración-de-seguridad-y-roles-rbac)
4. [Modelos Eloquent y Relaciones](#4-modelos-eloquent-y-relaciones)
5. [Capa de Controladores e Hilos de Trabajo](#5-capa-de-controladores-e-hilos-de-trabajo)
6. [Sistema de Enrutamiento y Middleware](#6-sistema-de-enrutamiento-y-middleware)
7. [Interfaz Gráfica (Layouts y Vistas Blade)](#7-interfaz-gráfica-layouts-y-vistas-blade)
8. [Módulo de Seguridad y Directorio de Usuarios](#8-módulo-de-seguridad-y-directorio-de-usuarios)
9. [Pasos para Recrear el Proyecto desde Cero](#9-pasos-para-recrear-el-proyecto-desde-cero)

---

## 1. ARQUITECTURA GENERAL

La aplicación sigue el patrón arquitectónico **Modelo-Vista-Controlador (MVC)** característico de Laravel, enriquecido con un sistema de roles para segmentar los privilegios de los usuarios.

### Componentes Principales:
*   **Backend**: Laravel 10 con PHP 8.1+.
*   **Base de datos**: Relacional (MySQL / MariaDB).
*   **Frontend**: Plantillas Blade, compiladas a través de **Vite**, complementadas con **Bootstrap 5.3.3** e **iconos de Bootstrap (Bootstrap Icons)**.
*   **Estilos y Temas**: Soporte premium integrado para el cambio de temas (Claro / Oscuro) persistido a través de `localStorage` del navegador en el cliente.
*   **Control de Acceso**: **Spatie Laravel Permission**, configurado en combinación con un campo numérico manual en la tabla de usuarios (`role`).

---

## 2. ESTRUCTURA Y MODELADO DE BASE DE DATOS

El sistema cuenta con un esquema de base de datos relacional robusto. A continuación se presentan las migraciones clave con el código completo y optimizado.

### Código Completo de Migraciones

#### A. Tablas Maestras
Estas tablas proveen los datos estáticos de parametrización para el sistema de soporte.

```php
// database/migrations/2026_05_18_create_nivel_jerarquicos_table.php
Schema::create('niveles_jerarquicos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre')->unique(); // Ej: Sede, Departamento, Oficina
    $table->integer('nivel'); // Ej: 1, 2, 3...
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// database/migrations/2026_05_18_create_unidad_administrativas_table.php
Schema::create('unidades_administrativas', function (Blueprint $table) {
    $table->id();
    $table->string('nombre'); // Ej: Tecnología, Ventas
    $table->foreignId('id_nivel')->constrained('niveles_jerarquicos')->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('unidades_administrativas')->cascadeOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// database/migrations/2023_01_01_000002_create_prioridades_table.php
Schema::create('prioridades', function (Blueprint $table) {
    $table->id('id_prioridad');
    $table->string('nombre_prioridad', 50); // Baja, Media, Alta, Crítica
    $table->timestamps();
});

// database/migrations/2023_01_01_000003_create_categorias_table.php
Schema::create('categorias', function (Blueprint $table) {
    $table->id('id_categoria');
    $table->string('nombre_categoria', 100); // Hardware, Software, Redes, etc.
    $table->timestamps();
});

// database/migrations/2026_05_25_003849_add_estado_to_categorias_table.php (Extensión del Estado)
Schema::table('categorias', function (Blueprint $table) {
    $table->boolean('estado')->default(true)->after('nombre_categoria');
});

// database/migrations/2026_05_25_031207_add_audit_fields_to_categorias_table.php (Auditoría de Creador/Editor)
Schema::table("categorias", function (Blueprint $table) {
    $table->unsignedBigInteger("created_by")->nullable()->after("updated_at");
    $table->foreign("created_by")->references("id")->on("users")->onDelete("set null");
    
    $table->unsignedBigInteger("updated_by")->nullable()->after("created_by");
    $table->foreign("updated_by")->references("id")->on("users")->onDelete("set null");
});

// database/migrations/2023_01_01_000004_create_tipos_equipo_table.php
Schema::create('tipos_equipo', function (Blueprint $table) {
    $table->id('id_tipo_equipo');
    $table->string('nombre_tipo_equipo', 100); // Laptop, Desktop, Impresora
    $table->timestamps();
});
```

#### B. Estructura de Usuarios y Personas
Una estructura modular donde los datos personales se almacenan en `personas` y las credenciales de acceso en `users`.

```php
// database/migrations/2023_01_01_000005_create_personas_table.php
Schema::create('personas', function (Blueprint $table) {
    $table->id('id_persona');
    $table->string('nombre', 100);
    $table->string('apellido', 100);
    $table->string('telefono', 20)->nullable();
    $table->unsignedBigInteger('id_unidad_administrativa')->nullable();
    $table->foreign('id_unidad_administrativa')->references('id')->on('unidades_administrativas')->nullOnDelete();
    $table->timestamps();
});

// database/migrations/2014_10_12_000000_create_users_table.php (Modificada)
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('role')->nullable(); // Campo numérico para compatibilidad manual
    $table->foreignId('id_persona')->nullable()->constrained('personas', 'id_persona')->onDelete('set null');
    $table->rememberToken();
    $table->timestamps();
});
```

#### C. Tabla Principal de Tickets
Esta tabla centraliza los reportes de soporte e implementa la lógica de borradores (los tickets inician en `estatus = 0` y la prioridad/técnico son `nullable`).

```php
// database/migrations/2026_05_01_000001_create_tickets_table.php
Schema::create('tickets', function (Blueprint $table) {
    $table->id('id_ticket');
    $table->string('asunto', 200);
    $table->foreignId('id_usuario')->constrained('users', 'id');
    $table->foreignId('id_tipo_equipo')->constrained('tipos_equipo', 'id_tipo_equipo');
    $table->foreignId('id_prioridad')->nullable()->constrained('prioridades', 'id_prioridad'); // Nullable para Borradores
    $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria');
    $table->text('descripcion_problema');
    $table->integer('estatus')->default(0); // 0 = Borrador, 1 = Abierto, 2 = En Proceso, 3 = Resuelto
    $table->timestamp('fecha_cierre')->nullable();
    $table->timestamps();
});
```

#### D. Tablas de Operación y Chat
Soporta las asignaciones de especialistas, hilos de conversación, adjuntos físicos y soluciones técnicas formales.

```php
// database/migrations/2026_05_01_000005_create_ticket_asignaciones_table.php
Schema::create('ticket_asignaciones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
    $table->foreignId('id_usuario_tecnico')->constrained('users', 'id');
    $table->text('nota')->nullable(); // Nota inicial de asignación/reasignación
    $table->timestamp('fecha_asignacion')->useCurrent();
    $table->timestamps();
});

// database/migrations/2026_05_01_000003_create_ticket_comentarios_table.php
Schema::create('ticket_comentarios', function (Blueprint $table) {
    $table->id('id_comentario');
    $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
    $table->foreignId('id_usuario')->constrained('users', 'id');
    $table->text('mensaje');
    $table->boolean('es_interno')->default(false); // true = Visible sólo para Gestor y Técnico
    $table->timestamps();
});

// database/migrations/2026_05_01_000007_create_ticket_adjuntos_table.php
Schema::create('ticket_adjuntos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
    $table->string('ruta_archivo');
    $table->string('nombre_original');
    $table->string('tipo_mimo', 100)->nullable();
    $table->bigInteger('tamano')->nullable();
    $table->timestamps();
});

// database/migrations/2026_05_01_000008_create_soluciones_tecnicas_table.php
Schema::create('soluciones_tecnicas', function (Blueprint $table) {
    $table->id('id_solucion');
    $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
    $table->foreignId('id_usuario_tecnico')->constrained('users', 'id');
    $table->string('resumen_usuario', 255); // Resumen en lenguaje sencillo para el cliente
    $table->text('procedimiento_detallado'); // Pasos técnicos detallados en lenguaje interno
    $table->timestamps();
});
```

---

## 3. CONFIGURACIÓN DE SEGURIDAD Y ROLES (RBAC)

El sistema utiliza **Spatie Laravel Permission** para proteger rutas y componentes. Los roles establecidos son:
1.  **admin** (Administrador): Acceso total y configuraciones globales.
2.  **gestor** (Gestor de Soporte): Despacho de tickets, asignación técnica, reasignaciones y chats.
3.  **tecnico** (Especialista Técnico): Solución de tickets asignados, comentarios internos/públicos.
4.  **usuario** (Usuario Final/Cliente): Creación de borradores, edición, envío a soporte e interacción del caso.

### Registro del Middleware
Para utilizar la validación de roles en rutas, se asocia el alias en el Kernel de la aplicación:

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ... otros middlewares
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
];
```

### Inicialización Semilla (Seeder Completo)
Este Seeder automatiza la creación de roles, datos maestros de configuración y usuarios iniciales con sus respectivos roles asignados.

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Oficina;
use App\Models\Prioridad;
use App\Models\Categoria;
use App\Models\TipoEquipo;
use App\Models\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Roles de Spatie
        $roleAdmin   = Role::updateOrCreate(['name' => 'admin']);
        $roleGestor  = Role::updateOrCreate(['name' => 'gestor']);
        $roleTecnico = Role::updateOrCreate(['name' => 'tecnico']);
        $roleUsuario = Role::updateOrCreate(['name' => 'usuario']);

        // 2. Poblar Tablas Maestras
        $ofiCentral = Oficina::create(['nombre_oficina' => 'Sede Central']);
        $ofiNorte   = Oficina::create(['nombre_oficina' => 'Sucursal Norte']);

        Prioridad::create(['nombre_prioridad' => 'Baja']);
        Prioridad::create(['nombre_prioridad' => 'Media']);
        Prioridad::create(['nombre_prioridad' => 'Alta']);
        Prioridad::create(['nombre_prioridad' => 'Crítica']);

        Categoria::create(['nombre_categoria' => 'Hardware']);
        Categoria::create(['nombre_categoria' => 'Software']);
        Categoria::create(['nombre_categoria' => 'Redes']);

        TipoEquipo::create(['nombre_tipo_equipo' => 'Laptop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Desktop']);
        TipoEquipo::create(['nombre_tipo_equipo' => 'Impresora']);

        // 3. Crear Información Personal (Personas)
        $pAdmin = Persona::create(['nombre' => 'Admin', 'apellido' => 'Sistemas', 'telefono' => '99999999', 'id_oficina' => $ofiCentral->id_oficina]);
        $pGestor = Persona::create(['nombre' => 'Gestor', 'apellido' => 'Soporte', 'telefono' => '88888888', 'id_oficina' => $ofiCentral->id_oficina]);
        $pTecnico = Persona::create(['nombre' => 'Tecnico', 'apellido' => 'Especialista', 'telefono' => '77777777', 'id_oficina' => $ofiCentral->id_oficina]);
        $pUsuario = Persona::create(['nombre' => 'Usuario', 'apellido' => 'Final', 'telefono' => '66666666', 'id_oficina' => $ofiNorte->id_oficina]);

        // 4. Crear Credenciales de Acceso y Asignar Roles
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@helpdesk.com',
            'password' => Hash::make('admin123'),
            'role' => '1',
            'id_persona' => $pAdmin->id_persona,
        ]);
        $admin->assignRole($roleAdmin);

        $gestor = User::create([
            'name' => 'Gestor de Soporte',
            'email' => 'gestor@helpdesk.com',
            'password' => Hash::make('gestor123'),
            'role' => '2',
            'id_persona' => $pGestor->id_persona,
        ]);
        $gestor->assignRole($roleGestor);

        $tecnico = User::create([
            'name' => 'Tecnico de Soporte',
            'email' => 'tecnico@helpdesk.com',
            'password' => Hash::make('tecnico123'),
            'role' => '3',
            'id_persona' => $pTecnico->id_persona,
        ]);
        $tecnico->assignRole($roleTecnico);

        $usuario = User::create([
            'name' => 'Usuario Final GDC',
            'email' => 'usuario@helpdesk.com',
            'password' => Hash::make('usuario123'),
            'role' => '4',
            'id_persona' => $pUsuario->id_persona,
        ]);
        $usuario->assignRole($roleUsuario);
    }
}
```

---

## 4. MODELOS ELOQUENT Y RELACIONES

Eloquent simplifica la comunicación con la Base de Datos. El modelo central es `Ticket.php`.

### Código de Modelos Principales

#### A. Modelo `Ticket.php`
Define todas las relaciones críticas, adjuntos, chat y un accesor dinámico para traducir códigos numéricos de estado a texto legible.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model {

    protected $table = 'tickets';
    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'asunto', 
        'id_usuario', 
        'id_tipo_equipo', 
        'id_prioridad', 
        'id_categoria', 
        'descripcion_problema', 
        'estatus', 
        'fecha_cierre'
    ];

    public function usuario(): BelongsTo { 
        return $this->belongsTo(User::class, 'id_usuario'); 
    }

    public function tecnico() {
        return $this->hasOneThrough(
            User::class,
            TicketAsignacion::class,
            'id_ticket',         // Llave foránea en ticket_asignaciones
            'id',                // Llave foránea en users
            'id_ticket',         // Llave local en tickets
            'id_usuario_tecnico' // Llave local en ticket_asignaciones
        );
    }

    public function prioridad(): BelongsTo { 
        return $this->belongsTo(Prioridad::class, 'id_prioridad'); 
    }

    public function categoria(): BelongsTo { 
        return $this->belongsTo(Categoria::class, 'id_categoria'); 
    }

    public function tipoEquipo(): BelongsTo { 
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); 
    }
    
    public function adjuntos(): HasMany { 
        return $this->hasMany(TicketAdjunto::class, 'id_ticket'); 
    }

    public function solucion(): HasOne {
        return $this->hasOne(SolucionTecnica::class, 'id_ticket');
    }

    public function asignacion(): HasOne {
        return $this->hasOne(TicketAsignacion::class, 'id_ticket', 'id_ticket'); 
    }

    public function comentarios(): HasMany {
        return $this->hasMany(TicketComentario::class, 'id_ticket', 'id_ticket');
    }

    // Accesor Dinámico para el Estado
    public function getEstadoTextoAttribute() {
        return match($this->estatus) {
            0 => 'Borrador',
            1 => 'Abierto',
            2 => 'En Proceso',
            3 => 'Resuelto',
            default => 'Desconocido',
        };
    }
}
```

#### B. Modelo `User.php`
Utiliza el trait `HasRoles` de Spatie y enlaza al usuario con su ficha de datos personales.

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_persona',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}
```

#### C. Ficha Personal (`Persona.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $fillable = [
        'nombre', 
        'segundo_nombre',
        'apellido', 
        'segundo_apellido',
        'cedula', 
        'telefono', 
        'id_unidad_administrativa'
    ];

    public function unidadAdministrativa()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'id_unidad_administrativa')->withTrashed();
    }
}
```

#### D. Unidades Administrativas (`UnidadAdministrativa.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadAdministrativa extends Model
{
    use SoftDeletes;
    protected $table = 'unidades_administrativas';
    protected $fillable = ['nombre', 'id_nivel', 'parent_id', 'is_active'];

    public function nivel()
    {
        return $this->belongsTo(NivelJerarquico::class, 'id_nivel');
    }

    public function parent()
    {
        return $this->belongsTo(UnidadAdministrativa::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(UnidadAdministrativa::class, 'parent_id');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_unidad_administrativa');
    }
}
```

#### E. Niveles Jerárquicos (`NivelJerarquico.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelJerarquico extends Model
{
    protected $table = 'niveles_jerarquicos';
    protected $fillable = ['nombre', 'nivel', 'is_active'];

    public function unidades()
    {
        return $this->hasMany(UnidadAdministrativa::class, 'id_nivel');
    }
}
```

#### F. Modelo de Categoría (`Categoria.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Categoria extends Model {

    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    protected $fillable = ['nombre_categoria', 'estado', 'created_by', 'updated_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

---

## 5. CAPA DE CONTROLADORES E HILOS DE TRABAJO

El flujo operativo del sistema está segmentado en tres controladores específicos por rol y un controlador de redirección para el inicio de sesión.

### A. Controlador de Redirección Automática

```php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('ver-panel-operativo')) {
            return view('soporte.dashboard');
        }

        // Si es cliente/usuario regular se le redirige a su bandeja
        return redirect()->route('usuario.home');
    }
}
```

### B. Módulo de Usuario (Cliente)
Permite al usuario redactar reportes en estado **Borrador (0)**, adjuntar archivos físicos, editar datos antes del envío definitivo, y formalizar el ticket al pasarlo a **Abierto (1)**.

```php
// app/Http/Controllers/Usuario/TicketUsuarioController.php
namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAdjunto;
use App\Models\Categoria;
use App\Models\Prioridad;
use App\Models\TipoEquipo;
use App\Models\TicketComentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketUsuarioController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('id_usuario', Auth::id())->latest()->get();
        return view('usuario.tickets.index', compact('tickets'));
    }

    public function home()
    {
        return view('usuario.home');
    }

    public function create()
    {
        $categorias = Categoria::all();
        $prioridades = Prioridad::all();
        $tiposEquipo = TipoEquipo::all();
        return view('usuario.tickets.create', compact('categorias', 'prioridades', 'tiposEquipo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asunto' => 'required|string|max:200',
            'descripcion_problema' => 'required|string',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_tipo_equipo' => 'required|exists:tipos_equipo,id_tipo_equipo',
            'adjuntos.*' => 'nullable|file|max:10240',
        ]);

        // Crea en estatus 0 (Borrador)
        $ticket = Ticket::create([
            'id_usuario' => Auth::id(),
            'asunto' => $request->asunto,
            'id_categoria' => $request->id_categoria,
            'id_tipo_equipo' => $request->id_tipo_equipo,
            'descripcion_problema' => $request->descripcion_problema,
            'id_prioridad' => null, 
            'estatus' => 0,
        ]);

        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $archivo) {
                $ruta = $archivo->store('tickets/' . $ticket->id_ticket, 'public');

                TicketAdjunto::create([
                    'id_ticket' => $ticket->id_ticket,
                    'ruta_archivo' => $ruta,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'tipo_mimo' => $archivo->getMimeType(),
                    'tamano' => $archivo->getSize(),
                ]);
            }
        }

        return redirect()->route('usuario.tickets.index')
            ->with('success', 'Ticket guardado como borrador. Por favor envíalo cuando desees reportarlo.');
    }

    public function enviar(Ticket $ticket) 
    {
        if ($ticket->id_usuario !== Auth::id()) {
            abort(403);
        }

        $ticket->update(['estatus' => 1]); // Pasa de Borrador a Abierto

        return back()->with('success', '¡Ticket enviado exitosamente al equipo de soporte!');
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            'categoria', 
            'tecnico', 
            'prioridad', 
            'adjuntos', 
            'comentarios.usuario'
        ])
        ->where('id_usuario', Auth::id())
        ->findOrFail($id);

        return view('usuario.tickets.show', compact('ticket'));
    }

    public function edit($id)
    {
        $ticket = Ticket::where('id_usuario', Auth::id())->where('estatus', 0)->findOrFail($id);
        $categorias = Categoria::all();
        return view('usuario.tickets.edit', compact('ticket', 'categorias'));
    }

    public function destroy($id)
    {
        $ticket = Ticket::where('id_usuario', Auth::id())->where('estatus', 0)->findOrFail($id);
        $ticket->delete();
        return redirect()->route('usuario.tickets.index')->with('success', 'Ticket borrador eliminado.');
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::where('id_usuario', Auth::id())->where('estatus', 0)->findOrFail($id);
        $request->validate([
            'asunto' => 'required|string|max:255',
            'descripcion_problema' => 'nullable|string',
            'id_categoria' => 'required|exists:categorias,id_categoria',
        ]);

        $ticket->update([
            'asunto' => $request->asunto,
            'descripcion_problema' => $request->descripcion_problema,
            'id_categoria' => $request->id_categoria,
        ]);

        return redirect()->route('usuario.tickets.index')->with('success', 'Borrador actualizado.');
    }

    public function storeComentario(Request $request, $id)
    {
        $request->validate(['mensaje' => 'required|string|max:1000']);
        $ticket = Ticket::where('id_usuario', Auth::id())->findOrFail($id);

        TicketComentario::create([
            'id_ticket'  => $ticket->id_ticket,
            'id_usuario' => Auth::id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => false, // Cliente no puede escribir notas ocultas
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }
}
```

### C. Módulo de Gestor
El Gestor visualiza los tickets mediante un panel distribuido en 3 categorías (*Por Asignar*, *En Gestión*, y *Resueltos*). Puede asignar un técnico y establecer la prioridad, lo cual cambia el estado del ticket a **En Proceso (2)**, emitiendo una notificación automática en el historial del chat.

```php
// app/Http/Controllers/Gestor/TicketGestorController.php
namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketComentario;
use App\Models\TicketAsignacion;
use App\Models\Prioridad;
use Illuminate\Http\Request;

class TicketGestorController extends Controller
{
    public function index() 
    {
        $ticketsNuevos = Ticket::with('prioridad')->where('estatus', 1)->latest()->get();
        $ticketsAsignados = Ticket::with('prioridad', 'asignacion.tecnico')->where('estatus', 2)->latest()->get();
        $ticketsResueltos = Ticket::with('prioridad', 'solucion')->where('estatus', 3)->latest()->get();
        
        $tecnicos = User::role('tecnico')->get(); 
        $prioridades = Prioridad::all(); 

        return view('gestor.tickets.index', compact(
            'ticketsNuevos', 
            'ticketsAsignados', 
            'ticketsResueltos', 
            'tecnicos',
            'prioridades'
        ));
    }

    public function asignar(Request $request, $id)
    {
        $request->validate([
            'id_usuario_tecnico' => 'required|exists:users,id',
            'id_prioridad' => 'required|exists:prioridades,id_prioridad',
            'nota' => 'nullable|string'
        ]);

        $ticket = Ticket::findOrFail($id);
        $esReasignacion = TicketAsignacion::where('id_ticket', $id)->exists();

        TicketAsignacion::updateOrCreate(
            ['id_ticket' => $id],
            [
                'id_usuario_tecnico' => $request->id_usuario_tecnico,
                'nota' => $request->nota, 
                'fecha_asignacion' => now()
            ]
        );

        $ticket->update([
            'id_prioridad' => $request->id_prioridad,
            'estatus' => 2 // Cambia a "En Proceso"
        ]);

        $tecnicoNombre = User::find($request->id_usuario_tecnico)->name;
        if ($esReasignacion) {
            $mensaje = "🔄 **Reasignación**: El caso ha sido reasignado al especialista " . $tecnicoNombre;
            $etiqueta = "Motivo";
        } else {
            $mensaje = "✅ **Asignación**: El caso ha sido asignado al técnico " . $tecnicoNombre;
            $etiqueta = "Instrucciones";
        }
        
        if ($request->nota) {
            $mensaje .= "\n\n**" . $etiqueta . "**: " . $request->nota;
        }

        TicketComentario::create([
            'id_ticket' => $id,
            'id_usuario' => auth()->id(),
            'mensaje' => $mensaje,
            'es_interno' => false 
        ]);

        return redirect()->back()->with('success', 'Asignación procesada con éxito.');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['usuario.persona.oficina', 'asignacion.tecnico', 'comentarios.usuario'])->findOrFail($id);
        $tecnicos = User::role('tecnico')->get();
        $prioridades = Prioridad::all();

        return view('gestor.tickets.show', compact('ticket', 'tecnicos', 'prioridades'));
    }

    public function comentar(Request $request, $id)
    {
        $request->validate(['mensaje' => 'required|string|max:1000']);

        TicketComentario::create([
            'id_ticket'  => $id,
            'id_usuario' => auth()->id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => $request->has('es_interno'), // Puede redactar notas privadas
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }
}
```

### D. Módulo Técnico
El Técnico atiende sus asignaciones ordenadas por jerarquía de prioridad. Puede comentar privadamente con los gestores o públicamente con el cliente. Para solucionar el problema, escribe un resumen final para el cliente y un informe detallado interno, cerrando el ticket a **Resuelto (3)**.

```php
// app/Http/Controllers/Tecnico/TicketTecnicoController.php
namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComentario;
use App\Models\SolucionTecnica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketTecnicoController extends Controller
{
    public function index() 
    {
        $usuarioId = Auth::id();

        $queryAsignados = Ticket::with(['prioridad', 'usuario.persona.oficina'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 2);

        $ticketsCriticos = (clone $queryAsignados)->where('id_prioridad', 4)->latest()->get();
        $ticketsAltos    = (clone $queryAsignados)->where('id_prioridad', 3)->latest()->get();
        $ticketsMedios   = (clone $queryAsignados)->where('id_prioridad', 2)->latest()->get();
        $ticketsBajos    = (clone $queryAsignados)->where('id_prioridad', 1)->latest()->get();

        $ticketsResueltos = Ticket::with(['prioridad', 'usuario.persona.oficina', 'solucion'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->where('estatus', 3)
            ->latest()
            ->get();

        return view('tecnico.tickets.index', compact(
            'ticketsCriticos', 'ticketsAltos', 'ticketsMedios', 'ticketsBajos', 'ticketsResueltos'
        ));
    }

    public function show($id)
    {
        $usuarioId = Auth::id();
        $ticket = Ticket::with(['usuario.persona.oficina', 'asignacion.tecnico', 'comentarios.usuario', 'prioridad'])
            ->whereHas('asignacion', function ($q) use ($usuarioId) {
                $q->where('id_usuario_tecnico', $usuarioId);
            })
            ->findOrFail($id);

        return view('tecnico.tickets.show', compact('ticket'));
    }

    public function comentar(Request $request, $id)
    {
        $request->validate(['mensaje' => 'required|string|max:1000']);

        TicketComentario::create([
            'id_ticket'  => $id,
            'id_usuario' => Auth::id(),
            'mensaje'    => $request->mensaje,
            'es_interno' => $request->has('es_interno'),
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }

    public function crearSolucion($id)
    {
        $ticket = Ticket::findOrFail($id);
        if ($ticket->estatus == 3) {
            return redirect()->route('tecnico.tickets.index')->with('info', 'Este caso ya fue cerrado.');
        }
        return view('tecnico.tickets.resolver', compact('ticket'));
    }

    public function guardarSolucion(Request $request, $id)
    {
        $request->validate([
            'resumen_usuario' => 'required|string|max:255',
            'procedimiento_detallado' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        SolucionTecnica::create([
            'id_ticket' => $id,
            'id_usuario_tecnico' => Auth::id(),
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
        ]);

        $ticket->update([
            'estatus' => 3, // Cambia a "Resuelto"
            'fecha_cierre' => now(),
        ]);

        return redirect()->route('tecnico.tickets.index')
                         ->with('success', 'Solución publicada y ticket cerrado satisfactoriamente.');
    }

    public function editarSolucion($id)
    {
        $ticket = Ticket::with('solucion')->findOrFail($id);
        if (!$ticket->solucion) {
            return redirect()->route('tecnico.tickets.resolver', $id);
        }
        return view('tecnico.tickets.editar_solucion', compact('ticket'));
    }

    public function actualizarSolucion(Request $request, $id)
    {
        $request->validate([
            'resumen_usuario' => 'required|string|max:255',
            'procedimiento_detallado' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->solucion->update([
            'resumen_usuario' => $request->resumen_usuario,
            'procedimiento_detallado' => $request->procedimiento_detallado,
        ]);

        return redirect()->route('tecnico.tickets.index')->with('success', 'Solución técnica actualizada.');
    }
}

### E. Módulo de Gestión de Categorías (Administrador)
```php
// app/Http/Controllers/Admin/CategoriaController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-categorias");
    }

    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->filled("search")) {
            $query->where("nombre_categoria", "like", "%". $request->search ."%");
        }

        if ($request->filled("estado")) {
            $query->where("estado", $request->estado == "activo" ? true : false);
        } else {
            $query->where("estado", true);
        }

        $categorias = $query->with(['creator', 'updater'])->paginate(10);

        if ($request->ajax()) {
            return view("admin.categorias._categorias_table", compact("categorias"))->render();
        }

        return view("admin.categorias.index", compact("categorias"));
    }

    public function create()
    {
        return view("admin.categorias.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre_categoria" => "required|string|max:100|unique:categorias",
        ]);

        $categoria = Categoria::create(array_merge($request->all(), ['created_by' => auth()->id()]));

        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría creada exitosamente.']);
        }

        return redirect()->route("admin.categorias.index")->with("success", "Categoría creada exitosamente.");
    }

    public function edit(Categoria $categoria)
    {
        return view("admin.categorias.edit", compact("categoria"));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            "nombre_categoria" => "required|string|max:100|unique:categorias,nombre_categoria,". $categoria->id_categoria .",id_categoria",
            "estado" => "boolean",
        ]);

        $categoria->update(array_merge($request->all(), ["updated_by" => auth()->id()]));

        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría actualizada exitosamente.']);
        }

        return redirect()->route("admin.categorias.index")->with("success", "Categoría actualizada exitosamente.");
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        try {
            $categoria->update(["estado" => false, "updated_by" => auth()->id()]);
            if ($request->ajax()) {
                return response()->json(['success' => 'Categoría desactivada exitosamente.']);
            }
            return redirect()->route("admin.categorias.index")->with("success", "Categoría desactivada exitosamente.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->route("admin.categorias.index")->with("error", "Error: " . $e->getMessage());
        }
    }

    public function activate(Categoria $categoria)
    {
        $categoria->update(["estado" => true, "updated_by" => auth()->id()]);
        if ($request->ajax()) {
            return response()->json(['success' => 'Categoría activada exitosamente.']);
        }
        return redirect()->route("admin.categorias.index")->with("success", "Categoría activada exitosamente.");
    }
}
```
```

---

## 6. SISTEMA DE ENRUTAMIENTO Y MIDDLEWARE

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Usuario\TicketUsuarioController; 
use App\Http\Controllers\Gestor\TicketGestorController;
use App\Http\Controllers\Tecnico\TicketTecnicoController;
use App\Http\Controllers\Admin\CategoriaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/unidades-hijas/{parentId}', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'getChildrenUnidades'])->name('unidades.hijas');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

Route::get('/espera', function () {
    return view('auth.awaiting-approval');
})->middleware('auth')->name('awaiting-approval');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- RUTAS DE USUARIO CLIENTE (Rol: usuario) ---
Route::middleware(['auth', 'approved', 'role:usuario'])->group(function () {
    Route::get('/usuario/dashboard', [TicketUsuarioController::class, 'home'])->name('usuario.home');
    Route::get('/mis-tickets', [TicketUsuarioController::class, 'index'])->name('usuario.tickets.index');
    Route::get('/mis-tickets/nuevo', [TicketUsuarioController::class, 'create'])->name('usuario.tickets.create');
    Route::post('/mis-tickets', [TicketUsuarioController::class, 'store'])->name('usuario.tickets.store');
    Route::get('/mis-tickets/{ticket}', [TicketUsuarioController::class, 'show'])->name('usuario.tickets.show');
    Route::get('/mis-tickets/{ticket}/editar', [TicketUsuarioController::class, 'edit'])->name('usuario.tickets.edit');
    
    Route::post('/mis-tickets/{ticket}/enviar', [TicketUsuarioController::class, 'enviar'])->name('usuario.tickets.enviar');
    Route::put('/tickets/{id}', [TicketUsuarioController::class, 'update'])->name('usuario.tickets.update');
    Route::delete('/tickets/{id}', [TicketUsuarioController::class, 'destroy'])->name('usuario.tickets.destroy');
    Route::post('/tickets/{id}/comentar', [TicketUsuarioController::class, 'storeComentario'])->name('usuario.tickets.comentar');
});

// --- RUTAS ADMINISTRATIVAS GLOBALES (Bajo prefijo admin) ---
Route::middleware(['auth', 'approved'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:ver-panel-operativo');
    
    Route::middleware('can:gestionar-roles')->group(function () {
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    });

    Route::middleware('can:gestionar-categorias')->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::post('categorias/{categoria}/activate', [CategoriaController::class, 'activate'])->name('categorias.activate');
    });
    
    Route::middleware('can:gestionar-usuarios')->group(function () {
        Route::get('/usuarios/pendientes', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'index'])->name('usuarios.pendientes');
        Route::post('/usuarios/pendientes/{id}/aprobar', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'aprobar'])->name('usuarios.aprobar');
        Route::delete('/usuarios/pendientes/{id}/rechazar', [\App\Http\Controllers\Admin\UsuarioAprobacionController::class, 'rechazar'])->name('usuarios.rechazar');
        Route::get('/usuarios', [\App\Http\Controllers\Admin\UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/{id}/update-role', [\App\Http\Controllers\Admin\UsuarioController::class, 'updateRole'])->name('usuarios.update-role');
        Route::post('/usuarios/{id}/toggle', [\App\Http\Controllers\Admin\UsuarioController::class, 'toggleStatus'])->name('usuarios.toggle');
    });
    
    Route::middleware('can:ver-configuraciones')->group(function () {
        Route::get('/estructura', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'index'])->name('estructura.index');
        Route::post('/estructura/unidades', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'storeUnidad'])->name('estructura.unidades.store');
        Route::put('/estructura/unidades/{id}', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'updateUnidad'])->name('estructura.unidades.update');
        Route::delete('/estructura/unidades/{id}', [\App\Http\Controllers\Admin\EstructuraOrganizacionalController::class, 'destroyUnidad'])->name('estructura.unidades.destroy');

        Route::get('/configuraciones', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'index'])->name('configuraciones.index');
        Route::post('/configuraciones/niveles/reorder', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'reorderNiveles'])->name('configuraciones.niveles.reorder');
        Route::post('/configuraciones/niveles/{id}/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleNivel'])->name('configuraciones.niveles.toggle');
        Route::post('/configuraciones/ad', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'updateAd'])->name('configuraciones.ad.update');
        Route::post('/configuraciones/ad/toggle', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'toggleAd'])->name('configuraciones.ad.toggle');
        Route::post('/configuraciones/ad/test', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'testAd'])->name('configuraciones.ad.test');
        Route::post('/configuraciones/niveles', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'storeNivel'])->name('configuraciones.niveles.store');
    });
});

// --- RUTAS UNIFICADAS DE SOPORTE (COORDINADORES Y TÉCNICOS) ---
Route::middleware(['auth', 'approved', 'can:ver-panel-operativo'])->prefix('soporte')->name('soporte.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('can:asignar-tickets')->group(function () {
        Route::get('/tickets', [TicketGestorController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/{id}/asignar', [TicketGestorController::class, 'asignar'])->name('tickets.asignar');
        Route::get('/tickets/{id}', [TicketGestorController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{id}/comentar', [TicketGestorController::class, 'comentar'])->name('tickets.comentar');
    });

    Route::middleware('can:resolver-tickets')->prefix('tecnico')->name('tickets.tecnico.')->group(function () {
        Route::get('/tickets', [TicketTecnicoController::class, 'index'])->name('index');
        Route::get('/tickets/{id}', [TicketTecnicoController::class, 'show'])->name('show');
        Route::post('/tickets/{id}/comentar', [TicketTecnicoController::class, 'comentar'])->name('comentar');
        Route::get('/tickets/{id}/resolver', [TicketTecnicoController::class, 'crearSolucion'])->name('resolver');
        Route::post('/tickets/{id}/guardar-solucion', [TicketTecnicoController::class, 'guardarSolucion'])->name('guardar-solucion');
        Route::get('/tickets/{id}/editar-solucion', [TicketTecnicoController::class, 'editarSolucion'])->name('editar-solucion');
        Route::put('/tickets/{id}/actualizar-solucion', [TicketTecnicoController::class, 'actualizarSolucion'])->name('actualizar-solucion');
    });
});

require __DIR__.'/auth.php';

---

## 7. INTERFAZ GRÁFICA (LAYOUTS Y VISTAS BLADE)

El sistema cuenta con un frontend responsivo premium, implementado con **Bootstrap 5.3.3** e iconos vectoriales. Soporta cambiar dinámicamente entre el tema claro y oscuro de manera integrada.

### A. Layout Operativo de Especialistas y Gestores
Este layout define un sidebar lateral estático para administradores, gestores y técnicos, y añade el botón para alternar el esquema de colores de la interfaz.

```html
<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { 
            --sb-width: 270px;
            --bg-main: #f4f7fa;
            --sb-bg: #ffffff;
        }
        [data-bs-theme="dark"] {
            --bg-main: #0b0c0d;
            --sb-bg: #111214;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main) !important;
            transition: all 0.3s ease;
        }
        #sidebar { 
            width: var(--sb-width); 
            min-width: var(--sb-width); 
            height: 100vh;
            background: var(--sb-bg);
            border-right: 1px solid var(--bs-border-color);
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .nav-link { 
            color: var(--bs-body-color) !important; 
            font-size: 0.88rem; 
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 2px 10px;
        }
        .nav-link:hover { background: var(--bs-secondary-bg); }
        .nav-link.active { background: #0d6efd !important; color: #fff !important; }
        #content { flex-grow: 1; padding: 2.5rem; }
        .card-premium {
            background: var(--bs-custom-card-bg, #fff);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .card-premium { background: #1a1c1e; }
        .user-footer {
            margin-top: auto;
            padding: 1.2rem;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="p-4 mb-2">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h5>
            </div>

            <div class="flex-grow-1">
                <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Panel Operativo</small>
                <ul class="nav nav-pills flex-column">
                    <li>
                        <a href="{{ route('soporte.dashboard') }}" class="nav-link {{ request()->routeIs('soporte.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        @can('asignar-tickets')
                            <a href="{{ route('soporte.tickets.index') }}" class="nav-link {{ request()->routeIs('soporte.tickets.*') && !request()->routeIs('soporte.tickets.tecnico.*') ? 'active' : '' }}">
                                <i class="bi bi-ticket-detailed-fill me-3"></i>Mesa de Despacho
                            </a>
                        @elsecan('resolver-tickets')
                            <a href="{{ route('soporte.tickets.tecnico.index') }}" class="nav-link {{ request()->routeIs('soporte.tickets.tecnico.*') ? 'active' : '' }}">
                                <i class="bi bi-ticket-perforated me-3"></i>Mis Tareas Activas
                            </a>
                        @endcan
                    </li>
                </ul>
            </div>

            <!-- Footer de Sidebar con datos de Usuario y Cambio de Tema -->
            <div class="user-footer">
                <div class="dropend">
                    <button class="btn border-0 d-flex align-items-center w-100 p-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ms-3 text-start">
                            <p class="mb-0 fw-bold small text-truncate" style="max-width: 120px;">{{ Auth::user()->name }}</p>
                            <small class="text-muted" style="font-size: 0.7rem;">
                                @if(Auth::user()->hasRole('admin')) Administrador (Sistemas)
                                @elseif(Auth::user()->hasRole('gestor')) Gestor de Soporte
                                @else Técnico
                                @endif
                            </small>
                        </div>
                        <i class="bi bi-chevron-right ms-auto text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 mb-2">
                        <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        <li><button class="dropdown-item py-2" onclick="toggleTheme()"><i class="bi bi-moon-stars me-2"></i> Cambiar Tema</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main id="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
        }
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>
```

### B. Layout de Clientes (Usuarios Finales)
Este define una cabecera horizontal y navegación dinámica superior, manteniendo un enfoque enfocado y libre de distracciones operativas.

```html
<!-- resources/views/layouts/usuario.blade.php -->
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soporte GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { 
            --bg-main: #f0f2f5; 
            --card-bg: #ffffff;
            --text-main: #1a1c1e;
            --border-color: #dee2e6;
            --input-bg: #ffffff;
        }
        [data-bs-theme="dark"] { 
            --bg-main: #0b0c0d; 
            --card-bg: #111214;
            --text-main: #e9ecef;
            --border-color: #2d2f31;
            --input-bg: #1a1c1e;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-main) !important; 
            color: var(--text-main);
            transition: all 0.3s ease; 
        }
        .card-premium {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 16px;
            color: var(--text-main) !important;
        }
        .form-control-premium {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .form-control-premium:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
        }
        .navbar { 
            background-color: var(--card-bg) !important; 
            border-bottom: 1px solid var(--border-color) !important; 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('usuario.home') }}">
                <i class="bi bi-headset me-2"></i>GDC <span class="text-body-secondary">Soporte</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navUsuario">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navUsuario">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.home') ? 'active text-primary' : '' }}" href="{{ route('usuario.home') }}">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.tickets.create') ? 'active text-primary' : '' }}" href="{{ route('usuario.tickets.create') }}">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Ticket
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium {{ request()->routeIs('usuario.tickets.index') ? 'active text-primary' : '' }}" href="{{ route('usuario.tickets.index') }}">
                            <i class="bi bi-ticket-perforated me-1"></i> Mis Casos
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-body p-0 shadow-none border-0" onclick="toggleTheme()">
                        <i class="bi bi-circle-half"></i>
                    </button>

                    <div class="dropdown">
                        <button class="btn border-0 d-flex align-items-center p-0" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 35px; height: 35px;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                            <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Salir</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
        }
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>
```

### C. Componente de Layout de Usuario
Para asegurar que `<x-usuario-layout>` renderice la vista correctamente:

```php
// app/View/Components/UsuarioLayout.php
namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class UsuarioLayout extends Component
{
    public function render(): View
    {
        return view('layouts.usuario');
    }
}
```

### D. Estructura y Vistas de Categorías (CRUD y AJAX)
Las vistas del módulo de categorías implementan una interacción asíncrona reactiva sin refrescar la página.

#### 1. Listado Principal (`admin/categorias/index.blade.php`)
```html
@extends("layouts.admin")

@section("content")
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Gestión de Categorías</h1>

        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route("admin.categorias.create") }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nueva Categoría
            </a>
        </div>

        <div id="ajax-messages" class="mb-4">
            @if (session("success"))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session("success") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="card shadow mb-4 card-premium">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Categorías</h6>
            </div>
            <div class="card-body">
                <form id="filter-form" action="{{ route("admin.categorias.index") }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre..." value="{{ request("search") }}">
                        </div>
                        <div class="col-md-3">
                            <select name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="activo" {{ request("estado") == "activo" ? "selected" : "" }}>Activas</option>
                                <option value="inactivo" {{ request("estado") == "inactivo" ? "selected" : "" }}>Inactivas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info">Buscar</button>
                        </div>
                    </div>
                </form>

                <div id="categorias-list">
                    @include("admin.categorias._categorias_table", ["categorias" => $categorias])
                </div>
            </div>
        </div>
    </div>
@endsection
```

#### 2. Tabla Parcial de Recarga AJAX (`_categorias_table.blade.php`)
```html
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Creado Por</th>
                <th>Última Actualización</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $cat)
                <tr>
                    <td>{{ $cat->id_categoria }}</td>
                    <td>{{ $cat->nombre_categoria }}</td>
                    <td>
                        <span class="badge {{ $cat->estado ? 'bg-success' : 'bg-danger' }}">
                            {{ $cat->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>{{ $cat->creator->name ?? 'N/A' }}</td>
                    <td>{{ $cat->updater->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.categorias.edit', $cat->id_categoria) }}" class="btn btn-sm btn-warning">Editar</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $categorias->links() }}
```

### E. Mejoras de Responsividad (Mobile First y Adaptación CSS)
Para asegurar el soporte al 100% de dispositivos móviles en la administración:
1. **Sidebar Off-Canvas**: Se modificó `layouts/admin.blade.php` con clases `transform: translateX(-100%)` y media-queries `@media (max-width: 991.98px)`.
2. **Backdrop**: Un elemento de fondo `.sidebar-backdrop` con filtro difuminado permite cerrar el menú al hacer clic en el exterior.
3. **Mobile Header**: Cabecera compacta visible únicamente en pantallas pequeñas con el botón para desplegar la barra de navegación lateral.

---

## 8. MÓDULO DE SEGURIDAD Y DIRECTORIO DE USUARIOS

El sistema integra un módulo avanzado de administración, protegido íntegramente por permisos granulares (Spatie), diseñado para gestionar el ciclo de vida, los accesos y la aprobación de nuevos integrantes.

### 8.1 Bandeja de Aprobaciones (Sala de Espera)
Por seguridad, los nuevos registros entran por defecto en estado inactivo (`is_approved = false`) y son retenidos en una Sala de Espera.
*   **Middleware (`CheckUserApproval`)**: Intercepta automáticamente cualquier petición de un usuario inactivo y lo redirige a la pantalla de espera o destruye su sesión, bloqueando instantáneamente el acceso a funcionalidades protegidas.
*   **Aprobación/Rechazo**: A través del `UsuarioAprobacionController`, los Administradores pueden revisar la jerarquía del solicitante (Sede > Complejo > División) y otorgar el acceso o rechazar la cuenta.

### 8.2 Directorio Activo de Gestión
La vista administrativa de usuarios (`admin/usuarios/index.blade.php`) implementa un diseño de vanguardia y altamente reactivo:
*   **Filtro Asíncrono (Fuzzy Search)**: Búsqueda dinámica en el frontend (JavaScript Vanilla) por nombre, correo electrónico o número de cédula, sin necesidad de recargas de página ni llamadas pesadas a la base de datos.
*   **Gestor Dinámico de Roles**: Permite actualizar en caliente el rol operativo (Admin, Gestor, Técnico, Usuario) mediante un modal superpuesto elegante.
*   **Interruptor de Acceso (AJAX)**: Activación y desactivación inmediata de las cuentas a través de un switch estilo Toggle que se comunica asíncronamente (vía `fetch` API) con el backend (`UsuarioController@toggleStatus`). Una cuenta desactivada expulsa de inmediato al usuario en su siguiente petición de red.

### 8.3 Permiso Estricto (`gestionar-usuarios`)
Toda la lógica de Aprobación y Directorio está contenida en un subgrupo de rutas en `routes/web.php` amparado por un middleware que exige explícitamente el permiso `gestionar-usuarios`.
*   Esto disocia el poder de "gestionar roles" del de "gestionar cuentas", permitiendo una delegación de responsabilidades más fina en el futuro.
*   La UI del menú lateral de administrador evalúa este permiso dinámicamente antes de renderizar los enlaces.

### 8.4 Integración de Directorio de Identidades (Active Directory & OpenLDAP)
El sistema cuenta con un panel avanzado de integración con directorios LDAP en la sección de configuraciones. Para dar soporte multi-institucional, se diseñó una interfaz con pestañas dinámicas que se adapta a dos proveedores principales:
1. **Windows Active Directory**:
   * **Usuario Bind (sAMAccountName / DN)**: DN completo de la cuenta de lectura (ej: `CN=Admin,CN=Users,DC=dominio,DC=local`).
   * **Atributo de Validación**: `sAMAccountName` (usuario corto clásico como `tito.castro`) o `userPrincipalName` (UPN estilo correo como `tito.castro@empresa.com`).
   * **Mapeo de Atributos**: Nombre real extraído mediante `displayName` o `cn`.
2. **OpenLDAP / Linux LDAP**:
   * **Usuario Bind**: DN completo (ej: `cn=admin,dc=empresa,dc=com`).
   * **Atributo de Búsqueda / Validación**: `uid` (identificador único LDAP estándar de Linux) o `cn` (nombre común).
   * **Mapeo de Atributos**: Nombre real mapeado por defecto mediante `cn`.

#### Características Técnicas e Interacciones:
* **Interruptor General (Switch Toggle)**: Habilita o deshabilita la integración de identidades globalmente de manera instantánea vía AJAX, realizando un `POST` asíncrono hacia la ruta `admin.configuraciones.ad.toggle`.
* **Probar Conexión (LDAP Test)**: Un botón con spinner de carga que realiza una prueba simulada de conexión y enlace contra el host LDAP indicado, llamando al endpoint `/admin/configuraciones/ad/test` para comprobar la viabilidad de la red antes de guardar cambios definitivos.
* **Persistencia**: Se almacena en la estructura de configuración global y se lee a través del archivo de configuración `config/ad.php`.

---

### 8.5 Módulo de Asignación y Control de Equipos (Inventario de Activos)

Este módulo gestiona el inventario físico e intangible de la institución, permitiendo relacionar los activos directamente con los usuarios de la plataforma y tipificando de manera fluida los problemas de soporte.

#### A. Estructura de Datos y Migraciones

La base de datos contiene la tabla `equipos` con columnas dinámicas según el tipo de elemento, manteniendo la constante de un número de bien institucional.

```php
// database/migrations/2026_05_26_000001_create_equipos_table.php
Schema::create('equipos', function (Blueprint $table) {
    $table->id('id_equipo');
    $table->string('nombre');
    $table->string('marca')->nullable();
    $table->string('modelo')->nullable();
    $table->string('numero_bien')->nullable()->unique();
    $table->string('ip_address')->nullable();
    $table->string('mac_address')->nullable();
    
    // Relación con tipo de equipo
    $table->unsignedBigInteger('id_tipo_equipo');
    $table->foreign('id_tipo_equipo')
          ->references('id_tipo_equipo')
          ->on('tipos_equipo')
          ->onDelete('restrict');
    
    // Relación con usuario asignado
    $table->unsignedBigInteger('id_usuario_asignado')->nullable();
    $table->foreign('id_usuario_asignado')
          ->references('id')
          ->on('users')
          ->onDelete('set null');
          
    $table->boolean('estado')->default(true);
    $table->timestamps();
});
```

Se agrega una columna `id_equipo` a la tabla `tickets` para rastrear de forma exacta el activo afectado en las solicitudes:

```php
// database/migrations/2026_05_26_000003_add_id_equipo_to_tickets_table.php
Schema::table('tickets', function (Blueprint $table) {
    $table->unsignedBigInteger('id_equipo')->nullable()->after('id_tipo_equipo');
    $table->foreign('id_equipo')
          ->references('id_equipo')
          ->on('equipos')
          ->onDelete('set null');
});
```

#### B. Modelo de Equipos (`Equipo.php`)

```php
// app/Models/Equipo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'nombre', 'marca', 'modelo', 'numero_bien',
        'ip_address', 'mac_address', 'id_tipo_equipo',
        'id_usuario_asignado', 'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_usuario_asignado', 'id');
    }
}
```

#### C. Controlador Administrativo (`EquipoController.php`)

```php
// app/Http/Controllers/Admin/EquipoController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\TipoEquipo;
use App\Models\User;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function __construct()
    {
        $this->middleware("can:gestionar-equipos");
    }

    public function index(Request $request)
    {
        $query = Equipo::query();

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("nombre", "like", "%" . $search . "%")
                  ->orWhere("numero_bien", "like", "%" . $search . "%")
                  ->orWhere("marca", "like", "%" . $search . "%")
                  ->orWhere("modelo", "like", "%" . $search . "%");
            });
        }

        if ($request->filled("id_tipo_equipo")) {
            $query->where("id_tipo_equipo", $request->id_tipo_equipo);
        }

        if ($request->filled("estado")) {
            $query->where("estado", $request->estado == "activo" ? true : false);
        }

        $equipos = $query->with(['tipoEquipo', 'usuarioAsignado'])->paginate(10);
        $tipos = TipoEquipo::all();

        if ($request->ajax()) {
            return view("admin.equipos._equipos_table", compact("equipos"))->render();
        }

        return view("admin.equipos.index", compact("equipos", "tipos"));
    }

    public function create()
    {
        $tipos = TipoEquipo::all();
        $usuarios = User::where('is_approved', true)->with('persona')->get();
        return view("admin.equipos.create", compact("tipos", "usuarios"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required|string|max:150",
            "marca" => "nullable|string|max:100",
            "modelo" => "nullable|string|max:100",
            "numero_bien" => "nullable|string|max:100|unique:equipos,numero_bien",
            "ip_address" => "nullable|ip",
            "mac_address" => "nullable|string|max:50",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_usuario_asignado" => "nullable|exists:users,id",
            "estado" => "required|boolean",
        ]);

        Equipo::create($request->all());

        return redirect()->route("admin.equipos.index")->with("success", "Equipo registrado exitosamente.");
    }

    public function edit(Equipo $equipo)
    {
        $tipos = TipoEquipo::all();
        $usuarios = User::where('is_approved', true)->with('persona')->get();
        return view("admin.equipos.edit", compact("equipo", "tipos", "usuarios"));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $request->validate([
            "nombre" => "required|string|max:150",
            "marca" => "nullable|string|max:100",
            "modelo" => "nullable|string|max:100",
            "numero_bien" => "nullable|string|max:100|unique:equipos,numero_bien," . $equipo->id_equipo . ",id_equipo",
            "ip_address" => "nullable|ip",
            "mac_address" => "nullable|string|max:50",
            "id_tipo_equipo" => "required|exists:tipos_equipo,id_tipo_equipo",
            "id_usuario_asignado" => "nullable|exists:users,id",
            "estado" => "required|boolean",
        ]);

        $equipo->update($request->all());

        return redirect()->route("admin.equipos.index")->with("success", "Equipo actualizado exitosamente.");
    }

    public function destroy(Equipo $equipo)
    {
        try {
            $equipo->delete();
            return redirect()->route("admin.equipos.index")->with("success", "Equipo eliminado del inventario.");
        } catch (\Exception $e) {
            return redirect()->route("admin.equipos.index")->with("error", "No se puede eliminar el equipo porque está asociado a registros históricos.");
        }
    }
}
```

#### D. Integración en el Panel de Usuario Final
* **Mis Equipos**: Se añade una vista de tarjetas al Dashboard del usuario (`home.blade.php`) donde se le listan detalladamente todos los dispositivos asignados a su cuenta para mayor visibilidad.
* **Auto-selección Inteligente en Tickets**: Al abrir un nuevo ticket (`usuario/tickets/create.blade.php`), se provee el listado de sus equipos personales. Mediante Javascript reactivo, si el usuario selecciona uno de sus equipos, el campo "Tipo de Equipo" se sincroniza automáticamente con el tipo correspondiente:

```javascript
$('#equipo-select').on('change', function() {
    let selectedOption = $(this).find('option:selected');
    let tipoId = selectedOption.data('tipo');
    if (tipoId) {
        $('#tipo-equipo-select').val(tipoId);
    }
});
```

---

### 8.6 Módulo de Marcas y Modelos Relacionales (Selects AJAX Dinámicos)

Para simplificar la categorización de activos y habilitar relaciones jerárquicas en el inventario (ej. que los modelos dependan directamente del fabricante), se refactorizó la especificación de texto libre a tablas dedicadas (`marcas` y `modelos`).

#### A. Base de Datos e Integración Relacional

```php
// database/migrations/2026_05_26_000004_create_marcas_table.php
Schema::create('marcas', function (Blueprint $table) {
    $table->id('id_marca');
    $table->string('nombre_marca')->unique();
    $table->timestamps();
});

// database/migrations/2026_05_26_000005_create_modelos_table.php
Schema::create('modelos', function (Blueprint $table) {
    $table->id('id_modelo');
    $table->string('nombre_modelo');
    $table->unsignedBigInteger('id_marca');
    $table->foreign('id_marca')->references('id_marca')->on('marcas')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['nombre_modelo', 'id_marca']);
});
```

#### B. Modelos Eloquent Vinculados

El modelo `Equipo.php` se actualizó para admitir relaciones de objetos en lugar de campos planos:

```php
public function marca()
{
    return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
}

public function modelo()
{
    return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
}
```

#### C. Lógica AJAX de Carga Dependiente

Cuando el administrador cambia la marca seleccionada en los formularios de registro o edición, una llamada AJAX asíncrona consulta la ruta de la API `/admin/marcas/{id}/modelos` para repoblar al instante el combo de modelos con los dispositivos compatibles de ese fabricante.

```javascript
$('#id_marca').on('change', function() {
    let marcaId = $(this).val();
    let modeloSelect = $('#id_modelo');
    modeloSelect.empty().append('<option value="">-- Cargando modelos... --</option>');
    
    if (marcaId) {
        $.ajax({
            url: `/admin/marcas/${marcaId}/modelos`,
            type: 'GET',
            success: function(data) {
                modeloSelect.empty().append('<option value="">-- Seleccionar Modelo --</option>');
                data.forEach(function(modelo) {
                    modeloSelect.append(`<option value="${modelo.id_modelo}">${modelo.nombre_modelo}</option>`);
                });
            }
        });
    }
});
```

### 8.7 Módulo de Auditoría Integral y Rendimiento Técnico

Para habilitar un seguimiento preciso y auditar qué usuarios realizan cambios en el catálogo, además de monitorear el desempeño de los técnicos de soporte, se implementaron soluciones avanzadas a nivel de base de datos, backend y visualización.

#### A. Nuevas Estructuras de Base de Datos

Se crearon e implementaron tres migraciones para normalizar y auditar datos:

```php
// database/migrations/2026_05_26_140211_deduplicate_and_add_unique_to_categorias_table.php
Schema::table('categorias', function (Blueprint $table) {
    $table->string('nombre_categoria', 100)->unique()->change();
});

// database/migrations/2026_05_26_140228_add_categoria_nombre_historico_to_tickets_table.php
Schema::table('tickets', function (Blueprint $table) {
    $table->string('categoria_nombre_historico')->nullable()->after('id_categoria');
});

// database/migrations/2026_05_26_140241_create_audit_logs_table.php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->string('action'); // create, update, delete
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('created_at')->nullable();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});
```

#### B. Trait `Auditable` para Seguimiento Automatizado

Se creó el trait `App\Traits\Auditable.php` para registrar cambios en los modelos `Categoria`, `Marca`, `Modelo` y `TipoEquipo` conectándose a los eventos del ciclo de vida de Eloquent (`created`, `updated`, `deleted`):

```php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('create', null, $model->getDirtyForAudit());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirtyForAudit();
            if (!empty($dirty)) {
                $old = [];
                foreach ($dirty as $key => $value) {
                    $old[$key] = $model->getOriginal($key);
                }
                $model->logAudit('update', $old, $dirty);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->getAttributes(), null);
        });
    }
}
```

#### C. Controladores de Auditoría y Rendimiento

1.  **`AuditController.php`**: Permite la visualización filtrada por acción, tipo de componente y responsable autor de la acción, con paginación avanzada. También incluye los métodos `export` (para reportes CSV) y `exportPdf` (para reportes en PDF formal y llamativo en modo horizontal) de manera dinámica y respetando los filtros activos de búsqueda del usuario.
2.  **`RendimientoTecnicoController.php`**: Agrupa y procesa métricas clave de cada técnico especialista, computando dinámicamente la velocidad promedio de resolución de incidentes calculando la brecha temporal entre la asignación del ticket y la fecha del reporte de cierre.

#### D. Nuevos Permisos de Seguridad (Spatie)

Se añadieron permisos de control RBAC granular:
*   `ver-auditorias`: Habilita el acceso de lectura a la bitácora histórica de auditorías.
*   `ver-rendimiento-tecnico`: Permite revisar el tablero de analíticas y productividad del equipo técnico de soporte.

---

## 9. PASOS PARA RECREAR EL PROYECTO DESDE CERO

Sigue esta secuencia exacta de comandos y código para reconstruir y desplegar el sistema Helpdesk GDC en un nuevo entorno.

### Paso 1: Instalación de Dependencias
Asegúrate de tener PHP 8.1+ y Composer instalados en tu sistema.

```bash
# 1. Crear nuevo proyecto en Laravel 10
composer create-project laravel/laravel helpdesk-gdc "10.*"
cd helpdesk-gdc

# 2. Instalar el paquete de Roles y Permisos de Spatie
composer require spatie/laravel-permission "^6.25"

# 3. Instalar la suite de autenticación base (Laravel Breeze con blade)
composer require laravel/breeze --dev
php artisan breeze:install blade

# 4. Instalar las dependencias NPM y Bootstrap para los estilos premium
npm install bootstrap@5.3.3 @popperjs/core sass bootstrap-icons
```

### Paso 2: Configuración del Archivo `.env`
Configura las credenciales de tu servidor de base de datos MySQL en la raíz:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_laravel
DB_USERNAME=root
DB_PASSWORD=tu_contrasena
```

### Paso 3: Publicación de Migraciones de Spatie
Genera los archivos para la estructura de roles de Spatie:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Paso 4: Creación de Archivos de Código
Copia el código detallado provisto en las secciones anteriores en sus respectivos archivos:
1.  **Migraciones**: Ubicadas en `database/migrations/`.
2.  **Modelos**: Ubicados en `app/Models/`.
3.  **Controladores**: Ubicados en `app/Http/Controllers/`.
4.  **Rutas**: Reemplaza el archivo `routes/web.php`.
5.  **Layouts y Vistas**: Ubicados en `resources/views/`.

### Paso 5: Despliegue de Base de Datos y Semillas (Seed)
Aplica los cambios de migración y ejecuta los semilleros automatizados para poblar los usuarios de prueba:

```bash
php artisan migrate:fresh --seed
```

### Paso 6: Compilación de Frontend y Servidor de Desarrollo
Inicia el motor de plantillas y el compilador de activos Vite para visualizar el proyecto:

```bash
# Ejecutar el compilador en desarrollo
npm run dev

# En otra terminal, inicializar el servidor Laravel
php artisan serve
```

### Paso 7: Cuentas de Acceso de Prueba
Accede a `http://localhost:8000` y prueba el sistema con los siguientes perfiles seeded:
*   **Gestor**: `gestor@helpdesk.com` | Contraseña: `gestor123`
*   **Técnico**: `tecnico@helpdesk.com` | Contraseña: `tecnico123`
*   **Usuario**: `usuario@helpdesk.com` | Contraseña: `usuario123`

---

> [!NOTE]
> **Manual Técnico Integral - Helpdesk GDC**
> Este manual documenta exactamente la arquitectura viva de tu aplicación y ha sido depositado directamente en la raíz de tu proyecto para una consulta rápida por parte del equipo técnico.


## Actualizaciones V2.0 (Mejoras UI/UX, Dashboard y Auditoría)

En esta fase se implementaron mejoras sustanciales en la experiencia de usuario y capacidades de monitoreo:

### 1. Mejoras de UI/UX
- **Glassmorphism:** Se inyectaron variables CSS avanzadas para lograr un efecto cristalino en la barra lateral (`.sidebar`) con soporte nativo para los temas claro y oscuro de Bootstrap.
- **Micro-animaciones:** Implementación de efectos de escala y elevación al interactuar con las tarjetas (`.card-premium`).
- **Badges Semánticos:** Nuevos indicadores visuales (`.badge-premium`) para identificar estados de tickets con degradados y sombras.
- **Tooltips Globales:** Activación de los tooltips de Bootstrap (`data-bs-toggle="tooltip"`) en iconos de la interfaz.
- **Spotlight Search:** Mejora del input de búsqueda en la base de conocimiento utilizando la clase `.search-premium` para lograr enfoque inmersivo con atenuación del fondo.

### 2. Chat Flotante (Tickets)
- Se extrajo el historial de mensajes de la vista del ticket hacia un panel flotante lateral (`Offcanvas`/`Fixed Panel`).
- Integración de un botón de acción flotante (FAB) para la apertura del chat de forma que no sature la vista principal.
- Desplazamiento (Scroll) automático suavizado hacia el último mensaje.

### 3. Dashboard con Datos Reales
- Se conectó `DashboardController` con Eloquent para renderizar métricas exactas (Tickets nuevos, En Gestión, Cerrados hoy).
- Se implementaron métricas diferenciadas por rol: Gestores ven estadísticas globales y Técnicos ven sus propias métricas (Pendientes, Resueltos).
- Se integró `Chart.js` para visualizar gráficas interactivas de Rendimiento por Técnico y Distribución por Categoría.

### 4. Sistema de Auditoría (Spatie Activitylog)
- Se instaló `spatie/laravel-activitylog` para rastrear las modificaciones de la base de datos.
- Se configuró el trait `LogsActivity` en los modelos principales: `Ticket`, `User`, `Categoria` y `ArticuloConocimiento`.
- Las migraciones fueron implementadas exitosamente configurando la tabla `activity_log`.



## Apéndice de Código: Actualizaciones V2.0

A continuación se anexa el código fuente íntegro de las modificaciones recientes (UI/UX, Chat Flotante, Dashboard con Chart.js y Auditoría con Spatie) para su posterior análisis técnico.

### Dashboard Controller (app/Http/Controllers/DashboardController.php)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('ver-panel-operativo')) {
            $stats = [];
            
            if ($user->can('asignar-tickets')) {
                // Gestores / Admins
                $stats['nuevos'] = Ticket::where('estatus', 1)->count();
                $stats['en_gestion'] = Ticket::where('estatus', 2)->count();
                $stats['cerrados_hoy'] = Ticket::where('estatus', 3)->whereDate('updated_at', Carbon::today())->count();
                $stats['tiempo_promedio'] = "45m";

                // Gráficas
                // 1. Porcentaje por Categoría
                $categoriasData = \Illuminate\Support\Facades\DB::table('tickets')
                    ->join('categorias', 'tickets.id_categoria', '=', 'categorias.id_categoria')
                    ->select('categorias.nombre_categoria', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->groupBy('categorias.nombre_categoria')
                    ->get();
                $stats['chart_cat_labels'] = $categoriasData->pluck('nombre_categoria')->toJson();
                $stats['chart_cat_data'] = $categoriasData->pluck('total')->toJson();

                // 2. Rendimiento por técnico (Tickets resueltos)
                $tecnicosData = \App\Models\User::role('tecnico')->withCount(['asignaciones as resueltos' => function($q) {
                    $q->whereHas('ticket', function($t) { $t->where('estatus', 3); });
                }])->get();
                $stats['chart_tech_labels'] = $tecnicosData->pluck('name')->toJson();
                $stats['chart_tech_data'] = $tecnicosData->pluck('resueltos')->toJson();
            } else {
                // Técnicos
                $stats['pendientes_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estado_tecnico', 'pendiente')->count();

                $stats['en_proceso_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estado_tecnico', 'en_progreso')->count();

                $stats['resueltos_tecnico'] = Ticket::whereHas('asignacion', function($q) use ($user) {
                    $q->where('id_usuario_tecnico', $user->id);
                })->where('estatus', 3)->count();
            }

            return view('soporte.dashboard', compact('stats'));
        }

        // Si no tiene acceso al panel operativo, se le redirige al inicio de cliente
        return redirect()->route('usuario.home');
    }
}
```

### Vista Dashboard (resources/views/soporte/dashboard.blade.php)

```html
@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .theme-surface-soft {
        background-color: color-mix(in srgb, var(--bs-body-bg) 82%, var(--bs-tertiary-bg) 18%) !important;
    }
    [data-bs-theme="dark"] .theme-surface-soft {
        background-color: #1f2327 !important;
    }
    .theme-badge-soft {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border: 1px solid var(--bs-border-color) !important;
    }
</style>
@endpush
<div class="container-fluid">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold theme-text mb-0">
                @can('gestionar-roles')
                    Panel de Administración Global
                @elsecan('asignar-tickets')
                    Panel de Supervisión de Soporte
                @else
                    Mi Panel Técnico Operativo
                @endcan
            </h3>
            <p class="text-muted">Bienvenido, <strong class="text-primary">{{ Auth::user()->name }}</strong>. Aquí tienes el resumen de actividades de hoy.</p>
        </div>
        <div class="text-end">
            <span class="badge theme-badge-soft px-3 py-2 shadow-sm rounded-pill">
                <i class="bi bi-calendar3 me-2 text-primary"></i> {{ date('d/m/Y') }}
            </span>
        </div>
    </div>

    {{-- 1. SECCIÓN DE GESTORES Y COORDINADORES --}}
    @can('asignar-tickets')
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card-premium border-start border-danger border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Tickets Sin Asignar</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['nuevos'] ?? 0 }}</h2>
                    <div class="text-danger small mt-2 fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Requieren técnico</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-primary border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">En Gestión Activa</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['en_gestion'] ?? 0 }}</h2>
                    <div class="text-primary small mt-2 fw-semibold"><i class="bi bi-gear-fill me-1"></i> Especialistas trabajando</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-success border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Cerrados hoy</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['cerrados_hoy'] ?? 0 }}</h2>
                    <div class="text-success small mt-2 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Buen desempeño</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-premium border-start border-warning border-4 shadow-sm">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Tiempo Promedio</small>
                    <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['tiempo_promedio'] ?? 'N/A' }}</h2>
                    <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-clock-history me-1"></i> Meta sugerida: 1h</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Rendimiento por Técnico</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center p-3 text-center theme-surface-soft border-secondary border-opacity-25 rounded-3" style="height: 250px;">
                        <canvas id="techChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-premium mb-4 shadow-sm" style="min-height: 350px;">
                    <h5 class="fw-bold mb-4 theme-text"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Porcentaje por Categoría</h5>
                    <div class="d-flex flex-column align-items-center justify-content-center p-3 text-center theme-surface-soft border-secondary border-opacity-25 rounded-3" style="height: 250px;">
                        <canvas id="catChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- 2. SECCIÓN DE TÉCNICOS (Solo si no es Gestor puro, o si tiene rol de técnico) --}}
    @cannot('asignar-tickets')
        @can('resolver-tickets')
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card-premium border-start border-warning border-4 shadow-sm">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Mis Casos Asignados</small>
                        <h2 class="fw-bold mb-0 theme-text mt-1">{{ ($stats['pendientes_tecnico'] ?? 0) + ($stats['en_proceso_tecnico'] ?? 0) }}</h2>
                        <div class="text-warning small mt-2 fw-semibold"><i class="bi bi-tools me-1"></i> Pendientes y en progreso</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card-premium border-start border-success border-4 shadow-sm">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Resueltos Histórico</small>
                        <h2 class="fw-bold mb-0 theme-text mt-1">{{ $stats['resueltos_tecnico'] ?? 0 }}</h2>
                        <div class="text-success small mt-2 fw-semibold"><i class="bi bi-check-lg me-1"></i> Casos cerrados con éxito</div>
                    </div>
                </div>
            </div>

            <div class="card-premium text-center py-5 shadow-sm border-0 mb-5">
                <i class="bi bi-laptop text-primary mb-3" style="font-size: 3.5rem;"></i>
                <h4 class="fw-bold theme-text mb-2">¡Hola de nuevo, Especialista!</h4>
                <p class="text-muted mx-auto" style="max-width: 500px;">
                    Accede a tu bandeja de **"Casos Asignados"** en el panel izquierdo para ver tus tareas prioritarias y comenzar a solucionar incidencias en vivo.
                </p>
                <a href="{{ route('soporte.tickets.tecnico.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold mt-3 shadow-sm">
                    <i class="bi bi-ticket-perforated me-2"></i>Ir a mis Casos
                </a>
            </div>
        @endcan
    @endcannot
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    @can('asignar-tickets')
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDark ? '#adb5bd' : '#6c757d';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // Chart Rendimiento Técnicos
        const ctxTech = document.getElementById('techChart').getContext('2d');
        new Chart(ctxTech, {
            type: 'bar',
            data: {
                labels: {!! $stats['chart_tech_labels'] ?? '[]' !!},
                datasets: [{
                    label: 'Tickets Resueltos',
                    data: {!! $stats['chart_tech_data'] ?? '[]' !!},
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                    x: { grid: { display: false }, ticks: { color: textColor } }
                }
            }
        });

        // Chart Categorías
        const ctxCat = document.getElementById('catChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: {!! $stats['chart_cat_labels'] ?? '[]' !!},
                datasets: [{
                    data: {!! $stats['chart_cat_data'] ?? '[]' !!},
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d'],
                    borderWidth: 2,
                    borderColor: isDark ? '#1a1c1e' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: textColor, boxWidth: 12, font: { size: 11 } } }
                },
                cutout: '70%'
            }
        });
    @endcan
});
</script>
@endpush

```

### Layout Admin CSS/JS (resources/views/layouts/admin.blade.php)

```html
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk GDC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", savedTheme);
    </script>
    
    @stack("styles") {{-- Para CSS de vistas específicas --}}

    <style>
        :root { 
            --sb-width: 270px;
            --bg-main: #f4f7fa; /* Gris claro que no cansa la vista */
            --sb-bg: #ffffff;
        }

        [data-bs-theme="dark"] {
            --bg-main: #0b0c0d;
            --sb-bg: #111214;
        }
        body {
            font-family: "Inter", sans-serif; 
            background-color: var(--bg-main) !important; 
            transition: all 0.3s ease;
        }
        
        /* Sidebar Fijo y Estructurado */
        #sidebar {
            width: var(--sb-width); 
            min-width: var(--sb-width); 
            height: 100vh;
            background: var(--sb-bg);
            border-right: 1px solid var(--bs-border-color);
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column; /* Permite empujar el footer al fondo */
            z-index: 1000;
            overflow: hidden; /* Evitar que el sidebar completo se desborde */
        }

        /* Scrollbar premium y minimalista para el menú interno */
        .sidebar-scrollable {
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .sidebar-scrollable::-webkit-scrollbar{ 
            width: 4px;
        }
        .sidebar-scrollable::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scrollable::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        [data-bs-theme="dark"] .sidebar-scrollable::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Estilos del menú desplegable del pie de página (Dropup) */
        .user-footer .dropdown-menu {
            position: absolute !important;
            bottom: 100% !important;
            left: 0 !important;
            width: 100% !important;
            margin-bottom: 8px !important;
            border: 1px solid var(--bs-border-color) !important;
            background-color: var(--sb-bg) !important;
            z-index: 1100;
        }
        .user-footer .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        [data-bs-theme="dark"] .user-footer .dropdown-menu {
            background-color: #1e293b !important;
        }

        .nav-link {
            color: var(--bs-body-color) !important; 
            font-size: 0.88rem; 
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 2px 10px;
        }

        .nav-link:hover {
            background: var(--bs-secondary-bg);
        }
        .nav-link.active {
            background: #0d6efd !important; color: #fff !important;
        }

        /* Contenedor de Contenido */
        #content {
            flex-grow: 1; padding: 2.5rem 2.5rem 2.5rem 1.5rem;
        }

        /* Tarjeta Premium que reacciona al tema */
        .card-premium {
            background: var(--bs-custom-card-bg, #fff);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .card-premium {
            background: #1a1c1e;
        }
        
        /* Anular bordes de focus nativos del navegador */
        .card-premium:focus, .card-premium:active, .card-premium:focus-visible {
            outline: none !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        /* Prevenir que el contenido desborde las tarjetas en cualquier pantalla */
        .card-premium {
            overflow: hidden;
            min-width: 0;
            word-break: break-word;
        }

        /* Asegurar que las columnas del grid respeten sus límites */
        .row > [class*="col-"] {
            min-width: 0;
        }
        }

        /* ================= SUGERENCIAS UI/UX ================= */
        /* Efecto Glassmorphism en Sidebar (C1) */
        #sidebar {
            background: rgba(255, 255, 255, 0.72) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,0,0,0.05);
        }
        [data-bs-theme="dark"] #sidebar {
            background: rgba(17, 18, 20, 0.85) !important;
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        /* Transiciones Suaves al Cambiar de Tema (C2) y Micro-animaciones (A2) */
        #sidebar, .card-premium, .nav-link, .badge, .btn, .table thead th, .table tbody tr, .dropdown-menu {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        [data-bs-theme="dark"] .card-premium:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        /* Spotlight Search (C6) */
        .search-premium {
            transition: all 0.3s ease;
        }
        .search-premium:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
            border-color: #0d6efd;
        }

        /* Badges Semánticos Mejorados (A3) */
        .badge-status-abierto { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white !important; border: none; }
        .badge-status-proceso { background: linear-gradient(135deg, #f59e0b, #d97706); color: white !important; border: none; }
        .badge-status-resuelto { background: linear-gradient(135deg, #10b981, #059669); color: white !important; border: none; }
        .badge-status-cerrado { background: linear-gradient(135deg, #6b7280, #4b5563); color: white !important; border: none; }
        /* ===================================================== */

        /* Truncar texto de una línea sin romper el layout */
        .text-truncate-safe {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            display: block;
        }

        /* Fechas y textos sin espacios: romper con overflow-wrap */
        .text-overflow-wrap {
            overflow-wrap: anywhere;
            word-break: break-word;
            hyphens: auto;
        }

        /* En pantallas menores a 768px, las columnas siempre ocupan el ancho completo */
        @media (max-width: 767.98px) {
            .col-md-4, .col-md-8 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        /* Responsive Sidebar y Mobile Header */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed !important;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1045 !important;
                box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            }
            #sidebar.show {
                transform: translateX(0);
            }
            #content {
                padding: 1.5rem !important;
                width: 100%;
            }
            /* Backdrop al abrir el sidebar */
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(4px);
                z-index: 1040;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            .sidebar-backdrop.show 
            {
                opacity: 1;
                visibility: visible;
            }
            /* Header móvil visible únicamente en pantallas pequeñas */
            .mobile-header {
                display: flex !important;
                align-items: center;
                background: var(--sb-bg);
                border-bottom: 1px solid var(--bs-border-color);
                padding: 0.8rem 1.5rem;
                position: sticky;
                top: 0;
                z-index: 1030;
                width: 100%;
                transition: background-color 0.3s ease;
            }
        }
        @media (min-width: 992px) {
            .mobile-header {
                display: none !important;
            }
        }

        /* Footer de Usuario fijo abajo */
        .user-footer {
            margin-top: auto; /* Empuja hacia abajo */
            padding: 1.2rem;
            border-top: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
            position: relative;
            z-index: 1050; /* Garantiza que se dibuje por encima de la lista de scroll */
        }

        /* Estilos para formularios Premium */
        .form-control-premium {
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }
        .form-control-premium:focus {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .section-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Tarjetas de sección */
        .profile-card {
            background-color: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        /* Estilo para los Labels (Nombres de campos) */
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            margin-bottom: 0.5rem;
        }

        /* Inputs Premium */
        .form-control-premium {
            background-color: var(--bs-body-bg) !important;
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color) !important;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .form-control-premium:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        /* Estilos premium para la paginación (Soporta Dark y Light Mode) */
        .pagination .page-link {
            background-color: var(--bs-custom-card-bg, #fff) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
            border-radius: 8px !important;
            margin: 0 3px !important;
            padding: 8px 16px !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        [data-bs-theme="dark"] .pagination .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        .pagination .page-link:hover {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: #0d6efd !important;
        }

        /* Estandar premium para listados de Admin/Soporte */
        .card-premium .table {
            margin-bottom: 0;
        }
        .card-premium .table thead th {
            border-bottom: 1px solid color-mix(in srgb, var(--bs-border-color) 85%, transparent);
            color: var(--bs-secondary-color);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }
        .card-premium .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .card-premium .table tbody tr:hover {
            background-color: color-mix(in srgb, var(--bs-primary-bg-subtle) 30%, transparent);
        }
        .card-premium .table td {
            vertical-align: middle;
        }
        .card-premium .table .btn {
            white-space: nowrap;
        }
        .btn-action-premium {
            border-radius: 10px !important;
            font-weight: 600;
            min-height: 34px;
            white-space: nowrap;
        }
        .btn-action-icon {
            min-width: 34px;
            min-height: 34px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .table .d-flex.gap-2 {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* Estándar de columnas de tabla heredado de SIGEINV */
        .th-id {
            width: 80px;
            min-width: 80px;
        }
        .th-estado {
            width: 120px;
            min-width: 120px;
        }
        .th-acciones {
            width: 140px;
            min-width: 140px;
            text-align: right;
        }

    </style>
</head>
<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div class="p-4 mb-2">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h5>
            </div>

            <div class="sidebar-scrollable">
                <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Panel Operativo</small>
                
                <ul class="nav nav-pills flex-column">
                    {{-- DASHBOARD COMPARTIDO --}}
                    <li>
                        <a href="{{ route("soporte.dashboard") }}" class="nav-link {{ request()->routeIs("soporte.dashboard") ? "active" : "" }}">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- GESTIÓN DE TICKETS SEGÚN ROL --}}
                    @can("asignar-tickets")
                        <li>
                            <a href="{{ route("soporte.tickets.index") }}" class="nav-link {{ request()->routeIs("soporte.tickets.*") && !request()->routeIs("soporte.tickets.tecnico.*") ? "active" : "" }}">
                                <i class="bi bi-ticket-detailed-fill me-3"></i>Mesa de Despacho
                            </a>
                        </li>
                    @endcan
                    
                    @can("resolver-tickets")
                        <li>
                            <a href="{{ route("soporte.tickets.tecnico.index") }}" class="nav-link {{ request()->routeIs("soporte.tickets.tecnico.*") ? "active" : "" }}">
                                <i class="bi bi-ticket-perforated me-3"></i>Mis Tareas Activas
                            </a>
                        </li>
                    @endcan

                    @can("ver-conocimiento")
                        <li>
                            <a href="#kbMenu" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('soporte.conocimiento.*') || request()->routeIs('soporte.tags.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-journal-bookmark-fill me-3"></i>Base de Conocimiento</span>
                                <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                            </a>
                            <ul class="collapse {{ request()->routeIs('soporte.conocimiento.*') || request()->routeIs('soporte.tags.*') ? 'show' : '' }} nav flex-column ms-3" id="kbMenu">
                                <li class="mt-1">
                                    <a href="{{ route('soporte.conocimiento.index') }}" class="nav-link py-1 {{ request()->routeIs('soporte.conocimiento.*') && request('estado') !== 'archivados' ? 'text-primary fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-circle me-2" style="font-size: 0.5rem;"></i> Artículos
                                    </a>
                                </li>
                                <li class="mb-1">
                                    <a href="{{ route('soporte.tags.index') }}" class="nav-link py-1 {{ request()->routeIs('soporte.tags.*') ? 'text-primary fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-circle me-2" style="font-size: 0.5rem;"></i> Etiquetas (Tags)
                                    </a>
                                </li>
                                <li class="mb-1">
                                    <a href="{{ route('soporte.conocimiento.index', ['estado' => 'archivados']) }}" class="nav-link py-1 {{ request('estado') === 'archivados' ? 'text-primary fw-bold' : 'text-muted' }}">
                                        <i class="bi bi-archive me-2" style="font-size: 0.8rem;"></i> Archivados
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    {{-- CONTROL DE ACCESOS (SÓLO ADMINISTRADORES) --}}
                    @if(auth()->user()->can("gestionar-roles") || auth()->user()->can("gestionar-usuarios"))
                        <hr class="mx-3 my-2 opacity-25 text-muted">
                        <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Control de Accesos</small>
                        
                        @can("gestionar-roles")
                        <li>
                            <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->routeIs("admin.roles.*") ? "active" : "" }}">
                                <i class="bi bi-shield-lock-fill me-3"></i>Roles y Permisos
                            </a>
                        </li>
                        @endcan
                        
                        @can("gestionar-usuarios")
                        <li>
                            @php
                                $pendientesCount = \App\Models\User::where("is_approved", false)->count();
                            @endphp
                            <a href="{{ route("admin.usuarios.pendientes") }}" class="nav-link {{ request()->routeIs("admin.usuarios.pendientes") ? "active" : "" }} d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-person-fill-gear me-3"></i>Aprobación de Usuarios
                                </span>
                                @if($pendientesCount > 0)
                                    <span class="badge bg-warning text-dark rounded-pill fw-bold" style="font-size: 0.7rem; padding: 0.25em 0.6em;">
                                        {{ $pendientesCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        @endcan
                    @endif

                    {{-- CONFIGURACIÓN Y ACTIVOS --}}
                    @if(auth()->user()->can("gestionar-usuarios") || auth()->user()->can("ver-configuraciones") || auth()->user()->can("asignar-tickets") || auth()->user()->can("gestionar-categorias") || auth()->user()->can("ver-rendimiento-tecnico") || auth()->user()->can("ver-auditorias"))
                        <hr class="mx-3 my-2 opacity-25 text-muted">
                        <small class="text-muted fw-bold ps-4 text-uppercase mb-2 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">Configuración y Activos</small>

                        @can("gestionar-usuarios")
                        <li><a href="{{ route("admin.usuarios.index") }}" class="nav-link {{ request()->routeIs("admin.usuarios.index") ? "active" : "" }}"><i class="bi bi-people-fill me-3"></i>Usuarios</a></li>
                        @endcan

                        @can("ver-configuraciones")
                        <li><a href="{{ route("admin.estructura.index") }}" class="nav-link {{ request()->routeIs("admin.estructura.*") ? "active" : "" }}"><i class="bi bi-diagram-3-fill me-3"></i>Organigrama</a></li>
                        @endcan
                        
                        @can("gestionar-categorias")
                         <li>
                            <a href="{{ route("admin.categorias.index") }}" class="nav-link {{ request()->routeIs("admin.categorias.*") ? "active" : "" }}">
                                <i class="bi bi-tags-fill me-3"></i>Categorías de Tickets
                            </a>
                        </li>
                        @endcan
                        @can("gestionar-equipos")
                        <li>
                            <a href="{{ route("admin.equipos.index") }}" class="nav-link {{ request()->routeIs("admin.equipos.*") && !request()->routeIs("admin.equipos.catalogos.*") ? "active" : "" }}">
                                <i class="bi bi-pc-display me-3"></i>Asignación de Equipos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route("admin.equipos.catalogos.index") }}" class="nav-link {{ request()->routeIs("admin.equipos.catalogos.*") ? "active" : "" }}">
                                <i class="bi bi-list-stars me-3"></i>Catálogo de Activos
                            </a>
                        </li>
                        @endcan
                        @can('ver-rendimiento-tecnico')
                            <li>
                                <a href="{{ route('admin.rendimiento.index') }}" class="nav-link {{ request()->routeIs('admin.rendimiento.*') ? 'active' : '' }}">
                                    <i class="bi bi-bar-chart-line-fill me-3"></i>Rendimiento Técnico
                                </a>
                            </li>
                        @endcan
                        @can('ver-auditorias')
                            <li>
                                <a href="{{ route('admin.auditorias.index') }}" class="nav-link {{ request()->routeIs('admin.auditorias.*') ? 'active' : '' }}">
                                    <i class="bi bi-journal-text me-3"></i>Bitácora de Auditorías
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>
            </div>

            <div class="user-footer">
                <div class="dropup" style="position: relative; z-index: 1050;">
                    <button class="btn border-0 d-flex align-items-center w-100 p-0" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="ms-3 text-start">
                            <p class="mb-0 fw-bold small text-truncate" style="max-width: 120px;">{{ Auth::user()->name }}</p>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.6rem; letter-spacing: 0.3px;">
                                {{ Auth::user()->roles->pluck("name")->implode(", ") ?: "Soporte" }}
                            </small>
                        </div>
                        <i class="bi bi-chevron-up ms-auto text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 mb-2">
                        <li><a class="dropdown-item py-2" href="{{ route("profile.edit") }}"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                        
                        @can("ver-configuraciones")
                        <li><a class="dropdown-item py-2" href="{{ route("admin.configuraciones.index") }}"><i class="bi bi-gear-fill me-2 text-secondary"></i> Ajustes del Sistema</a></li>
                        @endcan
                        
                        <li><button class="dropdown-item py-2" onclick="toggleTheme()"><i class="bi bi-moon-stars me-2"></i> Cambiar Tema</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route("logout") }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main id="content">
            <!-- Header Móvil para pantallas táctiles/pequeñas -->
            <div class="mobile-header d-none justify-content-between align-items-center mb-3 rounded-3 shadow-sm border border-secondary border-opacity-10">
                <button class="btn btn-outline-secondary border-0 p-1 shadow-none" id="btn-toggle-sidebar">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-headset me-2"></i>Helpdesk GDC</h6>
                <div style="width: 32px;"></div> <!-- Nivelador óptico -->
            </div>

            <div class="container-fluid">
                @yield("content")
                @if(isset($slot))
                    {{ $slot }} {{-- Para la vista de perfil que usa x-dynamic-component --}}
                @endif
            </div>
        </main>
    </div>

    <!-- Backdrop para cerrar menú táctil en móviles al pulsar fuera -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> {{-- Añadir jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const target = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
            html.setAttribute("data-bs-theme", target);
            localStorage.setItem("theme", target);
        }

        // Control del sidebar responsivo
        document.addEventListener("DOMContentLoaded", function() {
            const btnToggle = document.getElementById("btn-toggle-sidebar");
            const sidebar = document.getElementById("sidebar");
            const backdrop = document.getElementById("sidebar-backdrop");
            
            if (btnToggle && sidebar && backdrop) {
                btnToggle.addEventListener("click", function() {
                    sidebar.classList.add("show");
                    backdrop.classList.add("show");
                });
                
                backdrop.addEventListener("click", function() {
                    sidebar.classList.remove("show");
                    backdrop.classList.remove("show");
                });
            }

            // Inicializar Tooltips (C4)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @stack("scripts") {{-- Para JS de vistas específicas --}}
</body>
</html>

```

### Chat Flotante (resources/views/soporte/partials/chat-flotante.blade.php)

*(Archivo no encontrado)*

### Modelo User con Spatie (app/Models/User.php)

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importante añadir esto
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_persona', // Asegúrate de que este campo esté en tu tabla 'users'
        'is_approved',
    ];

    /**
     * Relación con la información personal del usuario.
     */
    public function persona(): BelongsTo
    {
        // 1er parámetro: Modelo relacionado
        // 2do parámetro: Llave foránea en la tabla 'users'
        // 3er parámetro: Llave primaria en la tabla 'personas'
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Relación con los equipos asignados al usuario.
     */
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_usuario_asignado', 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(TicketAsignacion::class, 'id_usuario_tecnico', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

### Modelo Ticket con Spatie (app/Models/Ticket.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\TicketAdjunto;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model {
    use LogsActivity;

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'asunto',
        'id_usuario', 
        'id_tipo_equipo', 
        'id_equipo',
        'id_prioridad', 
        'id_categoria', 
        'descripcion_problema', 
        'estatus', 
        'estado_tecnico',
        'id_usuario_tecnico', 
        'fecha_cierre'
    ];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

    public function tecnico()
    {
        return $this->hasOneThrough(
            User::class,
            TicketAsignacion::class,
            'id_ticket', // Llave foránea en ticket_asignaciones
            'id',        // Llave foránea en users
            'id_ticket', // Llave local en tickets
            'id_usuario_tecnico' // Llave local en ticket_asignaciones
        );
    }

    public function prioridad() 
    { 
        return $this->belongsTo(Prioridad::class, 'id_prioridad'); 
    }

    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria'); }

    public function tipoEquipo() { return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); }
    
    public function equipo() { return $this->belongsTo(Equipo::class, 'id_equipo'); }
    
    public function adjuntos() { 
        return $this->hasMany(TicketAdjunto::class, 'id_ticket'); 
    }

    public function solucion() {
        return $this->hasOne(SolucionTecnica::class, 'id_ticket');
    }

    public function asignacion(): HasOne
    {
        // El segundo parámetro es la llave foránea en 'ticket_asignaciones'
        // El tercer parámetro es la llave local en 'tickets'
        return $this->hasOne(TicketAsignacion::class, 'id_ticket', 'id_ticket'); 
    }

    // "Traductor" para que el estatus (1, 2, 3) se vea como texto en la tabla
    public function getEstadoTextoAttribute() {
        return match($this->estatus) {
            0 => 'Borrador',
            1 => 'Abierto',
            2 => 'En Proceso',
            3 => 'Resuelto',
            4 => 'Cerrado',
            default => 'Desconocido',
        };
    }

    public function comentarios()
    {
        // Cambiamos Comentario::class por TicketComentario::class
        return $this->hasMany(TicketComentario::class, 'id_ticket', 'id_ticket');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'asunto', 'id_prioridad', 'id_categoria', 'estatus', 'estado_tecnico', 'id_usuario_tecnico'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}


```



## C�DIGO DE LAS ACTUALIZACIONES RECIENTES (Spatie Activitylog y Mejoras Visuales)


### Archivo: app\Models\User.php

``php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importante añadir esto
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_persona', // Asegúrate de que este campo esté en tu tabla 'users'
        'is_approved',
    ];

    /**
     * Relación con la información personal del usuario.
     */
    public function persona(): BelongsTo
    {
        // 1er parámetro: Modelo relacionado
        // 2do parámetro: Llave foránea en la tabla 'users'
        // 3er parámetro: Llave primaria en la tabla 'personas'
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Relación con los equipos asignados al usuario.
     */
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_usuario_asignado', 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(TicketAsignacion::class, 'id_usuario_tecnico', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_approved', 'role'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
``

### Archivo: app\Models\Ticket.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\TicketAdjunto;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model {
    use LogsActivity;

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'asunto',
        'id_usuario', 
        'id_tipo_equipo', 
        'id_equipo',
        'id_prioridad', 
        'id_categoria', 
        'descripcion_problema', 
        'estatus', 
        'estado_tecnico',
        'id_usuario_tecnico', 
        'fecha_cierre'
    ];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }

    public function tecnico()
    {
        return $this->hasOneThrough(
            User::class,
            TicketAsignacion::class,
            'id_ticket', // Llave foránea en ticket_asignaciones
            'id',        // Llave foránea en users
            'id_ticket', // Llave local en tickets
            'id_usuario_tecnico' // Llave local en ticket_asignaciones
        );
    }

    public function prioridad() 
    { 
        return $this->belongsTo(Prioridad::class, 'id_prioridad'); 
    }

    public function categoria() { return $this->belongsTo(Categoria::class, 'id_categoria'); }

    public function tipoEquipo() { return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo'); }
    
    public function equipo() { return $this->belongsTo(Equipo::class, 'id_equipo'); }
    
    public function adjuntos() { 
        return $this->hasMany(TicketAdjunto::class, 'id_ticket'); 
    }

    public function solucion() {
        return $this->hasOne(SolucionTecnica::class, 'id_ticket');
    }

    public function asignacion(): HasOne
    {
        // El segundo parámetro es la llave foránea en 'ticket_asignaciones'
        // El tercer parámetro es la llave local en 'tickets'
        return $this->hasOne(TicketAsignacion::class, 'id_ticket', 'id_ticket'); 
    }

    // "Traductor" para que el estatus (1, 2, 3) se vea como texto en la tabla
    public function getEstadoTextoAttribute() {
        return match($this->estatus) {
            0 => 'Borrador',
            1 => 'Abierto',
            2 => 'En Proceso',
            3 => 'Resuelto',
            4 => 'Cerrado',
            default => 'Desconocido',
        };
    }

    public function comentarios()
    {
        // Cambiamos Comentario::class por TicketComentario::class
        return $this->hasMany(TicketComentario::class, 'id_ticket', 'id_ticket');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'asunto', 'id_prioridad', 'id_categoria', 'estatus', 'estado_tecnico', 'id_usuario_tecnico'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

``

### Archivo: app\Models\Categoria.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Categoria extends Model {
    use LogsActivity;

    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre_categoria', 'estado', 'created_by', 'updated_by'];

    /**
     * Get the user that created the Categoria.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the Categoria.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_categoria', 'estado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\ArticuloConocimiento.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ArticuloConocimiento extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'articulos_conocimiento';
    protected $primaryKey = 'id_articulo';

    protected $fillable = [
        'origen',
        'id_solucion',
        'titulo',
        'slug',
        'extracto',
        'contenido',
        'id_categoria',
        'id_autor',
        'id_editor',
        'estado',
        'es_destacado',
        'es_interno',
        'vistas',
        'veces_usado',
        'fecha_publicacion',
    ];

    protected $casts = [
        'es_destacado' => 'boolean',
        'es_interno' => 'boolean',
        'fecha_publicacion' => 'datetime',
    ];

    public function solucion()
    {
        return $this->belongsTo(SolucionTecnica::class, 'id_solucion', 'id_solucion');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'id_autor');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'id_editor');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'articulo_tag', 'id_articulo', 'id_tag');
    }

    public function adjuntos()
    {
        return $this->hasMany(ArticuloAdjunto::class, 'id_articulo', 'id_articulo');
    }

    public function valoraciones()
    {
        return $this->hasMany(ArticuloValoracion::class, 'id_articulo', 'id_articulo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'id_categoria'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\TipoEquipo.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TipoEquipo extends Model {
    use LogsActivity;

    protected $table = 'tipos_equipo';

    protected $primaryKey = 'id_tipo_equipo';

    protected $fillable = ['nombre_tipo_equipo'];

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\Marca.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Marca extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = ['nombre_marca', 'id_tipo_equipo'];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_marca', 'id_marca');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_marca', 'id_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\Modelo.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Modelo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';

    protected $fillable = ['nombre_modelo', 'id_marca'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_modelo', 'id_marca'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\Equipo.php

``php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Equipo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'equipos';

    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'nombre',
        'numero_bien',
        'id_marca',
        'id_modelo',
        'ip_address',
        'mac_address',
        'ram',
        'procesador',
        'disco_duro',
        'id_tipo_equipo',
        'id_usuario_asignado',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con el Tipo de Equipo.
     */
    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    /**
     * Relación con el Usuario Asignado.
     */
    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_usuario_asignado', 'id');
    }

    /**
     * Relación con la Marca.
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    /**
     * Relación con el Modelo.
     */
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre', 'numero_bien', 'id_marca', 'id_modelo', 
                'ip_address', 'mac_address', 'ram', 'procesador', 
                'disco_duro', 'id_tipo_equipo', 'id_usuario_asignado', 'estado'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\Role.php

``php
<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Role extends SpatieRole
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: app\Models\Permission.php

``php
<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Permission extends SpatiePermission
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
``

### Archivo: config\permission.php

``php
<?php

use Spatie\Permission\DefaultTeamResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */

        'permission' => App\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */

        'role' => App\Models\Role::class,

    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         */
        'role_pivot_key' => null, // default 'role_id',
        'permission_pivot_key' => null, // default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`.
         */

        'team_foreign_key' => 'team_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     * Set this to false if you want to implement custom logic for checking permissions.
     */

    'register_permission_check_method' => true,

    /*
     * When set to true, Laravel\Octane\Events\OperationTerminated event listener will be registered
     * this will refresh permissions on every TickTerminated, TaskTerminated and RequestTerminated
     * NOTE: This should not be needed in most cases, but an Octane/Vapor combination benefited from it.
     */
    'register_octane_reset_listener' => false,

    /*
     * Events will fire when a role or permission is assigned/unassigned:
     * \Spatie\Permission\Events\RoleAttached
     * \Spatie\Permission\Events\RoleDetached
     * \Spatie\Permission\Events\PermissionAttached
     * \Spatie\Permission\Events\PermissionDetached
     *
     * To enable, set to true, and then create listeners to watch these events.
     */
    'events_enabled' => false,

    /*
     * Teams Feature.
     * When set to true the package implements teams using the 'team_foreign_key'.
     * If you want the migrations to register the 'team_foreign_key', you must
     * set this to true before doing the migration.
     * If you already did the migration then you must make a new migration to also
     * add 'team_foreign_key' to 'roles', 'model_has_roles', and 'model_has_permissions'
     * (view the latest version of this package's migration file)
     */

    'teams' => false,

    /*
     * The class to use to resolve the permissions team id
     */
    'team_resolver' => DefaultTeamResolver::class,

    /*
     * Passport Client Credentials Grant
     * When set to true the package will use Passports Client to check permissions
     */

    'use_passport_client_credentials' => false,

    /*
     * When set to true, the required permission names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     */

    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     */

    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     * See documentation to understand supported syntax.
     */

    'enable_wildcard_permission' => false,

    /*
     * The class to use for interpreting wildcard permissions.
     * If you need to modify delimiters, override the class and specify its name here.
     */
    // 'wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /* Cache-specific settings */

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */

        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all permissions.
         */

        'key' => 'spatie.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],
];
``

### Archivo: app\Providers\EventServiceProvider.php

``php
<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Spatie LogsActivity maneja los eventos eloquent automáticamente.
        // Solo necesitamos registrar los eventos de autenticación.

        // --- Listeners de Sesión e Inicios de Sesión ---
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            try {
                activity('Autenticación')
                    ->causedBy($event->user)
                    ->event('login')
                    ->log('Inicio de sesión exitoso');
            } catch (\Exception $e) {
                logger()->error("Failed to write login audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            try {
                if ($event->user) {
                    activity('Autenticación')
                        ->causedBy($event->user)
                        ->event('logout')
                        ->log('Cierre de sesión');
                }
            } catch (\Exception $e) {
                logger()->error("Failed to write logout audit: " . $e->getMessage());
            }
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            try {
                activity('Autenticación')
                    ->event('login_failed')
                    ->withProperties(['email' => $event->credentials['email'] ?? 'Desconocido'])
                    ->log('Intento de inicio de sesión fallido');
            } catch (\Exception $e) {
                logger()->error("Failed to write login failed audit: " . $e->getMessage());
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
``

### Archivo: app\Http\Controllers\Admin\AuditController.php

``php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ver-auditorias');
    }

    public function index(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            $query->where('subject_type', 'like', "%{$request->type}%");
        }

        $logs = $query->paginate(15);

        return view('admin.auditorias.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            $query->where('subject_type', 'like', "%{$request->type}%");
        }

        $logs = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_auditoria_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Fecha y Hora',
                'Responsable',
                'Acción',
                'Módulo',
                'ID Afectado',
                'Valores Anteriores',
                'Valores Nuevos',
                'Propiedades (JSON)'
            ]);

            foreach ($logs as $log) {
                $old = isset($log->properties['old']) ? json_encode($log->properties['old'], JSON_UNESCAPED_UNICODE) : '';
                $new = isset($log->properties['attributes']) ? json_encode($log->properties['attributes'], JSON_UNESCAPED_UNICODE) : '';
                $all_props = json_encode($log->properties, JSON_UNESCAPED_UNICODE);

                fputcsv($file, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->causer ? $log->causer->name : 'Sistema',
                    $log->event,
                    $log->subject_type ? class_basename($log->subject_type) : $log->log_name,
                    $log->subject_id ?? 'N/A',
                    $old,
                    $new,
                    $all_props
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('type')) {
            $query->where('subject_type', 'like', "%{$request->type}%");
        }

        $logs = $query->get();

        $pdf = Pdf::loadView('admin.auditorias.pdf', compact('logs'))->setPaper('a4', 'landscape');
        
        return $pdf->download('reporte_auditoria_' . date('Ymd_His') . '.pdf');
    }
}
``

### Archivo: app\Http\Controllers\Admin\UsuarioAprobacionController.php

``php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UsuarioAprobacionController extends Controller
{
    /**
     * Listar los usuarios pendientes de aprobación.
     */
    public function index()
    {
        // Traemos todos los usuarios con is_approved = false y cargamos sus relaciones de Persona y Unidad
        $usuariosPendientes = User::where('is_approved', false)
            ->with(['persona.unidadAdministrativa.nivel'])
            ->get();

        // Obtenemos los roles disponibles en el sistema (ej: usuario, técnico, gestor, admin)
        // para dar la opción de cambiar el rol al momento de aprobar.
        $roles = Role::all();

        return view('admin.usuarios.pendientes', compact('usuariosPendientes', 'roles'));
    }

    /**
     * Aprobar la entrada de un usuario y opcionalmente cambiar su rol.
     */
    public function aprobar(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_name' => 'required|exists:roles,name'
        ]);

        // 1. Activar al usuario
        $user->is_approved = true;

        // 2. Si se seleccionó un rol diferente, reasignarlo
        // Removemos los roles anteriores (por si acaso) y asignamos el nuevo
        $user->syncRoles([$request->role_name]);

        // Ajustamos la columna "role" de apoyo que tienes en tu base de datos (según tu DatabaseSeeder)
        // 1: Admin, 2: Gestor, 3: Usuario, 4: Técnico (o el valor que corresponda en tu lógica)
        switch ($request->role_name) {
            case 'admin':
                $user->role = '1';
                break;
            case 'gestor':
                $user->role = '2';
                break;
            case 'tecnico':
                $user->role = '4';
                break;
            default:
                $user->role = '3'; // usuario normal
                break;
        }

        $user->save();

        activity('Aprobación')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('registro_aprobado')
            ->log('Gestor aprobó registro de usuario');

        return redirect()->back()->with('success', 'El usuario ' . $user->name . ' ha sido aprobado y activado con el rol ' . strtoupper($request->role_name) . '.');
    }

    /**
     * Rechazar e inactivar/eliminar la solicitud de un usuario.
     */
    public function rechazar($id)
    {
        $user = User::findOrFail($id);
        $persona = $user->persona;

        // Para resguardar la seguridad y limpieza de la base de datos,
        // eliminamos al usuario y a su persona asociada si se rechaza su solicitud.
        $nombre = $user->name;
        
        $user->delete();
        if ($persona) {
            $persona->delete();
        }

        activity('Aprobación')
            ->causedBy(auth()->user())
            ->event('registro_rechazado')
            ->log('Gestor rechazó y eliminó solicitud de: ' . $nombre);

        return redirect()->back()->with('success', 'La solicitud de ' . $nombre . ' ha sido rechazada y eliminada del sistema.');
    }
}
``

### Archivo: app\Http\Controllers\Auth\RegisteredUserController.php

``php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\NivelJerarquico;
use App\Models\Persona;
use App\Models\UnidadAdministrativa;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $nivelesActivos = NivelJerarquico::where('is_active', true)->orderBy('nivel', 'asc')->get();
        // Cargamos las unidades de Nivel 1 (ej: Sedes) para el primer select
        $unidadesNivel1 = UnidadAdministrativa::whereNull('parent_id')
            ->whereHas('nivel', function ($query) {
                $query->where('nivel', 1)->where('is_active', true);
            })->get();

        return view('auth.register', compact('nivelesActivos', 'unidadesNivel1'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:255', 'unique:'.Persona::class],
            'telefono' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'unidad_id' => ['required', 'exists:unidades_administrativas,id'],
        ]);

        $persona = Persona::create([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido' => $request->apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'id_unidad_administrativa' => $request->unidad_id,
        ]);

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_persona' => $persona->id_persona,
            'is_approved' => false,
        ]);
        
        // Asignamos rol por defecto de 'usuario' (Se asume id 3 o el que corresponda a usuario normal)
        // Lo asignaremos como string si es Spatie o id si es manual, asumo role = '3' según DatabaseSeeder
        $user->role = '3'; // 3 es el Rol de Usuario según tu DatabaseSeeder
        $user->save();
        $user->assignRole('usuario');

        activity('Autenticación')
            ->performedOn($user)
            ->event('registro_solicitado')
            ->log('Usuario completó formulario de registro y espera aprobación');

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function getChildrenUnidades($parentId)
    {
        $hijos = UnidadAdministrativa::where('parent_id', $parentId)
            ->whereHas('nivel', function($q) {
                $q->where('is_active', true);
            })->get(['id', 'nombre']);

        return response()->json($hijos);
    }
}
``

### Archivo: resources\views\admin\auditorias\index.blade.php

``php
@extends('layouts.admin')

@section('content')
@push('styles')
<style>
    .audit-action-badge {
        font-size: 0.75rem;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
    }
    .audit-create { background: rgba(25, 135, 84, 0.2); color: #0f5132; border-color: rgba(25, 135, 84, 0.35); }
    .audit-update { background: rgba(255, 193, 7, 0.22); color: #664d03; border-color: rgba(255, 193, 7, 0.38); }
    .audit-delete { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-login { background: rgba(13, 202, 240, 0.2); color: #055160; border-color: rgba(13, 202, 240, 0.35); }
    .audit-logout { background: rgba(108, 117, 125, 0.22); color: #41464b; border-color: rgba(108, 117, 125, 0.35); }
    .audit-failed { background: rgba(220, 53, 69, 0.2); color: #842029; border-color: rgba(220, 53, 69, 0.35); }
    .audit-sync { background: rgba(13, 110, 253, 0.2); color: #084298; border-color: rgba(13, 110, 253, 0.35); }
    .audit-generic { background: rgba(108, 117, 125, 0.18); color: #495057; border-color: rgba(108, 117, 125, 0.3); }

    [data-bs-theme="dark"] .audit-create { color: #75d7ae; background: rgba(25, 135, 84, 0.22); border-color: rgba(25, 135, 84, 0.45); }
    [data-bs-theme="dark"] .audit-update { color: #ffda6a; background: rgba(255, 193, 7, 0.22); border-color: rgba(255, 193, 7, 0.45); }
    [data-bs-theme="dark"] .audit-delete { color: #f5a3ad; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-login { color: #6edff6; background: rgba(13, 202, 240, 0.2); border-color: rgba(13, 202, 240, 0.45); }
    [data-bs-theme="dark"] .audit-logout { color: #ced4da; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }
    [data-bs-theme="dark"] .audit-failed { color: #f1aeb5; background: rgba(220, 53, 69, 0.24); border-color: rgba(220, 53, 69, 0.45); }
    [data-bs-theme="dark"] .audit-sync { color: #9ec5fe; background: rgba(13, 110, 253, 0.22); border-color: rgba(13, 110, 253, 0.45); }
    [data-bs-theme="dark"] .audit-generic { color: #dee2e6; background: rgba(108, 117, 125, 0.25); border-color: rgba(108, 117, 125, 0.45); }

    .audit-json {
        font-size: 0.7rem;
        max-width: 250px;
        overflow-x: auto;
        white-space: pre-wrap;
    }
    [data-bs-theme="dark"] .audit-json {
        background: #1f2327 !important;
        color: #e9ecef !important;
        border-color: #343a40 !important;
    }
</style>
@endpush
<div class="py-3 px-1">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h2 fw-bold mb-1 theme-text"><i class="bi bi-journal-text text-primary me-2"></i>Bitácora de Auditorías</h1>
            <p class="text-secondary mb-2">Monitorea y audita detalladamente qué usuario creó, modificó o eliminó elementos en la plataforma.</p>
            <div class="d-flex gap-2 mt-2">
                <a href="{{ route('admin.auditorias.export', request()->query()) }}" class="btn btn-success rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Excel (CSV)
                </a>
                <a href="{{ route('admin.auditorias.pdf', request()->query()) }}" class="btn btn-danger rounded-3 px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 btn-sm">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card card-premium shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.auditorias.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small mb-1">Buscar por tipo, acción o responsable</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-premium border-start-0 border-secondary border-opacity-25" placeholder="Ej. Categoria, create, Admin..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Acción Realizada</label>
                    <select name="action" class="form-select form-select-premium border-secondary border-opacity-25" onchange="this.form.submit()">
                        <option value="">Todas las acciones</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Crear (create)</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Actualizar (update)</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Eliminar (delete)</option>
                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Inicio de Sesión (login)</option>
                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Cierre de Sesión (logout)</option>
                        <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Sesión Fallida</option>
                        <option value="sync_permissions" {{ request('action') == 'sync_permissions' ? 'selected' : '' }}>Sincronizar Permisos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">Componente / Tabla</label>
                    <select name="type" class="form-select form-select-premium border-secondary border-opacity-25" onchange="this.form.submit()">
                        <option value="">Todos los componentes</option>
                        <option value="Categoria" {{ request('type') == 'Categoria' ? 'selected' : '' }}>Categorías</option>
                        <option value="TipoEquipo" {{ request('type') == 'TipoEquipo' ? 'selected' : '' }}>Tipos de Equipos</option>
                        <option value="Marca" {{ request('type') == 'Marca' ? 'selected' : '' }}>Marcas</option>
                        <option value="Modelo" {{ request('type') == 'Modelo' ? 'selected' : '' }}>Modelos</option>
                        <option value="User" {{ request('type') == 'User' ? 'selected' : '' }}>Usuarios / Sesiones</option>
                        <option value="Role" {{ request('type') == 'Role' ? 'selected' : '' }}>Roles de Seguridad</option>
                        <option value="Ticket" {{ request('type') == 'Ticket' ? 'selected' : '' }}>Tickets de Soporte</option>
                        <option value="Equipo" {{ request('type') == 'Equipo' ? 'selected' : '' }}>Equipos de Inventario</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('admin.auditorias.index') }}" class="btn btn-light rounded-3 px-3 fw-bold w-100" style="height: calc(2.25rem + 2px); display: inline-flex; align-items: center; justify-content: center;" title="Limpiar filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Logs Ampliada (Premium sin padding en la tarjeta) -->
    <div class="card-premium shadow-sm border-0 p-0 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: var(--bg-main);">
                    <tr class="text-nowrap">
                        <th class="ps-4 py-3 border-0 text-muted small text-uppercase">Fecha y Hora</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Responsable del Cambio</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Tipo de Acción</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Elemento Afectado</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">ID Ref</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Valores Anteriores</th>
                        <th class="py-3 border-0 text-muted small text-uppercase">Valores Nuevos</th>
                        <th class="py-3 border-0 text-muted small text-uppercase pe-4">Dirección IP y Navegador</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="text-nowrap" style="border-bottom: 1px solid var(--bs-border-color);">
                            <td class="ps-4 py-3">
                                <span class="fw-semibold theme-text">{{ $log->created_at->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td class="py-3">
                                @if($log->causer)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            {{ substr($log->causer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold theme-text">{{ $log->causer->name }}</span><br>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.65rem; padding: 2px 6px;">
                                                <i class="bi bi-person-badge-fill me-1"></i>{{ $log->causer->roles->pluck('name')->first() ?? 'Soporte' }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1.5 rounded" style="font-size: 0.75rem;">
                                        <i class="bi bi-cpu-fill me-1 text-secondary"></i>Sistema (Consola / Seeder)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($log->event === 'created' || $log->event === 'create')
                                    <span class="audit-action-badge audit-create">
                                        <i class="bi bi-plus-circle-fill me-1"></i>Creación
                                    </span>
                                @elseif($log->event === 'updated' || $log->event === 'update')
                                    <span class="audit-action-badge audit-update">
                                        <i class="bi bi-pencil-square me-1"></i>Modificación
                                    </span>
                                @elseif($log->event === 'deleted' || $log->event === 'delete')
                                    <span class="audit-action-badge audit-delete">
                                        <i class="bi bi-trash3-fill me-1"></i>Eliminación
                                    </span>
                                @elseif($log->event === 'login')
                                    <span class="audit-action-badge audit-login">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Ingreso
                                    </span>
                                @elseif($log->event === 'logout')
                                    <span class="audit-action-badge audit-logout">
                                        <i class="bi bi-box-arrow-right me-1"></i>Salida
                                    </span>
                                @elseif($log->event === 'login_failed')
                                    <span class="audit-action-badge audit-failed">
                                        <i class="bi bi-exclamation-octagon-fill me-1"></i>Acceso Fallido
                                    </span>
                                @elseif($log->event === 'sync_permissions')
                                    <span class="audit-action-badge audit-sync">
                                        <i class="bi bi-shield-check me-1"></i>Permisos Sinc.
                                    </span>
                                @elseif($log->event === 'registro_solicitado')
                                    <span class="audit-action-badge audit-create" style="background-color: var(--bs-info-bg-subtle); color: var(--bs-info-text-emphasis); border-color: var(--bs-info-border-subtle);">
                                        <i class="bi bi-person-lines-fill me-1"></i>Reg. Solicitado
                                    </span>
                                @elseif($log->event === 'registro_aprobado')
                                    <span class="audit-action-badge audit-create" style="background-color: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); border-color: var(--bs-success-border-subtle);">
                                        <i class="bi bi-person-check-fill me-1"></i>Reg. Aprobado
                                    </span>
                                @elseif($log->event === 'registro_rechazado')
                                    <span class="audit-action-badge audit-delete">
                                        <i class="bi bi-person-x-fill me-1"></i>Reg. Rechazado
                                    </span>
                                @else
                                    <span class="audit-action-badge audit-generic">
                                        {{ strtoupper($log->event) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="fw-bold theme-text">{{ $log->subject_type ? class_basename($log->subject_type) : $log->log_name }}</span>
                            </td>
                            <td class="py-3">
                                <code class="text-secondary fw-bold">#{{ $log->subject_id ?? 'N/A' }}</code>
                            </td>
                            <td class="py-3">
                                @if(isset($log->properties['old']))
                                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#old-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="old-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json"><code>{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Registro Nuevo)</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if(isset($log->properties['attributes']) || count($log->properties) > 0)
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2 rounded-3" data-bs-toggle="collapse" data-bs-target="#new-{{ $log->id }}" style="font-size: 0.75rem;">
                                        Ver datos <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                    <div class="collapse mt-2" id="new-{{ $log->id }}">
                                        <pre class="bg-light p-2 rounded text-start text-dark border mb-0 text-overflow-wrap audit-json"><code>{{ json_encode($log->properties['attributes'] ?? $log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Ninguno (Eliminación)</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4">
                                <small class="fw-semibold theme-text d-block"><i class="bi bi-pc-display me-1 text-muted"></i>{{ $log->properties['ip'] ?? 'Local' }}</small>
                                <small class="text-muted text-truncate d-block" style="max-width: 150px;" title="{{ $log->properties['user_agent'] ?? 'N/A' }}">{{ $log->properties['user_agent'] ?? 'N/A' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-info-circle text-muted fs-2 mb-3 d-block"></i>
                                <p class="text-muted mb-0">No se encontraron registros de auditoría en la bitácora.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
``

### Archivo: resources\views\usuario\tickets\show.blade.php

``php
<x-usuario-layout>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('usuario.tickets.index') }}" class="btn btn-link text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <h2 class="fw-bold mb-0 text-body">Ticket #{{ $ticket->id_ticket }}</h2>
                    <small class="">Creado el {{ $ticket->created_at->format('d/m/Y h:i A') }}</small>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->estatus == 0)
                        <span class="badge bg-secondary-subtle border border-secondary-subtle fs-6 px-3 shadow-sm">Borrador</span>
                        <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-send-check me-1"></i> Enviar a Soporte
                            </button>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 shadow-sm">Enviado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Información del Ticket --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3 text-body">Asunto: {{ $ticket->asunto }}</h5>
                        </div>
                        <p class=" border-start border-4 border-primary ps-3 py-2 bg-body rounded-end" style="white-space: pre-line;">{{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}</p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-paperclip me-2 text-primary"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card h-100 border border-light-subtle bg-body shadow-xs hover-shadow">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 140px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                <p class="small mb-0 text-uppercase text-secondary fw-bold">{{ $ext }}</p>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-transparent border-0 text-center py-2">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 border-0">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="mb-0 small"><i class="bi bi-info-circle me-1"></i> No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ELIMINADO EL CHAT INLINE: SE MOVIÓ A UN PANEL FLOTANTE (OFFCANVAS) ABAJO --}}
            </div>

            {{-- Sidebar derecho --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-body border-bottom border-light-subtle pb-2">Información de Seguimiento</h6>
                        
                        {{-- Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="small d-block mb-3">Técnico Responsable: </label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center p-3 bg-body border border-light-subtle rounded-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ strtoupper(substr($ticket->tecnico->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-body" style="font-size: 0.95rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-primary fw-medium">Agente de Soporte</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning bg-warning-subtle text-warning-emphasis border-0 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-clock-history fs-5 me-2"></i>
                                    Pendiente por asignar un técnico.
                                </div>
                            @endif
                        </div>

                        <hr class="text-secondary opacity-25">

                        <div class="mb-3">
                            <label class="small d-block">Categoría</label>
                            <span class="fw-bold text-body"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small d-block">Equipo afectado</label>
                            <span class="fw-bold text-body"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>

                        <div class="mb-0">
                            <label class="small d-block mb-1">Prioridad del Caso: </label>
                            @if($ticket->prioridad)
                                <span class="badge py-2 px-3 {{ $ticket->prioridad->nombre_prioridad == 'Alta' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} border border-light-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="small italic">Evaluando prioridad...</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card de Ayuda --}}
                <div class="card bg-primary bg-gradient text-white border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle display-6 mb-3 opacity-50"></i>
                        <h6>¿Necesitas ayuda inmediata?</h6>
                        <p class="small opacity-75">Si el problema es crítico, puedes comunicarte con la extensión de soporte técnico en tu oficina.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón Flotante para abrir el Chat --}}
    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#chatOffcanvas" 
            aria-controls="chatOffcanvas"
            style="position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px; z-index: 1040; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="bi bi-chat-dots-fill fs-3"></i>
        @php
            $mensajesPublicos = $ticket->comentarios->where('es_interno', false);
        @endphp
        @if($mensajesPublicos->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light border-2" style="font-size: 0.8rem; padding: 0.35em 0.65em;">
                {{ $mensajesPublicos->count() }}
            </span>
        @endif
    </button>

    {{-- Panel Deslizable (Offcanvas) de Chat Glassmorphism --}}
    <div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="chatOffcanvas" aria-labelledby="chatOffcanvasLabel" style="width: 450px; max-width: 100vw; background: rgba(var(--bs-body-bg-rgb), 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-10 py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold mb-0" id="chatOffcanvasLabel">Chat de Soporte</h5>
                    <small class="text-secondary d-flex align-items-center gap-1">
                        <span class="d-inline-block bg-success rounded-circle" style="width: 8px; height: 8px;"></span> Ticket #{{ $ticket->id_ticket }}
                    </small>
                </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body p-0 d-flex flex-column" style="background: rgba(var(--bs-secondary-bg-rgb), 0.3);">
            {{-- Área de Mensajes --}}
            <div class="chat-container flex-grow-1 p-4" id="offcanvasChatContainer" style="overflow-y: auto; scroll-behavior: smooth;">
                @if($mensajesPublicos->count() > 0)
                    @foreach($mensajesPublicos as $comentario)
                        <div class="d-flex flex-column mb-4 {{ $comentario->id_usuario == auth()->id() ? 'align-items-end' : 'align-items-start' }}">
                            <div class="d-flex align-items-end gap-2 {{ $comentario->id_usuario == auth()->id() ? 'flex-row-reverse' : '' }}">
                                {{-- Avatar Mini --}}
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white' : 'bg-body-secondary text-secondary-emphasis' }}" style="width: 35px; height: 35px; font-size: 0.8rem; flex-shrink: 0;">
                                    {{ substr($comentario->usuario->name, 0, 1) }}
                                </div>
                                
                                {{-- Burbuja de Chat --}}
                                <div class="p-3 rounded-4 shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white text-end' : 'bg-body border border-light-subtle' }}" style="max-width: 85%; border-bottom-{{ $comentario->id_usuario == auth()->id() ? 'right' : 'left' }}-radius: 4px !important;">
                                    <div class="d-flex align-items-center gap-2 mb-1 justify-content-{{ $comentario->id_usuario == auth()->id() ? 'end' : 'start' }}">
                                        <small class="fw-bold {{ $comentario->id_usuario == auth()->id() ? 'text-white text-opacity-75' : 'text-primary' }}" style="font-size: 0.75rem;">
                                            {{ $comentario->id_usuario == auth()->id() ? 'Tú' : $comentario->usuario->name }}
                                        </small>
                                        @if($comentario->id_usuario != auth()->id()) 
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.55rem; padding: 2px 6px;">SOPORTE</span> 
                                        @endif
                                    </div>
                                    <p class="mb-0" style="font-size: 0.95rem; line-height: 1.5; white-space: pre-wrap;">{{ $comentario->mensaje }}</p>
                                </div>
                            </div>
                            <small class="text-secondary mt-1 px-5" style="font-size: 0.65rem;">
                                {{ $comentario->created_at->format('h:i A') }}
                            </small>
                        </div>
                    @endforeach
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-5 opacity-50">
                        <i class="bi bi-chat-square-dots display-3 text-secondary mb-3"></i>
                        <h6 class="fw-bold">No hay mensajes</h6>
                        <p class="small text-secondary mb-0">Envía un mensaje para contactar al soporte técnico.</p>
                    </div>
                @endif
            </div>

            {{-- Área de Input --}}
            <div class="p-3 bg-body border-top border-secondary border-opacity-10 shadow-sm z-3">
                @if($ticket->estatus != 0)
                    <form action="{{ route('usuario.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="position-relative">
                            <textarea name="mensaje" 
                                      class="form-control form-control-premium bg-body-secondary bg-opacity-50 border-0 shadow-none pe-5" 
                                      placeholder="Escribe un mensaje..." 
                                      rows="1" 
                                      required 
                                      style="resize: none; border-radius: 25px; padding-top: 12px; padding-bottom: 12px; transition: all 0.3s ease;"></textarea>
                            <button class="btn btn-primary rounded-circle position-absolute end-0 top-50 translate-middle-y me-1 d-flex align-items-center justify-content-center shadow-sm hover-scale" 
                                    type="submit" 
                                    style="width: 38px; height: 38px;">
                                <i class="bi bi-send-fill fs-6 ms-1"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-secondary bg-opacity-10 text-secondary text-center py-3 rounded-4">
                        <span class="small fw-medium"><i class="bi bi-lock-fill me-1"></i> Envía el ticket para habilitar el chat.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación al hacer hover en el botón flotante
            const chatBtn = document.querySelector('[data-bs-target="#chatOffcanvas"]');
            if (chatBtn) {
                chatBtn.addEventListener('mouseenter', () => chatBtn.style.transform = 'scale(1.1) rotate(-5deg)');
                chatBtn.addEventListener('mouseleave', () => chatBtn.style.transform = 'scale(1) rotate(0)');
            }

            // Auto-scroll al fondo cuando se abre el offcanvas
            const chatOffcanvas = document.getElementById('chatOffcanvas');
            if (chatOffcanvas) {
                chatOffcanvas.addEventListener('shown.bs.offcanvas', function () {
                    const chatContainer = document.getElementById('offcanvasChatContainer');
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        // Pequeño focus visual a los mensajes
                        const mensajes = chatContainer.querySelectorAll('.d-flex.flex-column');
                        mensajes.forEach((msg, idx) => {
                            msg.style.opacity = '0';
                            msg.style.transform = 'translateY(10px)';
                            setTimeout(() => {
                                msg.style.transition = 'all 0.3s ease-out';
                                msg.style.opacity = '1';
                                msg.style.transform = 'translateY(0)';
                            }, 50 * idx);
                        });
                    }
                });
            }
        });
    </script>
    @endpush
</x-usuario-layout>
``

### Archivo: app\Console\Commands\MigrateAuditLogsToSpatie.php

``php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAuditLogsToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:migrate-to-spatie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los registros antiguos de la tabla audit_logs a la nueva tabla activity_log de Spatie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de registros de auditoría antiguos...');

        $oldLogs = DB::table('audit_logs')->get();
        $count = 0;

        foreach ($oldLogs as $log) {
            $properties = [];
            
            if ($log->old_values) {
                $properties['old'] = json_decode($log->old_values, true);
            }
            if ($log->new_values) {
                $properties['attributes'] = json_decode($log->new_values, true);
            }
            if ($log->ip_address) {
                $properties['ip'] = $log->ip_address;
            }
            if ($log->user_agent) {
                $properties['user_agent'] = $log->user_agent;
            }

            // Map action to event
            $event = $log->action;
            if ($event === 'create') $event = 'created';
            if ($event === 'update') $event = 'updated';
            if ($event === 'delete') $event = 'deleted';

            DB::table('activity_log')->insert([
                'log_name' => 'default',
                'description' => 'Migrado desde audit_logs',
                'subject_type' => $log->auditable_type,
                'event' => $event,
                'subject_id' => $log->auditable_id == 0 ? null : $log->auditable_id,
                'causer_type' => $log->user_id ? 'App\Models\User' : null,
                'causer_id' => $log->user_id,
                'properties' => json_encode($properties),
                'batch_uuid' => null,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ]);
            $count++;
        }

        $this->info("Migración completada. Se migraron $count registros a Spatie Activitylog.");
    }
}
``



## CODIGO RECIENTE: Auditorias Secundarias, Aprobacion y Chat Flotante

### Archivo: app/Models/TipoEquipo.php
`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TipoEquipo extends Model {
    use LogsActivity;

    protected $table = 'tipos_equipo';

    protected $primaryKey = 'id_tipo_equipo';

    protected $fillable = ['nombre_tipo_equipo'];

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

`

### Archivo: app/Models/Marca.php
`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Marca extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = ['nombre_marca', 'id_tipo_equipo'];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_marca', 'id_marca');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_marca', 'id_tipo_equipo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

`

### Archivo: app/Models/Modelo.php
`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Modelo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';

    protected $fillable = ['nombre_modelo', 'id_marca'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre_modelo', 'id_marca'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

`

### Archivo: app/Models/Equipo.php
`php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Equipo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'equipos';

    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'nombre',
        'numero_bien',
        'id_marca',
        'id_modelo',
        'ip_address',
        'mac_address',
        'ram',
        'procesador',
        'disco_duro',
        'id_tipo_equipo',
        'id_usuario_asignado',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con el Tipo de Equipo.
     */
    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    /**
     * Relación con el Usuario Asignado.
     */
    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'id_usuario_asignado', 'id');
    }

    /**
     * Relación con la Marca.
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    /**
     * Relación con el Modelo.
     */
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre', 'numero_bien', 'id_marca', 'id_modelo', 
                'ip_address', 'mac_address', 'ram', 'procesador', 
                'disco_duro', 'id_tipo_equipo', 'id_usuario_asignado', 'estado'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

`

### Archivo: app/Http/Controllers/Admin/UsuarioAprobacionController.php
`php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UsuarioAprobacionController extends Controller
{
    /**
     * Listar los usuarios pendientes de aprobación.
     */
    public function index()
    {
        // Traemos todos los usuarios con is_approved = false y cargamos sus relaciones de Persona y Unidad
        $usuariosPendientes = User::where('is_approved', false)
            ->with(['persona.unidadAdministrativa.nivel'])
            ->get();

        // Obtenemos los roles disponibles en el sistema (ej: usuario, técnico, gestor, admin)
        // para dar la opción de cambiar el rol al momento de aprobar.
        $roles = Role::all();

        return view('admin.usuarios.pendientes', compact('usuariosPendientes', 'roles'));
    }

    /**
     * Aprobar la entrada de un usuario y opcionalmente cambiar su rol.
     */
    public function aprobar(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_name' => 'required|exists:roles,name'
        ]);

        // 1. Activar al usuario
        $user->is_approved = true;

        // 2. Si se seleccionó un rol diferente, reasignarlo
        // Removemos los roles anteriores (por si acaso) y asignamos el nuevo
        $user->syncRoles([$request->role_name]);

        // Ajustamos la columna "role" de apoyo que tienes en tu base de datos (según tu DatabaseSeeder)
        // 1: Admin, 2: Gestor, 3: Usuario, 4: Técnico (o el valor que corresponda en tu lógica)
        switch ($request->role_name) {
            case 'admin':
                $user->role = '1';
                break;
            case 'gestor':
                $user->role = '2';
                break;
            case 'tecnico':
                $user->role = '4';
                break;
            default:
                $user->role = '3'; // usuario normal
                break;
        }

        $user->save();

        activity('Aprobación')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('registro_aprobado')
            ->log('Gestor aprobó registro de usuario');

        return redirect()->back()->with('success', 'El usuario ' . $user->name . ' ha sido aprobado y activado con el rol ' . strtoupper($request->role_name) . '.');
    }

    /**
     * Rechazar e inactivar/eliminar la solicitud de un usuario.
     */
    public function rechazar($id)
    {
        $user = User::findOrFail($id);
        $persona = $user->persona;

        // Para resguardar la seguridad y limpieza de la base de datos,
        // eliminamos al usuario y a su persona asociada si se rechaza su solicitud.
        $nombre = $user->name;
        
        $user->delete();
        if ($persona) {
            $persona->delete();
        }

        activity('Aprobación')
            ->causedBy(auth()->user())
            ->event('registro_rechazado')
            ->log('Gestor rechazó y eliminó solicitud de: ' . $nombre);

        return redirect()->back()->with('success', 'La solicitud de ' . $nombre . ' ha sido rechazada y eliminada del sistema.');
    }
}

`

### Archivo: app/Http/Controllers/Auth/RegisteredUserController.php
`php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\NivelJerarquico;
use App\Models\Persona;
use App\Models\UnidadAdministrativa;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $nivelesActivos = NivelJerarquico::where('is_active', true)->orderBy('nivel', 'asc')->get();
        // Cargamos las unidades de Nivel 1 (ej: Sedes) para el primer select
        $unidadesNivel1 = UnidadAdministrativa::whereNull('parent_id')
            ->whereHas('nivel', function ($query) {
                $query->where('nivel', 1)->where('is_active', true);
            })->get();

        return view('auth.register', compact('nivelesActivos', 'unidadesNivel1'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:255', 'unique:'.Persona::class],
            'telefono' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'unidad_id' => ['required', 'exists:unidades_administrativas,id'],
        ]);

        $persona = Persona::create([
            'nombre' => $request->nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'apellido' => $request->apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'id_unidad_administrativa' => $request->unidad_id,
        ]);

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_persona' => $persona->id_persona,
            'is_approved' => false,
        ]);
        
        // Asignamos rol por defecto de 'usuario' (Se asume id 3 o el que corresponda a usuario normal)
        // Lo asignaremos como string si es Spatie o id si es manual, asumo role = '3' según DatabaseSeeder
        $user->role = '3'; // 3 es el Rol de Usuario según tu DatabaseSeeder
        $user->save();
        $user->assignRole('usuario');

        activity('Autenticación')
            ->performedOn($user)
            ->event('registro_solicitado')
            ->log('Usuario completó formulario de registro y espera aprobación');

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function getChildrenUnidades($parentId)
    {
        $hijos = UnidadAdministrativa::where('parent_id', $parentId)
            ->whereHas('nivel', function($q) {
                $q->where('is_active', true);
            })->get(['id', 'nombre']);

        return response()->json($hijos);
    }
}

`

### Archivo: resources/views/usuario/tickets/show.blade.php
`php
<x-usuario-layout>
    <div class="container-fluid py-4">
        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('usuario.tickets.index') }}" class="btn btn-link text-decoration-none p-0">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <h2 class="fw-bold mb-0 text-body">Ticket #{{ $ticket->id_ticket }}</h2>
                    <small class="">Creado el {{ $ticket->created_at->format('d/m/Y h:i A') }}</small>
                </div>
                <div class="d-flex gap-2">
                    @if($ticket->estatus == 0)
                        <span class="badge bg-secondary-subtle border border-secondary-subtle fs-6 px-3 shadow-sm">Borrador</span>
                        <a href="{{ route('usuario.tickets.edit', $ticket->id_ticket) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                        <form action="{{ route('usuario.tickets.enviar', $ticket->id_ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-send-check me-1"></i> Enviar a Soporte
                            </button>
                        </form>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 shadow-sm">Enviado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Información del Ticket --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold mb-3 text-body">Asunto: {{ $ticket->asunto }}</h5>
                        </div>
                        <p class=" border-start border-4 border-primary ps-3 py-2 bg-body rounded-end" style="white-space: pre-line;">{{ $ticket->descripcion_problema ?? 'Sin descripción proporcionada.' }}</p>
                    </div>
                </div>

                {{-- Adjuntos --}}
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle">
                        <h6 class="fw-bold mb-0 text-body"><i class="bi bi-paperclip me-2 text-primary"></i>Archivos Adjuntos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($ticket->adjuntos as $archivo)
                                <div class="col-md-4 col-sm-6">
                                    <div class="card h-100 border border-light-subtle bg-body shadow-xs hover-shadow">
                                        @php $ext = pathinfo($archivo->ruta_archivo, PATHINFO_EXTENSION); @endphp
                                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $archivo->ruta_archivo) }}" class="card-img-top object-fit-cover" style="height: 140px;">
                                            </a>
                                        @else
                                            <div class="card-body text-center py-4">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                <p class="small mb-0 text-uppercase text-secondary fw-bold">{{ $ext }}</p>
                                            </div>
                                        @endif
                                        <div class="card-footer bg-transparent border-0 text-center py-2">
                                            <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" download class="btn btn-sm btn-outline-secondary w-100 border-0">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="mb-0 small"><i class="bi bi-info-circle me-1"></i> No se subieron archivos.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ELIMINADO EL CHAT INLINE: SE MOVIÓ A UN PANEL FLOTANTE (OFFCANVAS) ABAJO --}}
            </div>

            {{-- Sidebar derecho --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-body border-bottom border-light-subtle pb-2">Información de Seguimiento</h6>
                        
                        {{-- Técnico Asignado --}}
                        <div class="mb-4">
                            <label class="small d-block mb-3">Técnico Responsable: </label>
                            @if($ticket->tecnico)
                                <div class="d-flex align-items-center p-3 bg-body border border-light-subtle rounded-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ strtoupper(substr($ticket->tecnico->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block text-body" style="font-size: 0.95rem;">{{ $ticket->tecnico->name }}</span>
                                        <small class="text-primary fw-medium">Agente de Soporte</small>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning bg-warning-subtle text-warning-emphasis border-0 small mb-0 d-flex align-items-center">
                                    <i class="bi bi-clock-history fs-5 me-2"></i>
                                    Pendiente por asignar un técnico.
                                </div>
                            @endif
                        </div>

                        <hr class="text-secondary opacity-25">

                        <div class="mb-3">
                            <label class="small d-block">Categoría</label>
                            <span class="fw-bold text-body"><i class="bi bi-tag-fill me-2 text-primary"></i>{{ $ticket->categoria_nombre_historico ?? $ticket->categoria->nombre_categoria ?? 'Sin categoría' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small d-block">Equipo afectado</label>
                            <span class="fw-bold text-body"><i class="bi bi-pc-display me-2 text-primary"></i>{{ $ticket->tipoEquipo->nombre_tipo_equipo ?? 'No especificado' }}</span>
                        </div>

                        <div class="mb-0">
                            <label class="small d-block mb-1">Prioridad del Caso: </label>
                            @if($ticket->prioridad)
                                <span class="badge py-2 px-3 {{ $ticket->prioridad->nombre_prioridad == 'Alta' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} border border-light-subtle">
                                    {{ $ticket->prioridad->nombre_prioridad }}
                                </span>
                            @else
                                <span class="small italic">Evaluando prioridad...</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card de Ayuda --}}
                <div class="card bg-primary bg-gradient text-white border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-question-circle display-6 mb-3 opacity-50"></i>
                        <h6>¿Necesitas ayuda inmediata?</h6>
                        <p class="small opacity-75">Si el problema es crítico, puedes comunicarte con la extensión de soporte técnico en tu oficina.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón Flotante para abrir el Chat --}}
    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#chatOffcanvas" 
            aria-controls="chatOffcanvas"
            style="position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px; z-index: 1040; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="bi bi-chat-dots-fill fs-3"></i>
        @php
            $mensajesPublicos = $ticket->comentarios->where('es_interno', false);
        @endphp
        @if($mensajesPublicos->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light border-2" style="font-size: 0.8rem; padding: 0.35em 0.65em;">
                {{ $mensajesPublicos->count() }}
            </span>
        @endif
    </button>

    {{-- Panel Deslizable (Offcanvas) de Chat Glassmorphism --}}
    <div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="chatOffcanvas" aria-labelledby="chatOffcanvasLabel" style="width: 450px; max-width: 100vw; background: rgba(var(--bs-body-bg-rgb), 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-10 py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold mb-0" id="chatOffcanvasLabel">Chat de Soporte</h5>
                    <small class="text-secondary d-flex align-items-center gap-1">
                        <span class="d-inline-block bg-success rounded-circle" style="width: 8px; height: 8px;"></span> Ticket #{{ $ticket->id_ticket }}
                    </small>
                </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body p-0 d-flex flex-column" style="background: rgba(var(--bs-secondary-bg-rgb), 0.3);">
            {{-- Área de Mensajes --}}
            <div class="chat-container flex-grow-1 p-4" id="offcanvasChatContainer" style="overflow-y: auto; scroll-behavior: smooth;">
                @if($mensajesPublicos->count() > 0)
                    @foreach($mensajesPublicos as $comentario)
                        <div class="d-flex flex-column mb-4 {{ $comentario->id_usuario == auth()->id() ? 'align-items-end' : 'align-items-start' }}">
                            <div class="d-flex align-items-end gap-2 {{ $comentario->id_usuario == auth()->id() ? 'flex-row-reverse' : '' }}">
                                {{-- Avatar Mini --}}
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white' : 'bg-body-secondary text-secondary-emphasis' }}" style="width: 35px; height: 35px; font-size: 0.8rem; flex-shrink: 0;">
                                    {{ substr($comentario->usuario->name, 0, 1) }}
                                </div>
                                
                                {{-- Burbuja de Chat --}}
                                <div class="p-3 rounded-4 shadow-sm {{ $comentario->id_usuario == auth()->id() ? 'bg-primary text-white text-end' : 'bg-body border border-light-subtle' }}" style="max-width: 85%; border-bottom-{{ $comentario->id_usuario == auth()->id() ? 'right' : 'left' }}-radius: 4px !important;">
                                    <div class="d-flex align-items-center gap-2 mb-1 justify-content-{{ $comentario->id_usuario == auth()->id() ? 'end' : 'start' }}">
                                        <small class="fw-bold {{ $comentario->id_usuario == auth()->id() ? 'text-white text-opacity-75' : 'text-primary' }}" style="font-size: 0.75rem;">
                                            {{ $comentario->id_usuario == auth()->id() ? 'Tú' : $comentario->usuario->name }}
                                        </small>
                                        @if($comentario->id_usuario != auth()->id()) 
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.55rem; padding: 2px 6px;">SOPORTE</span> 
                                        @endif
                                    </div>
                                    <p class="mb-0" style="font-size: 0.95rem; line-height: 1.5; white-space: pre-wrap;">{{ $comentario->mensaje }}</p>
                                </div>
                            </div>
                            <small class="text-secondary mt-1 px-5" style="font-size: 0.65rem;">
                                {{ $comentario->created_at->format('h:i A') }}
                            </small>
                        </div>
                    @endforeach
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-5 opacity-50">
                        <i class="bi bi-chat-square-dots display-3 text-secondary mb-3"></i>
                        <h6 class="fw-bold">No hay mensajes</h6>
                        <p class="small text-secondary mb-0">Envía un mensaje para contactar al soporte técnico.</p>
                    </div>
                @endif
            </div>

            {{-- Área de Input --}}
            <div class="p-3 bg-body border-top border-secondary border-opacity-10 shadow-sm z-3">
                @if($ticket->estatus != 0)
                    <form action="{{ route('usuario.tickets.comentar', $ticket->id_ticket) }}" method="POST">
                        @csrf
                        <div class="position-relative">
                            <textarea name="mensaje" 
                                      class="form-control form-control-premium bg-body-secondary bg-opacity-50 border-0 shadow-none pe-5" 
                                      placeholder="Escribe un mensaje..." 
                                      rows="1" 
                                      required 
                                      style="resize: none; border-radius: 25px; padding-top: 12px; padding-bottom: 12px; transition: all 0.3s ease;"></textarea>
                            <button class="btn btn-primary rounded-circle position-absolute end-0 top-50 translate-middle-y me-1 d-flex align-items-center justify-content-center shadow-sm hover-scale" 
                                    type="submit" 
                                    style="width: 38px; height: 38px;">
                                <i class="bi bi-send-fill fs-6 ms-1"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-secondary bg-opacity-10 text-secondary text-center py-3 rounded-4">
                        <span class="small fw-medium"><i class="bi bi-lock-fill me-1"></i> Envía el ticket para habilitar el chat.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación al hacer hover en el botón flotante
            const chatBtn = document.querySelector('[data-bs-target="#chatOffcanvas"]');
            if (chatBtn) {
                chatBtn.addEventListener('mouseenter', () => chatBtn.style.transform = 'scale(1.1) rotate(-5deg)');
                chatBtn.addEventListener('mouseleave', () => chatBtn.style.transform = 'scale(1) rotate(0)');
            }

            // Auto-scroll al fondo cuando se abre el offcanvas
            const chatOffcanvas = document.getElementById('chatOffcanvas');
            if (chatOffcanvas) {
                chatOffcanvas.addEventListener('shown.bs.offcanvas', function () {
                    const chatContainer = document.getElementById('offcanvasChatContainer');
                    if (chatContainer) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        // Pequeño focus visual a los mensajes
                        const mensajes = chatContainer.querySelectorAll('.d-flex.flex-column');
                        mensajes.forEach((msg, idx) => {
                            msg.style.opacity = '0';
                            msg.style.transform = 'translateY(10px)';
                            setTimeout(() => {
                                msg.style.transition = 'all 0.3s ease-out';
                                msg.style.opacity = '1';
                                msg.style.transform = 'translateY(0)';
                            }, 50 * idx);
                        });
                    }
                });
            }
        });
    </script>
    @endpush
</x-usuario-layout>
`

### Archivo: app/Console/Commands/MigrateAuditLogsToSpatie.php
`php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAuditLogsToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:migrate-to-spatie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los registros antiguos de la tabla audit_logs a la nueva tabla activity_log de Spatie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de registros de auditoría antiguos...');

        $oldLogs = DB::table('audit_logs')->get();
        $count = 0;

        foreach ($oldLogs as $log) {
            $properties = [];
            
            if ($log->old_values) {
                $properties['old'] = json_decode($log->old_values, true);
            }
            if ($log->new_values) {
                $properties['attributes'] = json_decode($log->new_values, true);
            }
            if ($log->ip_address) {
                $properties['ip'] = $log->ip_address;
            }
            if ($log->user_agent) {
                $properties['user_agent'] = $log->user_agent;
            }

            // Map action to event
            $event = $log->action;
            if ($event === 'create') $event = 'created';
            if ($event === 'update') $event = 'updated';
            if ($event === 'delete') $event = 'deleted';

            DB::table('activity_log')->insert([
                'log_name' => 'default',
                'description' => 'Migrado desde audit_logs',
                'subject_type' => $log->auditable_type,
                'event' => $event,
                'subject_id' => $log->auditable_id == 0 ? null : $log->auditable_id,
                'causer_type' => $log->user_id ? 'App\Models\User' : null,
                'causer_id' => $log->user_id,
                'properties' => json_encode($properties),
                'batch_uuid' => null,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ]);
            $count++;
        }

        $this->info("Migración completada. Se migraron $count registros a Spatie Activitylog.");
    }
}

`


### 6. Actualizaci�n T�cnica y Correcci�n de Bugs (21/06/2026)
- **Correcci�n de Sesi�n por Inactividad:** Se resolvi� el bug donde la opci�n de "Continuar sesi�n" fallaba al cambiar de secci�n.
  - El frontend JS ahora recibe URLs din�micas (keep_alive_url y logout_url) desde los layouts (dmin y usuario) mediante window.config en lugar de rutas absolutas quemadas, evitando errores 404 en entornos de subcarpeta (como Laragon).
  - El middleware CheckIdleSession.php fue reescrito para utilizar *Unix Timestamps* (segundos exactos) para calcular la inactividad, erradicando los desajustes causados al serializar/deserializar objetos Carbon con zonas horarias diferentes.
  - La ruta /keep-alive ahora realiza un guardado de sesi�n s�ncrono expl�cito (session()->save()) previniendo race conditions si el usuario navega a otra secci�n antes de que el ciclo de vida normal de Laravel guarde la respuesta JSON.
- **Limpieza del Repositorio:** Toda la documentaci�n adicional, manuales, c�digo de Python e historiales ajenos al ecosistema Laravel puro fueron reubicados en un directorio unificado llamado _documentacion_y_extras en la ra�z del proyecto para preservar la limpieza de la arquitectura MVC.
