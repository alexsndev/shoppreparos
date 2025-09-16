<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    private function checkAdmin()
    {
        if (!Auth::check() || Auth::user()->perfil !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        
        $banners = Banner::where('is_active', true)->orderBy('ordem')->get();
        $history = Banner::where('is_active', false)->orderBy('created_at', 'desc')->get();
        
        return view('banners.index', compact('banners', 'history'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $request->validate([
            'titulo' => 'required|string|max:255',
            'desktop_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'mobile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'ordem' => 'nullable|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $ordem = $request->ordem;
            if (!$ordem) {
                $ultimaOrdem = Banner::where('is_active', true)->max('ordem') ?? 0;
                $ordem = $ultimaOrdem + 1;
            }

            $banner = new Banner();
            $banner->titulo = $request->titulo;
            $banner->ordem = $ordem;
            $banner->is_active = true;

            // Upload otimizado da imagem desktop
            if ($request->hasFile('desktop_image')) {
                $desktopFile = $request->file('desktop_image');
                $desktopName = 'desktop_' . time() . '_' . Str::random(10) . '.' . $desktopFile->getClientOriginalExtension();
                
                // Usar Storage diretamente (mais eficiente)
                $desktopPath = $desktopFile->storeAs('public/banners/desktop', $desktopName);
                $banner->desktop_image = $desktopName;
                
                Log::info('Upload desktop concluído: ' . $desktopPath);
            }

            // Upload otimizado da imagem mobile
            if ($request->hasFile('mobile_image')) {
                $mobileFile = $request->file('mobile_image');
                $mobileName = 'mobile_' . time() . '_' . Str::random(10) . '.' . $mobileFile->getClientOriginalExtension();
                
                // Usar Storage diretamente (mais eficiente)
                $mobilePath = $mobileFile->storeAs('public/banners/mobile', $mobileName);
                $banner->mobile_image = $mobileName;
                
                Log::info('Upload mobile concluído: ' . $mobilePath);
            }

            $banner->save();
            
            DB::commit();

            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Banner criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao criar banner: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar banner: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Banner $banner)
    {
        $this->checkAdmin();
        
        try {
            Log::info('Iniciando upload de banner');
            Log::info('Request data:', $request->all());
            \Log::info('Files:', $request->allFiles());
            
            $request->validate([
                'titulo' => 'nullable|string|max:255',
                'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'ordem' => 'nullable|integer|min:1'
            ]);

            // Atualizar campos se fornecidos
            if ($request->filled('titulo')) {
                $banner->titulo = $request->titulo;
            }
            
            if ($request->filled('ordem')) {
                $banner->ordem = $request->ordem;
            }

            // Salvar imagens antigas no histórico
            if ($request->hasFile('desktop_image') && $banner->desktop_image) {
                $banner->desktop_old_image = $banner->desktop_image;
            }
            
            if ($request->hasFile('mobile_image') && $banner->mobile_image) {
                $banner->mobile_old_image = $banner->mobile_image;
            }

            // Upload da nova imagem desktop
            if ($request->hasFile('desktop_image')) {
                \Log::info('Processando upload desktop');
                $desktopFile = $request->file('desktop_image');
                $desktopName = 'desktop_' . time() . '_' . Str::random(10) . '.' . $desktopFile->getClientOriginalExtension();
                
                try {
                    // Usar método nativo para upload mais confiável
                    $destinationPath = storage_path('app/public/banners/desktop');
                    
                    // Garantir que o diretório existe
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    // Mover arquivo usando método nativo
                    if ($desktopFile->move($destinationPath, $desktopName)) {
                        \Log::info('Desktop image atualizada com sucesso: ' . $destinationPath . '/' . $desktopName);
                        
                        // Copiar também para public/storage para compatibilidade
                        $publicPath = public_path('storage/banners/desktop');
                        if (!file_exists($publicPath)) {
                            mkdir($publicPath, 0755, true);
                        }
                        copy($destinationPath . '/' . $desktopName, $publicPath . '/' . $desktopName);
                        
                        $banner->desktop_image = $desktopName;
                    } else {
                        throw new \Exception('Falha no upload da imagem desktop');
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro no upload desktop (update): ' . $e->getMessage());
                    throw $e;
                }
                
                // Mover imagem antiga para histórico se existir
                if ($banner->desktop_old_image) {
                    $oldPath = 'public/banners/desktop/' . $banner->desktop_old_image;
                    $newPath = 'public/banners/history/desktop/' . $banner->desktop_old_image;
                    
                    // Criar diretório de histórico se não existir
                    if (!Storage::exists('public/banners/history/desktop')) {
                        Storage::makeDirectory('public/banners/history/desktop');
                    }
                    
                    if (Storage::exists($oldPath)) {
                        Storage::move($oldPath, $newPath);
                    }
                }
            }

            // Upload da nova imagem mobile
            if ($request->hasFile('mobile_image')) {
                \Log::info('Processando upload mobile');
                $mobileFile = $request->file('mobile_image');
                $mobileName = 'mobile_' . time() . '_' . Str::random(10) . '.' . $mobileFile->getClientOriginalExtension();
                
                try {
                    // Usar método nativo para upload mais confiável
                    $destinationPath = storage_path('app/public/banners/mobile');
                    
                    // Garantir que o diretório existe
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    
                    // Mover arquivo usando método nativo
                    if ($mobileFile->move($destinationPath, $mobileName)) {
                        \Log::info('Mobile image atualizada com sucesso: ' . $destinationPath . '/' . $mobileName);
                        
                        // Copiar também para public/storage para compatibilidade
                        $publicPath = public_path('storage/banners/mobile');
                        if (!file_exists($publicPath)) {
                            mkdir($publicPath, 0755, true);
                        }
                        copy($destinationPath . '/' . $mobileName, $publicPath . '/' . $mobileName);
                        
                        $banner->mobile_image = $mobileName;
                    } else {
                        throw new \Exception('Falha no upload da imagem mobile');
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro no upload mobile (update): ' . $e->getMessage());
                    throw $e;
                }
                
                // Mover imagem antiga para histórico se existir
                if ($banner->mobile_old_image) {
                    $oldPath = 'public/banners/mobile/' . $banner->mobile_old_image;
                    $newPath = 'public/banners/history/mobile/' . $banner->mobile_old_image;
                    
                    // Criar diretório de histórico se não existir
                    if (!Storage::exists('public/banners/history/mobile')) {
                        Storage::makeDirectory('public/banners/history/mobile');
                    }
                    
                    if (Storage::exists($oldPath)) {
                        Storage::move($oldPath, $newPath);
                    }
                }
            }

            $banner->save();
            \Log::info('Banner salvo com sucesso');

            // Se for uma requisição AJAX, retornar JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Banner atualizado com sucesso!',
                    'banner' => $banner
                ]);
            }

            return redirect()->route('admin.banners.index')->with('success', 'Banners atualizados com sucesso!');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar banner: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar banner: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.banners.index')->with('error', 'Erro ao atualizar banner: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        if (auth()->user()->perfil !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }
        
        $oldBanner = Banner::findOrFail($id);
        $currentBanner = Banner::getActive();
        
        if (!$currentBanner) {
            $currentBanner = new Banner();
            $currentBanner->is_active = true;
        }

        // Restaurar imagem desktop
        if ($oldBanner->desktop_old_image) {
            $currentBanner->desktop_old_image = $currentBanner->desktop_image;
            $currentBanner->desktop_image = $oldBanner->desktop_old_image;
            
            // Mover arquivos
            $oldPath = 'public/banners/history/desktop/' . $oldBanner->desktop_old_image;
            $newPath = 'public/banners/desktop/' . $oldBanner->desktop_old_image;
            
            if (Storage::exists($oldPath)) {
                Storage::move($oldPath, $newPath);
            }
        }

        // Restaurar imagem mobile
        if ($oldBanner->mobile_old_image) {
            $currentBanner->mobile_old_image = $currentBanner->mobile_image;
            $currentBanner->mobile_image = $oldBanner->mobile_old_image;
            
            // Mover arquivos
            $oldPath = 'public/banners/history/mobile/' . $oldBanner->mobile_old_image;
            $newPath = 'public/banners/mobile/' . $oldBanner->mobile_old_image;
            
            if (Storage::exists($oldPath)) {
                Storage::move($oldPath, $newPath);
            }
        }

        $currentBanner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner restaurado com sucesso!');
    }

    public function gallery()
    {
        if (auth()->user()->perfil !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }
        
        $history = Banner::where('is_active', false)
            ->orWhere('desktop_old_image', '!=', null)
            ->orWhere('mobile_old_image', '!=', null)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('banners.gallery', compact('history'));
    }

    public function destroy(Banner $banner)
    {
        if (auth()->user()->perfil !== 'admin') {
            abort(403, 'Acesso não autorizado');
        }
        
        try {
            
            // Deletar arquivos físicos do storage
            if ($banner->desktop_image) {
                $desktopPath = 'public/banners/desktop/' . $banner->desktop_image;
                if (Storage::exists($desktopPath)) {
                    Storage::delete($desktopPath);
                }
                
                // Deletar também da pasta public
                $publicDesktopPath = public_path('storage/banners/desktop/' . $banner->desktop_image);
                if (file_exists($publicDesktopPath)) {
                    unlink($publicDesktopPath);
                }
            }
            
            if ($banner->mobile_image) {
                $mobilePath = 'public/banners/mobile/' . $banner->mobile_image;
                if (Storage::exists($mobilePath)) {
                    Storage::delete($mobilePath);
                }
                
                // Deletar também da pasta public
                $publicMobilePath = public_path('storage/banners/mobile/' . $banner->mobile_image);
                if (file_exists($publicMobilePath)) {
                    unlink($publicMobilePath);
                }
            }
            
            $banner->delete();
            
            return redirect()->route('admin.banners.index')->with('success', 'Banner deletado com sucesso!');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao deletar banner: ' . $e->getMessage());
            return redirect()->route('admin.banners.index')->with('error', 'Erro ao deletar banner: ' . $e->getMessage());
        }
    }
}
