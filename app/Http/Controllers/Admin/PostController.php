<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->perfil !== 'admin') {
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

        $data = $request->all();
        
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
        
        if ($request->meta_keywords) {
            $data['meta_keywords'] = array_map('trim', explode(',', $request->meta_keywords));
        }
        
        if ($request->tags) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        // Upload da imagem usando Storage (padrão de produtos/serviços)
        if ($request->hasFile('featured_image')) {
            $imageFile = $request->file('featured_image');
            $imageName = 'post_' . time() . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();
            
            try {
                // Garantir que a pasta existe
                if (!Storage::disk('public')->exists('blog')) {
                    Storage::disk('public')->makeDirectory('blog');
                }
                
                // Salvar usando Storage (mais eficiente e confiável)
                $path = $imageFile->storeAs('public/blog', $imageName);
                
                // Verificar se o arquivo foi salvo
                if ($path && Storage::disk('public')->exists('blog/' . $imageName)) {
                    $data['featured_image'] = 'blog/' . $imageName;
                } else {
                    // Fallback: salvar diretamente na pasta public
                    $destinationPath = public_path('img/blog');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    if ($imageFile->move($destinationPath, $imageName)) {
                        $data['featured_image'] = 'img/blog/' . $imageName;
                    }
                }
            } catch (\Exception $e) {
                // Log do erro para debug
                Log::error('Erro no upload de imagem do blog: ' . $e->getMessage());
                
                // Fallback: salvar diretamente na pasta public
                $destinationPath = public_path('img/blog');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                if ($imageFile->move($destinationPath, $imageName)) {
                    $data['featured_image'] = 'img/blog/' . $imageName;
                }
            }
        }

        // Definir autor
        $data['author_name'] = auth()->user()->name ?? 'Equipe Shopp Reparos';
        
        // Data de publicação
        if ($data['published']) {
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

        $data = $request->all();

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
        
        if ($request->meta_keywords) {
            $data['meta_keywords'] = array_map('trim', explode(',', $request->meta_keywords));
        } else {
            $data['meta_keywords'] = null;
        }
        
        if ($request->tags) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        } else {
            $data['tags'] = null;
        }

        // Upload da nova imagem usando Storage (padrão de produtos/serviços)
        if ($request->hasFile('featured_image')) {
            // Deletar imagem antiga se existir
            if ($post->featured_image && $post->featured_image !== 'img/blog/') {
                // Verificar se é do sistema antigo ou novo
                if (str_starts_with($post->featured_image, 'img/blog/')) {
                    // Sistema antigo - deletar de public/img/blog
                    if (file_exists(public_path($post->featured_image))) {
                        unlink(public_path($post->featured_image));
                    }
                } else {
                    // Sistema novo - deletar de storage
                    Storage::delete('public/' . $post->featured_image);
                }
            }
            
            $imageFile = $request->file('featured_image');
            $imageName = 'post_' . time() . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();
            
            try {
                // Garantir que a pasta existe
                if (!Storage::disk('public')->exists('blog')) {
                    Storage::disk('public')->makeDirectory('blog');
                }
                
                // Salvar usando Storage (mais eficiente e confiável)
                $path = $imageFile->storeAs('public/blog', $imageName);
                
                // Verificar se o arquivo foi salvo
                if ($path && Storage::disk('public')->exists('blog/' . $imageName)) {
                    $data['featured_image'] = 'blog/' . $imageName;
                } else {
                    // Fallback: salvar diretamente na pasta public
                    $destinationPath = public_path('img/blog');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    if ($imageFile->move($destinationPath, $imageName)) {
                        $data['featured_image'] = 'img/blog/' . $imageName;
                    }
                }
            } catch (\Exception $e) {
                // Log do erro para debug
                Log::error('Erro no upload de imagem do blog (update): ' . $e->getMessage());
                
                // Fallback: salvar diretamente na pasta public
                $destinationPath = public_path('img/blog');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                if ($imageFile->move($destinationPath, $imageName)) {
                    $data['featured_image'] = 'img/blog/' . $imageName;
                }
            }
        }

        // Data de publicação
        if ($data['published'] && !$post->published) {
            $data['published_at'] = now();
        } elseif (!$data['published']) {
            $data['published_at'] = null;
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
                Storage::delete('public/' . $post->featured_image);
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