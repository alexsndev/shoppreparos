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

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'preco' => 'nullable|numeric',
        ]);

        // Garantir que o slug seja gerado
        $data['slug'] = null; // Será gerado automaticamente pelo modelo

        if ($request->hasFile('imagem')) {
            $imagemFile = $request->file('imagem');
            $imagemName = 'produto_' . time() . '_' . Str::random(10) . '.' . $imagemFile->getClientOriginalExtension();
            $path = $imagemFile->storeAs('public/produtos', $imagemName);
            // Salva apenas o caminho relativo a partir de 'produtos/'
            $data['imagem'] = $imagemName;
        }

        try {
            $produto = Produto::create($data);
            return redirect()->route('admin.produtos.index')->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao cadastrar produto: ' . $e->getMessage());
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

        // Se o nome mudou, limpar o slug para que seja regenerado
        if ($data['nome'] !== $produto->nome) {
            $data['slug'] = null;
        }

        if ($request->hasFile('imagem')) {
            // Deletar imagem antiga se existir
            if ($produto->imagem) {
                Storage::delete('public/produtos/' . $produto->imagem);
            }
            $imagemFile = $request->file('imagem');
            $imagemName = 'produto_' . time() . '_' . Str::random(10) . '.' . $imagemFile->getClientOriginalExtension();
            $path = $imagemFile->storeAs('public/produtos', $imagemName);
            // Salva apenas o caminho relativo a partir de 'produtos/'
            $data['imagem'] = $imagemName;
        }
        
        try {
            $produto->update($data);
            return redirect()->route('admin.produtos.index')->with('success', 'Produto atualizado!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    public function show(Produto $produto)
    {
        return view('produtos.show', compact('produto'));
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Produto $produto)
    {
        // Criar uma cópia do produto
        $produtoDuplicado = $produto->replicate();
        $produtoDuplicado->nome = $produto->nome . ' (Cópia)';
        $produtoDuplicado->slug = null; // Será gerado automaticamente
        $produtoDuplicado->save();

        return redirect()->route('admin.produtos.index')->with('success', 'Produto duplicado com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('admin.produtos.index')->with('success', 'Produto removido!');
    }
}
        return redirect()->route('admin.produtos.index')->with('success', 'Produto removido!');
    }
}
