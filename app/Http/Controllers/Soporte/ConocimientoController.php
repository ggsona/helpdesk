<?php

namespace App\Http\Controllers\Soporte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArticuloConocimiento;
use App\Models\Categoria;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConocimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = ArticuloConocimiento::with(['autor', 'categoria', 'tags', 'adjuntos']);

        if ($request->estado === 'archivados') {
            $query->where('estado', 'archivado');
        } else {
            $query->where(function($q) {
                $q->where('estado', 'publicado')
                  ->orWhere(function($subQ) {
                      $subQ->where('estado', 'borrador')
                           ->where('id_autor', Auth::id());
                  });
            });
        }

        // Búsqueda
        if ($request->has('q')) {
            $q = $request->q;
            $query->where(function($query) use ($q) {
                $query->where('titulo', 'LIKE', "%{$q}%")
                      ->orWhere('contenido', 'LIKE', "%{$q}%")
                      ->orWhere('extracto', 'LIKE', "%{$q}%");
            });
        }

        // Filtro por categoría
        if ($request->has('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Filtro por etiqueta (tag)
        if ($request->has('tag')) {
            $tagNombre = $request->tag;
            $query->whereHas('tags', function($q) use ($tagNombre) {
                $q->where('nombre', $tagNombre);
            });
        }

        $articulos = $query->orderBy('es_destacado', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(12);

        $categorias = Categoria::where('estado', 1)->get();
        $articulosDestacados = ArticuloConocimiento::where('es_destacado', true)->where('estado', 'publicado')->take(5)->get();

        return view('conocimiento.index', compact('articulos', 'categorias', 'articulosDestacados'));
    }

    public function show($slug)
    {
        $articulo = ArticuloConocimiento::with(['autor', 'categoria', 'tags', 'adjuntos', 'solucion.ticket'])
                        ->where('slug', $slug)
                        ->firstOrFail();

        // Validar permisos para ver borradores
        if ($articulo->estado === 'borrador' && $articulo->id_autor !== Auth::id()) {
            abort(403, 'No tienes permiso para ver este borrador. Solo su autor puede visualizarlo.');
        }

        // Incrementar vistas solo si no es el autor (opcional, pero buena práctica)
        if ($articulo->id_autor !== Auth::id()) {
            $articulo->increment('vistas');
        }

        // Artículos relacionados (misma categoría o tags)
        $relacionados = ArticuloConocimiento::where('id_categoria', $articulo->id_categoria)
                            ->where('id_articulo', '!=', $articulo->id_articulo)
                            ->where('estado', 'publicado')
                            ->take(3)
                            ->get();

        return view('conocimiento.show', compact('articulo', 'relacionados'));
    }

    public function create()
    {
        $categorias = Categoria::where('estado', 1)->get();
        $tags = Tag::where('estado', 1)->get();
        return view('conocimiento.create', compact('categorias', 'tags'));
    }

    public function store(Request $request)
    {
        $maxKb = env('KB_MAX_UPLOAD_KB', 1048576); // Por defecto 1 GB (1048576 KB)
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
            'estado' => 'required|in:borrador,publicado,archivado',
            'adjuntos.*' => "nullable|file|max:{$maxKb}|mimes:exe,msi,zip,rar,7z,bat,ps1,pdf,doc,docx,xlsx,iso,img,jpg,jpeg,png",
        ]);

        $articulo = ArticuloConocimiento::create([
            'origen' => 'manual',
            'titulo' => $request->titulo,
            'slug' => Str::slug($request->titulo) . '-' . uniqid(),
            'extracto' => $request->extracto ?? Str::limit(strip_tags($request->contenido), 200),
            'contenido' => $request->contenido,
            'id_categoria' => $request->id_categoria,
            'id_autor' => Auth::id(),
            'estado' => $request->estado,
            'es_destacado' => $request->has('es_destacado'),
            'es_interno' => $request->has('es_interno') ? true : false,
            'fecha_publicacion' => $request->estado == 'publicado' ? now() : null,
        ]);

        // Procesar tags
        if ($request->filled('tags')) {
            $tagIds = [];
            $tagsInput = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            foreach ($tagsInput as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['nombre' => $tagName, 'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))]
                    );
                    $tagIds[] = $tag->id;
                }
            }
            $articulo->tags()->sync($tagIds);
        }

        // Procesar archivos adjuntos
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                $path = $file->store('articulos/adjuntos', 'public');
                $articulo->adjuntos()->create([
                    'nombre_original' => $file->getClientOriginalName(),
                    'ruta_archivo' => $path,
                    'tipo_mime' => $file->getClientMimeType(),
                    'tamano' => $file->getSize(),
                    'subido_por' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('soporte.conocimiento.show', $articulo->slug)
                         ->with('success', 'Artículo guardado correctamente.');
    }

    public function edit($slug)
    {
        $articulo = ArticuloConocimiento::with(['tags', 'adjuntos'])->where('slug', $slug)->firstOrFail();
        $categorias = Categoria::where('estado', 1)->get();
        // Cargar tags activos + tags inactivos que ya estén asociados a este artículo
        $tags = Tag::where('estado', 1)->orWhereIn('id', $articulo->tags->pluck('id'))->get();
        return view('conocimiento.edit', compact('articulo', 'categorias', 'tags'));
    }

    public function update(Request $request, $slug)
    {
        $articulo = ArticuloConocimiento::where('slug', $slug)->firstOrFail();

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
            'estado' => 'required|in:borrador,publicado,archivado',
        ]);

        $articulo->update([
            'titulo' => $request->titulo,
            // 'slug' no lo cambiamos para no romper links
            'extracto' => $request->extracto ?? Str::limit(strip_tags($request->contenido), 200),
            'contenido' => $request->contenido,
            'id_categoria' => $request->id_categoria,
            'id_editor' => Auth::id(),
            'estado' => $request->estado,
            'es_destacado' => $request->has('es_destacado'),
            'es_interno' => $request->has('es_interno') ? true : false,
            'fecha_publicacion' => ($request->estado == 'publicado' && !$articulo->fecha_publicacion) ? now() : $articulo->fecha_publicacion,
        ]);

        // Sincronizar tags
        if ($request->has('tags')) {
            $tagIds = [];
            $tagsInput = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            foreach ($tagsInput as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['nombre' => $tagName, 'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))]
                    );
                    $tagIds[] = $tag->id;
                }
            }
            $articulo->tags()->sync($tagIds);
        } else {
            $articulo->tags()->sync([]);
        }

        // Nuevos adjuntos
        if ($request->hasFile('adjuntos')) {
            $maxKb = env('KB_MAX_UPLOAD_KB', 1048576); // Por defecto 1 GB
            $request->validate([
                'adjuntos.*' => "nullable|file|max:{$maxKb}|mimes:exe,msi,zip,rar,7z,bat,ps1,pdf,doc,docx,xlsx,iso,img,jpg,jpeg,png",
            ]);
            
            foreach ($request->file('adjuntos') as $file) {
                $path = $file->store('articulos/adjuntos', 'public');
                $articulo->adjuntos()->create([
                    'nombre_original' => $file->getClientOriginalName(),
                    'ruta_archivo' => $path,
                    'tipo_mime' => $file->getClientMimeType(),
                    'tamano' => $file->getSize(),
                    'subido_por' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('soporte.conocimiento.show', $articulo->slug)
                         ->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $articulo = ArticuloConocimiento::findOrFail($id);
        $articulo->delete();
        return redirect()->route('soporte.conocimiento.index')->with('success', 'Artículo eliminado.');
    }

    public function valorar(Request $request, $id)
    {
        $request->validate(['es_util' => 'required|boolean']);
        
        $articulo = ArticuloConocimiento::findOrFail($id);
        
        $articulo->valoraciones()->updateOrCreate(
            ['id_usuario' => Auth::id()],
            ['es_util' => $request->es_util, 'comentario' => $request->comentario]
        );

        return back()->with('success', 'Gracias por tu valoración.');
    }

    public function descargar($id)
    {
        $adjunto = \App\Models\ArticuloAdjunto::findOrFail($id);
        $adjunto->increment('descargas');
        return Storage::disk('public')->download($adjunto->ruta_archivo, $adjunto->nombre_original);
    }
}
