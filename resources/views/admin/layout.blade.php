<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Shopp Reparos</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Responsivo para Admin -->
    <link href="{{ asset('css/admin-responsive.css') }}" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        .sidebar-link.active {
            background-color: #3b82f6;
            color: white;
        }
        
        /* Animações para o menu mobile */
        .sidebar-mobile {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            will-change: transform;
        }
        
        .sidebar-mobile.open {
            transform: translateX(0);
        }
        
        /* Overlay para fechar o menu */
        .sidebar-overlay {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
            pointer-events: none;
        }
        
        .sidebar-overlay.open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        /* Garantir que o menu mobile funcione em todos os dispositivos */
        @media (max-width: 1023px) {
            .sidebar-mobile {
                display: block !important;
            }
            
            /* Garantir que o botão hambúrguer seja visível */
            #mobile-menu-btn {
                display: block !important;
            }
            
            /* Garantir que o overlay seja visível quando necessário */
            .sidebar-overlay.open {
                display: block !important;
            }
        }
        
        /* Estilos adicionais para o menu mobile */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease-in-out;
        }
        
        .sidebar-link:hover {
            background-color: #f3f4f6;
            color: #111827;
        }
        
        .sidebar-link.active {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar Desktop (oculto em mobile) -->
        <div class="hidden lg:flex lg:w-64 bg-white shadow-lg flex-col">
            <div class="flex items-center justify-center h-16 border-b border-gray-200">
                <img src="{{ asset('img/logohorizontal.png') }}" alt="Shopp Reparos" class="h-8">
            </div>
            
            <nav class="mt-8 flex-1">
                <div class="px-4 mb-4">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Menu Principal</h3>
                </div>
                
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.posts.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                    <i class="fas fa-blog w-5 h-5 mr-3"></i>
                    Blog
                    <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                        {{ \App\Models\Post::count() }}
                    </span>
                </a>
                
                <a href="{{ route('admin.banners.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="fas fa-images w-5 h-5 mr-3"></i>
                    Banners
                </a>
                
                <a href="{{ route('admin.usuarios.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Usuários
                </a>

                <a href="{{ route('admin.servicos.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}">
                    <i class="fas fa-tools w-5 h-5 mr-3"></i>
                    Serviços
                </a>

                <a href="{{ route('admin.produtos.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.produtos.*') ? 'active' : '' }}">
                    <i class="fas fa-box w-5 h-5 mr-3"></i>
                    Produtos
                </a>

                <a href="{{ route('admin.categorias.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}">
                    <i class="fas fa-tags w-5 h-5 mr-3"></i>
                    Categorias
                </a>

                <a href="{{ route('admin.ordem_servicos.index') }}" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.ordem_servicos.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                    Ordens de Serviço
                </a>

                <div class="px-4 mt-8 mb-4">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Site</h3>
                </div>
                
                <a href="/blog" target="_blank" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-external-link-alt w-5 h-5 mr-3"></i>
                    Ver Blog
                </a>
                
                <a href="/" target="_blank" class="sidebar-link flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-home w-5 h-5 mr-3"></i>
                    Ver Site
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 lg:px-6 py-6">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex-1 text-center lg:text-left">
                        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="hidden sm:inline text-sm text-gray-600">Olá, {{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 px-3 py-1 rounded hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-1"></i>
                                <span class="hidden sm:inline">Sair</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 lg:p-6 pt-8">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" style="display: none;"></div>
    
    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="sidebar-mobile fixed top-0 left-0 h-full w-64 bg-white shadow-lg z-50 lg:hidden" style="display: none;">
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
            <img src="{{ asset('img/logohorizontal.png') }}" alt="Shopp Reparos" class="h-8">
            <button id="close-sidebar" class="p-2 text-gray-600 hover:text-gray-900">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <nav class="mt-8 px-4">
            <div class="mb-4">
                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Menu Principal</h3>
            </div>
            
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                Dashboard
            </a>
            
            <a href="{{ route('admin.posts.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                <i class="fas fa-blog w-5 h-5 mr-3"></i>
                Blog
                <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                    {{ \App\Models\Post::count() }}
                </span>
            </a>
            
            <a href="{{ route('admin.banners.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="fas fa-images w-5 h-5 mr-3"></i>
                Banners
            </a>
            
            <a href="{{ route('admin.usuarios.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                <i class="fas fa-users w-5 h-5 mr-3"></i>
                Usuários
            </a>

            <a href="{{ route('admin.servicos.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}">
                <i class="fas fa-tools w-5 h-5 mr-3"></i>
                Serviços
            </a>

            <a href="{{ route('admin.produtos.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.produtos.*') ? 'active' : '' }}">
                <i class="fas fa-box w-5 h-5 mr-3"></i>
                Produtos
            </a>

            <a href="{{ route('admin.categorias.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.categorias.*') ? 'active' : '' }}">
                <i class="fas fa-tags w-5 h-5 mr-3"></i>
                Categorias
            </a>

            <a href="{{ route('admin.ordem_servicos.index') }}" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2 {{ request()->routeIs('admin.ordem_servicos.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                Ordens de Serviço
            </a>

            <div class="mt-8 mb-4">
                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Site</h3>
            </div>
            
            <a href="/blog" target="_blank" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2">
                <i class="fas fa-external-link-alt w-5 h-5 mr-3"></i>
                Ver Blog
            </a>
            
            <a href="/" target="_blank" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mb-2">
                <i class="fas fa-home w-5 h-5 mr-3"></i>
                Ver Site
            </a>
        </nav>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebarBtn = document.getElementById('close-sidebar');
            
            // Verificar se os elementos existem
            if (!mobileMenuBtn || !mobileSidebar || !sidebarOverlay || !closeSidebarBtn) {
                console.error('Elementos do menu mobile não encontrados');
                return;
            }
            
            // Abrir sidebar
            function openSidebar() {
                console.log('Abrindo sidebar mobile');
                mobileSidebar.style.display = 'block';
                sidebarOverlay.style.display = 'block';
                
                // Pequeno delay para garantir que o display seja aplicado antes da animação
                setTimeout(() => {
                    mobileSidebar.classList.add('open');
                    sidebarOverlay.classList.add('open');
                }, 10);
                
                document.body.style.overflow = 'hidden';
            }
            
            // Fechar sidebar
            function closeSidebar() {
                console.log('Fechando sidebar mobile');
                mobileSidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
                
                // Aguardar a animação terminar antes de ocultar
                setTimeout(() => {
                    mobileSidebar.style.display = 'none';
                    sidebarOverlay.style.display = 'none';
                }, 300);
                
                document.body.style.overflow = '';
            }
            
            // Event listeners
            mobileMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSidebar();
            });
            
            closeSidebarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
            
            sidebarOverlay.addEventListener('click', function(e) {
                if (e.target === sidebarOverlay) {
                    closeSidebar();
                }
            });
            
            // Fechar ao redimensionar para desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });
            
            // Fechar ao pressionar ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
            
            // Debug: verificar se o JavaScript está funcionando
            console.log('Menu mobile inicializado');
            console.log('Botão hambúrguer:', mobileMenuBtn);
            console.log('Sidebar mobile:', mobileSidebar);
        });
    </script>
</body>
</html>