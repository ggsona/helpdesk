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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $fillable = ['nombre', 'apellido', 'telefono', 'id_oficina'];

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'id_oficina');
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
```

---

## 6. SISTEMA DE ENRUTAMIENTO Y MIDDLEWARE

El archivo `routes/web.php` establece las rutas agrupadas y protegidas por autenticación y asignación de roles.

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Usuario\TicketUsuarioController; 
use App\Http\Controllers\Gestor\TicketGestorController;
use App\Http\Controllers\Tecnico\TicketTecnicoController;

// Página de bienvenida pública
Route::get('/', function () {
    return view('welcome');
});

// Enrutador inteligente de ingreso
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rutas de Perfil (Compartidas para usuarios logueados)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- RUTAS DE USUARIO CLIENTE (Rol: usuario) ---
Route::middleware(['auth', 'role:usuario'])->group(function () {
    Route::get('/usuario/dashboard', [TicketUsuarioController::class, 'home'])->name('usuario.home');
    
    // CRUD y Acciones de Ticket del Cliente
    Route::get('/mis-tickets', [TicketUsuarioController::class, 'index'])->name('usuario.tickets.index');
    Route::get('/mis-tickets/nuevo', [TicketUsuarioController::class, 'create'])->name('usuario.tickets.create');
    Route::post('/mis-tickets', [TicketUsuarioController::class, 'store'])->name('usuario.tickets.store');
    Route::get('/mis-tickets/{ticket}', [TicketUsuarioController::class, 'show'])->name('usuario.tickets.show');
    Route::get('/mis-tickets/{ticket}/editar', [TicketUsuarioController::class, 'edit'])->name('usuario.tickets.edit');
    Route::put('/tickets/{id}', [TicketUsuarioController::class, 'update'])->name('usuario.tickets.update');
    Route::delete('/tickets/{id}', [TicketUsuarioController::class, 'destroy'])->name('usuario.tickets.destroy');
    
    // Enviar borrador a soporte y chatear
    Route::post('/mis-tickets/{ticket}/enviar', [TicketUsuarioController::class, 'enviar'])->name('usuario.tickets.enviar');
    Route::post('/tickets/{id}/comentar', [TicketUsuarioController::class, 'storeComentario'])->name('usuario.tickets.comentar');
});

// --- RUTAS DE GESTOR Y ADMIN (Acceso Operativo Compartido) ---
Route::middleware(['auth', 'role:gestor|admin'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('/tickets', [TicketGestorController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{id}/asignar', [TicketGestorController::class, 'asignar'])->name('tickets.asignar');
    Route::get('/tickets/{id}', [TicketGestorController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/comentar', [TicketGestorController::class, 'comentar'])->name('tickets.comentar');
});

// --- RUTAS DE TECNICO ESPECIALISTA (Rol: tecnico) ---
Route::middleware(['auth', 'role:tecnico'])->prefix('tecnico')->name('tecnico.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tickets', [TicketTecnicoController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{id}', [TicketTecnicoController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/comentar', [TicketTecnicoController::class, 'comentar'])->name('tickets.comentar');
    
    // Resolver y gestionar soluciones
    Route::get('/tickets/{id}/resolver', [TicketTecnicoController::class, 'crearSolucion'])->name('tickets.resolver');
    Route::post('/tickets/{id}/guardar-solucion', [TicketTecnicoController::class, 'guardarSolucion'])->name('tickets.guardar-solucion');
    Route::get('/tickets/{id}/editar-solucion', [TicketTecnicoController::class, 'editarSolucion'])->name('tickets.editar-solucion');
    Route::put('/tickets/{id}/actualizar-solucion', [TicketTecnicoController::class, 'actualizarSolucion'])->name('tickets.actualizar-solucion');
});

// Rutas de autenticación por defecto (Laravel Breeze)
require __DIR__.'/auth.php';
```

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
