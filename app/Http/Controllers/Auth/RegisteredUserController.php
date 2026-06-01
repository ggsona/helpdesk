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
