<?php

namespace App\Http\Controllers\Soporte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('articulos')->orderBy('nombre', 'asc')->get();
        return view('conocimiento.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:tags,nombre',
            'color' => 'nullable|string|max:10'
        ]);

        Tag::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'color' => $request->color ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF))
        ]);

        return redirect()->back()->with('success', 'Etiqueta creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:50|unique:tags,nombre,'.$id,
            'color' => 'required|string|max:10'
        ]);

        $tag->update([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'color' => $request->color
        ]);

        return redirect()->back()->with('success', 'Etiqueta actualizada.');
    }

    public function toggle($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->update(['estado' => !$tag->estado]);
        
        $status = $tag->estado ? 'activada' : 'desactivada';
        return redirect()->back()->with('success', "Etiqueta $status correctamente.");
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        if ($tag->articulos()->count() > 0) {
            // Si tiene artículos, solo la desactivamos en lugar de eliminarla
            $tag->update(['estado' => false]);
            return redirect()->back()->with('success', 'La etiqueta tenía artículos asignados, por lo que fue desactivada en lugar de eliminada.');
        }
        $tag->delete();
        return redirect()->back()->with('success', 'Etiqueta eliminada permanentemente.');
    }
}
