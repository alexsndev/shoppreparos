@extends('admin.layout')

@section('title', 'Gerenciar Banners')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gerenciar Banners</h1>
            <p class="text-gray-600 mt-2">Gerencie os banners do site principal</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.banners.gallery') }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Galeria de Histórico
            </a>
        </div>
    </div>
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Formulário para Criar Novo Banner -->
            <div class="bg-white p-6 rounded-lg shadow border mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Criar Novo Banner</h3>
                
                <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título do Banner</label>
                            <input type="text" name="titulo" id="titulo" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Banner Principal">
                        </div>
                        
                        <div>
                            <label for="ordem" class="block text-sm font-medium text-gray-700 mb-2">Ordem de Exibição (Opcional)</label>
                            <input type="number" name="ordem" id="ordem" min="1" placeholder="Auto (próximo número)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Deixe vazio para adicionar automaticamente no final</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Banner Desktop -->
                        <div>
                            <label for="desktop_image" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-desktop mr-2"></i>Imagem Desktop (Obrigatória)
                            </label>
                            <input type="file" name="desktop_image" id="desktop_image" required
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Máximo 5MB - JPEG, PNG, JPG, GIF, WEBP</p>
                        </div>

                        <!-- Banner Mobile -->
                        <div>
                            <label for="mobile_image" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-mobile-alt mr-2"></i>Imagem Mobile (Obrigatória)
                            </label>
                            <input type="file" name="mobile_image" id="mobile_image" required
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Máximo 5MB - JPEG, PNG, JPG, GIF, WEBP</p>
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Criar Banner
                    </button>
                </form>
            </div>

            <!-- Lista de Banners Existentes -->
            <div class="bg-white p-6 rounded-lg shadow border">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Banners Existentes</h3>
                
                @if($banners->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($banners as $banner)
                            <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $banner->titulo ?? 'Banner #' . $banner->id }}</h4>
                                        <p class="text-sm text-gray-500">Ordem: {{ $banner->ordem }}</p>
                                    </div>
                                    

                                </div>

                                <!-- Preview das Imagens -->
                                <div class="space-y-3">
                                    @if($banner->desktop_image)
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Desktop:</p>
                                            <img src="{{ $banner->desktop_image_path }}" alt="Desktop" 
                                                class="w-full h-24 object-cover rounded border">
                                        </div>
                                    @endif

                                    @if($banner->mobile_image)
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Mobile:</p>
                                            <img src="{{ $banner->mobile_image_path }}" alt="Mobile" 
                                                class="w-full h-24 object-cover rounded border">
                                        </div>
                                    @endif
                                </div>

                                <!-- Form de Edição Completa -->
                                <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="space-y-2">
                                        <input type="text" name="titulo" value="{{ $banner->titulo }}" 
                                            placeholder="Título do banner"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                        
                                        <input type="number" name="ordem" value="{{ $banner->ordem }}" min="1"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                        
                                        <!-- Substituir Imagem Desktop -->
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Nova Imagem Desktop (opcional):</label>
                                            <input type="file" name="desktop_image" 
                                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                        </div>
                                        
                                        <!-- Substituir Imagem Mobile -->
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Nova Imagem Mobile (opcional):</label>
                                            <input type="file" name="mobile_image" 
                                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-3">
                                        <button type="submit" class="flex-1 bg-green-600 text-white px-3 py-1 text-sm rounded hover:bg-green-700">
                                            <i class="fas fa-save mr-1"></i>Atualizar
                                        </button>
                                        
                                        <button type="button" onclick="deleteBanner({{ $banner->id }})" 
                                            class="bg-red-600 text-white px-3 py-1 text-sm rounded hover:bg-red-700" 
                                            title="Deletar banner">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-images text-4xl mb-4"></i>
                        <p>Nenhum banner encontrado. Crie seu primeiro banner usando o formulário acima.</p>
                    </div>
                @endif
            </div>
    </div>
</div>

<!-- Scripts para melhorar UX -->
<script>
    // Preview de imagens antes do upload
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('desktop_image').addEventListener('change', function() {
        previewImage(this, 'desktop_preview');
    });

    document.getElementById('mobile_image').addEventListener('change', function() {
        previewImage(this, 'mobile_preview');
    });
    
    // Função para deletar banner
    function deleteBanner(bannerId) {
        if (confirm('Tem certeza que deseja deletar este banner? Esta ação não pode ser desfeita.')) {
            // Criar formulário dinamicamente para enviar DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            // Usar prefixo /admin correto da rota: Route::delete('banners/{banner}', ...)->prefix('admin')
            const deleteBaseUrl = "{{ url('/admin/banners') }}";
            form.action = `${deleteBaseUrl}/${bannerId}`;
            
            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Method DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection