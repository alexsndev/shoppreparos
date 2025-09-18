<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('categoria')->orderBy('nome')->paginate(12);
        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();
        return view('produtos.create', compact('categorias'));
    }

    private function uploadImagem(Request $request, array &$data, ?Produto $produto = null): void
    {
        if (!$request->hasFile('imagem')) {
            return;
        }

        try {
            $file = $request->file('imagem');

            // Remove imagem antiga (considera formato antigo 'produtos/arquivo.ext' ou apenas 'arquivo.ext')
            if ($produto && $produto->imagem) {
                $old = str_contains($produto->imagem, '/') ? $produto->imagem : 'produtos/' . $produto->imagem;
                if (Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            $filename = uniqid('produto_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('produtos', $filename, 'public'); // salva em storage/app/public/produtos/<filename>
            $data['imagem'] = $filename; // apenas o nome no banco
        } catch (\Throwable $e) {
            Log::error('Erro upload imagem produto: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'preco' => 'nullable|numeric',
        ]);
        $data['slug'] = null;

        $this->uploadImagem($request, $data);

        try {
            Produto::create($data);
            return redirect()->route('admin.produtos.index')->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: '.$e->getMessage());
            return back()->withInput()->with('error', 'Erro ao cadastrar produto.');
        }
    }

    public function edit(Produto $produto)
    {
        $categorias = Categoria::orderBy('nome')->get();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, Produto $produto)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'preco' => 'nullable|numeric',
        ]);

        if ($data['nome'] !== $produto->nome) {
            $data['slug'] = null;
        }

        $this->uploadImagem($request, $data, $produto);

        try {
            $produto->update($data);
            return redirect()->route('admin.produtos.index')->with('success', 'Produto atualizado!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: '.$e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar produto.');
        }
    }

    public function show(Produto $produto)
    {
        return view('produtos.show', compact('produto'));
    }

    public function duplicate(Produto $produto)
    {
        $produtoDuplicado = $produto->replicate();
        $produtoDuplicado->nome = $produto->nome . ' (Cópia)';
        $produtoDuplicado->slug = null;

        if ($produto->imagem) {
            $orig = str_contains($produto->imagem, '/') ? $produto->imagem : 'produtos/' . $produto->imagem;
            if (Storage::disk('public')->exists($orig)) {
                try {
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $newFilename = 'produto_' . time() . '_' . Str::random(8) . '.' . $ext;
                    Storage::disk('public')->copy($orig, 'produtos/' . $newFilename);
                    $produtoDuplicado->imagem = $newFilename; // só nome
                } catch (\Throwable $e) {
                    Log::warning('Falha ao copiar imagem na duplicação: ' . $e->getMessage());
                }
            }
        }

        $produtoDuplicado->save();
        return redirect()->route('admin.produtos.index')->with('success', 'Produto duplicado com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        if ($produto->imagem) {
            $path = str_contains($produto->imagem, '/') ? $produto->imagem : 'produtos/' . $produto->imagem;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $produto->delete();
        return redirect()->route('admin.produtos.index')->with('success', 'Produto removido!');
    }
}
