<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private function checkAdmin()
    {
        if (!Auth::check() || Auth::user()->perfil !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();
        
        $query = Post::query();

        // Filtros
        if ($request->has('busca') && $request->busca) {
            $query->where('title', 'LIKE', '%' . $request->busca . '%')
                  ->orWhere('content', 'LIKE', '%' . $request->busca . '%');
        }

        if ($request->has('categoria') && $request->categoria) {
            $query->where('category', $request->categoria);
        }

        if ($request->has('status')) {
            if ($request->status === 'publicado') {
                $query->where('published', true);
            } elseif ($request->status === 'rascunho') {
                $query->where('published', false);
            }
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $categorias = Post::select('category')
                         ->distinct()
                         ->pluck('category')
                         ->sort();

        return view('admin.blog.index', compact('posts', 'categorias'));
    }

    public function create()
    {
        $this->checkAdmin();
        
        $categorias = [
            'reparos-hidraulicos' => 'Reparos Hidráulicos',
            'eletrica' => 'Elétrica',
            'ferramentas' => 'Ferramentas',
            'pintura' => 'Pintura',
            'manutencao-geral' => 'Manutenção Geral'
        ];

        return view('admin.blog.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'meta_keywords' => 'nullable|string',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'published' => 'boolean'
        ]);

    // Capturar apenas os campos esperados (evita lixo do $request->all())
    $data = $request->only(['title','excerpt','content','category']);
        
        // Processar slug
        $data['slug'] = Str::slug($data['title']);
        
        // Verificar se slug já existe
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Post::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Processar meta dados
        $data['meta_title'] = $data['title'] . ' | Shopp Reparos';
        $data['meta_description'] = $data['excerpt'];
        
        if ($request->has('meta_keywords')) {
            $data['meta_keywords'] = $request->filled('meta_keywords')
                ? array_map('trim', explode(',', $request->meta_keywords))
                : null;
        }
        if ($request->has('tags')) {
            $data['tags'] = $request->filled('tags')
                ? array_map('trim', explode(',', $request->tags))
                : null;
        }

        // Upload da imagem padronizado no disco 'public' (storage/app/public/blog)
        if ($request->hasFile('featured_image')) {
            $imageFile = $request->file('featured_image');
            $imageName = 'post_' . time() . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();

            try {
                Storage::disk('public')->makeDirectory('blog');
                $stored = $imageFile->storeAs('blog', $imageName, 'public');
                if ($stored) {
                    $data['featured_image'] = 'blog/' . $imageName;
                } else {
                    throw new \RuntimeException('Falha ao salvar imagem no disco public');
                }
            } catch (\Throwable $e) {
                Log::error('Erro no upload de imagem do blog: ' . $e->getMessage());
                // Fallback para public/img/blog
                $destinationPath = public_path('img/blog');
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
                if ($imageFile->move($destinationPath, $imageName)) {
                    $data['featured_image'] = 'img/blog/' . $imageName;
                }
            }
        }

        // Definir autor
        $data['author_name'] = Auth::user()->name ?? 'Equipe Shopp Reparos';

        // Publicação: checkbox ou botão "Criar Post" (action=publish)
        $wantsPublish = $request->boolean('published') || $request->input('action') === 'publish';
        $data['published'] = $wantsPublish;
        if ($wantsPublish) {
            $data['published_at'] = now();
        }

        Post::create($data);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post criado com sucesso!');
    }

    public function show(Post $post)
    {
        $this->checkAdmin();
        return view('admin.blog.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->checkAdmin();
        
        $categorias = [
            'reparos-hidraulicos' => 'Reparos Hidráulicos',
            'eletrica' => 'Elétrica',
            'ferramentas' => 'Ferramentas',
            'pintura' => 'Pintura',
            'manutencao-geral' => 'Manutenção Geral'
        ];

        return view('admin.blog.edit', compact('post', 'categorias'));
    }

    public function update(Request $request, Post $post)
    {
        $this->checkAdmin();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'meta_keywords' => 'nullable|string',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'published' => 'boolean'
        ]);

    // Capturar apenas os campos previstos
    $data = $request->only(['title','excerpt','content','category']);

        // Atualizar slug se título mudou
        if ($data['title'] !== $post->title) {
            $data['slug'] = Str::slug($data['title']);
            
            // Verificar se novo slug já existe
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Post::where('slug', $data['slug'])->where('id', '!=', $post->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Processar meta dados
        $data['meta_title'] = $data['title'] . ' | Shopp Reparos';
        $data['meta_description'] = $data['excerpt'];
        
        if ($request->has('meta_keywords')) {
            $data['meta_keywords'] = $request->filled('meta_keywords')
                ? array_map('trim', explode(',', $request->meta_keywords))
                : null; // permitir limpar
        }
        if ($request->has('tags')) {
            $data['tags'] = $request->filled('tags')
                ? array_map('trim', explode(',', $request->tags))
                : null; // permitir limpar
        }

        // Upload da nova imagem padronizado no disco 'public'
        if ($request->hasFile('featured_image')) {
            // Deletar imagem antiga se existir
            if ($post->featured_image) {
                if (str_starts_with($post->featured_image, 'img/blog/')) {
                    if (file_exists(public_path($post->featured_image))) @unlink(public_path($post->featured_image));
                } elseif (str_starts_with($post->featured_image, 'blog/')) {
                    Storage::disk('public')->delete($post->featured_image);
                }
            }
            
            $imageFile = $request->file('featured_image');
            $imageName = 'post_' . time() . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();
            
            try {
                Storage::disk('public')->makeDirectory('blog');
                $stored = $imageFile->storeAs('blog', $imageName, 'public');
                if ($stored) {
                    $data['featured_image'] = 'blog/' . $imageName;
                } else {
                    throw new \RuntimeException('Falha ao salvar imagem no disco public');
                }
            } catch (\Throwable $e) {
                Log::error('Erro no upload de imagem do blog (update): ' . $e->getMessage());
                // Fallback: salvar diretamente na pasta public
                $destinationPath = public_path('img/blog');
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0755, true);
                if ($imageFile->move($destinationPath, $imageName)) {
                    $data['featured_image'] = 'img/blog/' . $imageName;
                }
            }
        }

        // Publicação (checkbox). Só alterar se o campo vier no request
        if ($request->has('published')) {
            $newPublished = $request->boolean('published');
            $data['published'] = $newPublished;
            if ($newPublished && !$post->published) {
                $data['published_at'] = now();
            } elseif (!$newPublished && $post->published) {
                $data['published_at'] = null;
            }
        }

        $post->update($data);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post atualizado com sucesso!');
    }

    public function destroy(Post $post)
    {
        $this->checkAdmin();
        
        // Deletar imagem se existir (compatibilidade com sistema antigo e novo)
        if ($post->featured_image) {
            if (str_starts_with($post->featured_image, 'img/blog/')) {
                // Sistema antigo - deletar de public/img/blog
                if (file_exists(public_path($post->featured_image))) {
                    unlink(public_path($post->featured_image));
                }
            } else {
                // Sistema novo - deletar de storage
                Storage::disk('public')->delete($post->featured_image);
            }
        }

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post excluído com sucesso!');
    }

    public function togglePublished(Post $post)
    {
        $this->checkAdmin();
        
        $post->update([
            'published' => !$post->published,
            'published_at' => !$post->published ? now() : null
        ]);

        $status = $post->published ? 'publicado' : 'despublicado';
        
        return redirect()
            ->back()
            ->with('success', "Post {$status} com sucesso!");
    }
}