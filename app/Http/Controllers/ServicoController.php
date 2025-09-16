<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicos = Servico::orderBy('created_at', 'desc')->paginate(10);
        
        // Verificar se o usuário é admin
        $isAdmin = Auth::check() && Auth::user()->perfil === 'admin';
        
        // Se for admin, usar layout admin, senão usar layout comum
        $layout = $isAdmin ? 'admin.layout' : 'layouts.app';
        
        return view('servicos.index', compact('servicos', 'layout', 'isAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar se o usuário é admin
        $isAdmin = Auth::check() && Auth::user()->perfil === 'admin';
        
        // Se for admin, usar layout admin, senão usar layout comum
        $layout = $isAdmin ? 'admin.layout' : 'layouts.app';
        
        return view('servicos.create', compact('layout', 'isAdmin'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'marca' => 'required|in:Lorenzetti,Roca,Meber',
            'codigo_interno' => 'nullable|string|max:255',
            'imagem' => 'nullable|image|max:2048',
            'valor_estimado' => 'nullable|string|max:255',
            'prazo_medio' => 'nullable|string|max:255',
            'possui_garantia' => 'nullable|boolean',
            'info_tecnica' => 'nullable|string',
            'instrucoes_cliente' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('imagem')) {
            $imagemFile = $request->file('imagem');
            $imagemName = time() . '_' . Str::random(10) . '.' . $imagemFile->getClientOriginalExtension();
            
            // Salvar na pasta storage/app/public/servicos
            $destinationPath = storage_path('app/public/servicos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            if ($imagemFile->move($destinationPath, $imagemName)) {
                // Copiar também para public/storage/servicos
                $publicPath = public_path('storage/servicos');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                copy($destinationPath . '/' . $imagemName, $publicPath . '/' . $imagemName);
                
                $data['imagem'] = $imagemName;
            }
        }

        $data['possui_garantia'] = $request->has('possui_garantia') ? 1 : 0;
        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        Servico::create($data);

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Servico $servico)
    {
        // Verificar se o usuário é admin
        $isAdmin = Auth::check() && Auth::user()->perfil === 'admin';
        
        // Se for admin, usar layout admin, senão usar layout comum
        $layout = $isAdmin ? 'admin.layout' : 'layouts.app';
        
        return view('servicos.show', compact('servico', 'layout', 'isAdmin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Servico $servico)
    {
        // Verificar se o usuário é admin
        $isAdmin = Auth::check() && Auth::user()->perfil === 'admin';
        
        // Se for admin, usar layout admin, senão usar layout comum
        $layout = $isAdmin ? 'admin.layout' : 'layouts.app';
        
        return view('servicos.edit', compact('servico', 'layout', 'isAdmin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Servico $servico)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'marca' => 'required|in:Lorenzetti,Roca,Meber',
            'codigo_interno' => 'nullable|string|max:255',
            'imagem' => 'nullable|image|max:2048',
            'valor_estimado' => 'nullable|string|max:255',
            'prazo_medio' => 'nullable|string|max:255',
            'possui_garantia' => 'nullable|boolean',
            'info_tecnica' => 'nullable|string',
            'instrucoes_cliente' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('imagem')) {
            // Deletar imagem antiga se existir
            if ($servico->imagem) {
                $oldImagePath = storage_path('app/public/servicos/' . $servico->imagem);
                $oldPublicPath = public_path('storage/servicos/' . $servico->imagem);
                if (file_exists($oldImagePath)) unlink($oldImagePath);
                if (file_exists($oldPublicPath)) unlink($oldPublicPath);
            }
            
            $imagemFile = $request->file('imagem');
            $imagemName = time() . '_' . Str::random(10) . '.' . $imagemFile->getClientOriginalExtension();
            
            // Salvar na pasta storage/app/public/servicos
            $destinationPath = storage_path('app/public/servicos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            if ($imagemFile->move($destinationPath, $imagemName)) {
                // Copiar também para public/storage/servicos
                $publicPath = public_path('storage/servicos');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                copy($destinationPath . '/' . $imagemName, $publicPath . '/' . $imagemName);
                
                $data['imagem'] = $imagemName;
            }
        } else {
            // Mantém a imagem existente se não houver upload novo
            $data['imagem'] = $servico->imagem;
        }

        $data['possui_garantia'] = $request->has('possui_garantia') ? 1 : 0;
        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        $servico->update($data);

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Servico $servico)
    {
        // Criar uma cópia do serviço
        $servicoDuplicado = $servico->replicate();
        $servicoDuplicado->titulo = $servico->titulo . ' (Cópia)';
        $servicoDuplicado->codigo_interno = $servico->codigo_interno ? $servico->codigo_interno . '_copy' : null;
        $servicoDuplicado->ativo = false; // Por padrão, cópias ficam inativas
        $servicoDuplicado->slug = null; // Limpar o slug para que seja regenerado automaticamente
        $servicoDuplicado->save();

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço duplicado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servico $servico)
    {
        if ($servico->imagem && Storage::disk('public')->exists($servico->imagem)) {
            Storage::disk('public')->delete($servico->imagem);
        }

        $servico->delete();

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço excluído com sucesso!');
    }
}
