@extends('layouts.site')

@section('title', $post->meta_title ?: $post->title . ' | Blog Shopp Reparos')
@section('description', $post->meta_description ?: $post->excerpt)
@section('keywords', $post->meta_keywords_string)

@push('meta')
<!-- Open Graph / Facebook -->
<meta property="og:type" content="article">
<meta property="og:url" content="{{ $post->canonical_url }}">
<meta property="og:title" content="{{ $post->title }}">
<meta property="og:description" content="{{ $post->meta_description ?: $post->excerpt }}">
@if($post->featured_image && \App\Helpers\BlogHelper::imageExists($post->featured_image))
<meta property="og:image" content="{{ \App\Helpers\BlogHelper::getImageUrl($post->featured_image) }}">
@endif
<meta property="article:published_time" content="{{ $post->published_at->toISOString() }}">
<meta property="article:modified_time" content="{{ $post->updated_at->toISOString() }}">
<meta property="article:author" content="{{ $post->author_name }}">
<meta property="article:section" content="{{ $post->category }}">
@if($post->tags)
@foreach($post->tags as $tag)
<meta property="article:tag" content="{{ $tag }}">
@endforeach
@endif

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $post->canonical_url }}">
<meta name="twitter:title" content="{{ $post->title }}">
<meta name="twitter:description" content="{{ $post->meta_description ?: $post->excerpt }}">
@if($post->featured_image)
<meta name="twitter:image" content="{{ \App\Helpers\BlogHelper::getImageUrl($post->featured_image) }}">
@endif

<!-- Canonical URL -->
<link rel="canonical" href="{{ $post->canonical_url }}">

<!-- Structured Data -->
<script type="application/ld+json">
{!! json_encode($post->structured_data) !!}
</script>
@endpush

@push('styles')
<style>
/* Reading Progress */
.reading-progress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 4px;
    background: linear-gradient(90deg, #1e40af, #fbbf24);
    z-index: 1000;
    transition: width 0.1s ease;
}

/* Post Hero */
.post-hero {
    position: relative;
    min-height: 45vh;
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #fbbf24 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0;
    overflow: hidden;
}

.post-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    opacity: 0.3;
}

.post-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
    filter: blur(1px);
}

.post-hero-content {
    position: relative;
    z-index: 2;
    color: white;
    text-align: center;
    max-width: 900px;
    padding: 0 2rem;
}

.floating-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 1;
}

.particle {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float-particle 8s ease-in-out infinite;
}

.particle:nth-child(1) { width: 6px; height: 6px; top: 20%; left: 10%; animation-delay: 0s; }
.particle:nth-child(2) { width: 8px; height: 8px; top: 60%; right: 15%; animation-delay: 2s; }
.particle:nth-child(3) { width: 4px; height: 4px; top: 80%; left: 20%; animation-delay: 4s; }
.particle:nth-child(4) { width: 10px; height: 10px; top: 40%; right: 30%; animation-delay: 6s; }

@keyframes float-particle {
    0%, 100% { transform: translateY(0px) translateX(0px); opacity: 0.3; }
    25% { transform: translateY(-20px) translateX(10px); opacity: 1; }
    50% { transform: translateY(-10px) translateX(-5px); opacity: 0.7; }
    75% { transform: translateY(-30px) translateX(15px); opacity: 0.5; }
}

/* Breadcrumb */
.breadcrumb {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 1.5rem 0;
    margin-bottom: 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.breadcrumb nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.breadcrumb a {
    transition: all 0.2s ease;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.breadcrumb a:hover {
    background: rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

/* Article Content */
.article-container {
    background: white;
    border-radius: 24px 24px 0 0;
    margin-top: -2rem;
    position: relative;
    z-index: 10;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
}

.post-content {
    font-size: 1.2rem;
    line-height: 1.8;
    color: #2d3748;
    font-family: 'Inter', system-ui, sans-serif;
}

.post-content h2 {
    font-size: 2.25rem;
    font-weight: 800;
    margin: 3rem 0 1.5rem 0;
    color: #1a202c;
    font-family: 'Poppins', sans-serif;
    position: relative;
    padding-left: 1rem;
}

.post-content h2::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #1e40af, #fbbf24);
    border-radius: 2px;
}

.post-content h3 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 2.5rem 0 1rem 0;
    color: #2d3748;
    font-family: 'Poppins', sans-serif;
}

.post-content h4 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 2rem 0 1rem 0;
    color: #2d3748;
}

.post-content p {
    margin-bottom: 1.75rem;
    text-align: justify;
}

.post-content ul, .post-content ol {
    margin: 2rem 0;
    padding-left: 2rem;
}

.post-content li {
    margin-bottom: 0.75rem;
    line-height: 1.7;
}

.post-content ul li::marker {
    content: '✦ ';
    color: #3b82f6;
}

.post-content blockquote {
    border-left: 6px solid #3b82f6;
    padding: 2rem;
    margin: 3rem 0;
    font-style: italic;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 0 16px 16px 0;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.post-content blockquote::before {
    content: '"';
    position: absolute;
    top: -0.5rem;
    left: 1rem;
    font-size: 4rem;
    color: #3b82f6;
    opacity: 0.3;
    font-family: serif;
}

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 16px;
    margin: 3rem 0;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.post-content img:hover {
    transform: scale(1.02);
}

.post-content a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
}

.post-content a:hover {
    border-bottom-color: #3b82f6;
    color: #1d4ed8;
}

.post-content code {
    background: #f7fafc;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.9em;
    color: #e53e3e;
    border: 1px solid #e2e8f0;
}

.post-content pre {
    background: #1a202c;
    color: #e2e8f0;
    padding: 2rem;
    border-radius: 12px;
    overflow-x: auto;
    margin: 2rem 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.post-content pre code {
    background: transparent;
    border: none;
    padding: 0;
    color: inherit;
}

/* Social Share */
.social-share {
    position: fixed;
    left: 2rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    z-index: 100;
}

.share-btn {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
}

.share-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.share-btn:hover {
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
}

.share-btn:hover::before {
    left: 100%;
}

/* Tags */
.tags-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 20px;
    padding: 2rem;
    margin: 3rem 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.tag {
    background: linear-gradient(135deg, #1e40af, #fbbf24);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.tag:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
}

/* Navigation Posts */
.navigation-posts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin: 4rem 0;
}

.nav-post {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.nav-post::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1e40af, #fbbf24);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.nav-post:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.nav-post:hover::before {
    transform: scaleX(1);
}

/* Related Posts */
.related-posts {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.9));
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 3rem;
    margin-top: 4rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
}

.related-post-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.related-post-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.related-post-image {
    height: 160px;
    overflow: hidden;
    position: relative;
}

.related-post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
    margin: 0;
    border-radius: 0;
    box-shadow: none;
}

.related-post-card:hover .related-post-image img {
    transform: scale(1.1);
}

.meta-info {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.author-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.author-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #1e40af, #fbbf24);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

@media (max-width: 1024px) {
    .social-share {
        position: static;
        transform: none;
        flex-direction: row;
        justify-content: center;
        margin: 3rem 0;
        gap: 1rem;
    }
    
    .share-btn {
        position: relative;
    }
}

@media (max-width: 768px) {
    .navigation-posts {
        grid-template-columns: 1fr;
    }
    
    .post-hero {
        min-height: 50vh;
    }
    
    .post-content {
        font-size: 1.1rem;
    }
    
    .post-content h2 {
        font-size: 1.875rem;
    }
    
    .post-content h3 {
        font-size: 1.5rem;
    }
}

/* Scroll indicator */
.scroll-indicator {
    position: fixed;
    top: 50%;
    right: 2rem;
    transform: translateY(-50%);
    width: 4px;
    height: 200px;
    background: rgba(59, 130, 246, 0.2);
    border-radius: 2px;
    z-index: 100;
}

.scroll-indicator-progress {
    width: 100%;
    background: linear-gradient(180deg, #1e40af, #fbbf24);
    border-radius: 2px;
    transition: height 0.1s ease;
    height: 0%;
}

@media (max-width: 1024px) {
    .scroll-indicator {
        display: none;
    }
}
</style>
@endpush

@section('content')
<!-- Reading Progress Bar -->
<div class="reading-progress"></div>

<!-- Scroll Indicator -->
<div class="scroll-indicator">
    <div class="scroll-indicator-progress"></div>
</div>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container mx-auto px-4">
        <nav class="text-sm font-medium">
            <a href="{{ route('home') }}" class="text-primary-600 hover:text-primary-800">
                <i class="fas fa-home mr-1"></i>Home
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('blog.index') }}" class="text-primary-600 hover:text-primary-800">
                <i class="fas fa-blog mr-1"></i>Blog
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('blog.categoria', $post->category) }}" class="text-primary-600 hover:text-primary-800">
                <i class="fas fa-tag mr-1"></i>{{ ucfirst($post->category) }}
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-700 font-semibold">{{ Str::limit($post->title, 50) }}</span>
        </nav>
    </div>
</div>

<!-- Hero do Post -->
<div class="post-hero">
    <!-- Floating Particles -->
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    @if($post->featured_image && \App\Helpers\BlogHelper::imageExists($post->featured_image))
        <img src="{{ \App\Helpers\BlogHelper::getImageUrl($post->featured_image) }}" alt="{{ $post->title }}" class="post-hero-bg">
    @endif
    
    <div class="post-hero-content">
        <div class="animate-fade-in">
            <!-- Category Badge -->
            <div class="inline-block bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-6 py-3 mb-6">
                <span class="text-sm font-semibold uppercase tracking-wider flex items-center">
                    <i class="fas fa-tag mr-2"></i>
                    {{ ucfirst($post->category) }}
                </span>
            </div>
            
            <!-- Title -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-black mb-6 leading-tight">
                {{ $post->title }}
            </h1>
            
            <!-- Excerpt -->
            @if($post->excerpt)
            <p class="text-xl md:text-2xl opacity-90 max-w-4xl mx-auto mb-8 leading-relaxed">
                {{ $post->excerpt }}
            </p>
            @endif
            
            <!-- Meta Information -->
            <div class="flex flex-wrap items-center justify-center gap-6 text-sm mb-8">
                <div class="flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2">
                    <i class="fas fa-user-circle mr-2 text-lg"></i>
                    <span class="font-medium">{{ $post->author_name }}</span>
                </div>
                <div class="flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2">
                    <i class="fas fa-calendar mr-2"></i>
                    <span>{{ $post->formatted_published_at }}</span>
                </div>
                <div class="flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2">
                    <i class="fas fa-clock mr-2"></i>
                    <span>{{ $post->reading_time_text }}</span>
                </div>
                <div class="flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2">
                    <i class="fas fa-eye mr-2"></i>
                    <span>{{ $post->views }} visualizações</span>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex flex-wrap justify-center gap-4">
                <button onclick="scrollToContent()" class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 text-white font-semibold px-6 py-3 rounded-full transition-all duration-300 hover:scale-105">
                    <i class="fas fa-arrow-down mr-2"></i>
                    Começar Leitura
                </button>
                <button onclick="shareArticle()" class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-30 text-white font-semibold px-6 py-3 rounded-full transition-all duration-300 hover:scale-105">
                    <i class="fas fa-share-alt mr-2"></i>
                    Compartilhar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Article Container -->
<div class="article-container">
    <div class="container mx-auto px-4 py-12">
        <!-- Author Info -->
        <div class="author-info">
            <div class="author-avatar">
                {{ strtoupper(substr($post->author_name, 0, 2)) }}
            </div>
            <div>
                <h3 class="font-semibold text-lg text-gray-800">{{ $post->author_name }}</h3>
                <p class="text-gray-600">Especialista em Reparos • Shopp Reparos</p>
                <p class="text-sm text-gray-500">
                    Publicado em {{ $post->formatted_published_at }} • Atualizado em {{ $post->updated_at->format('d/m/Y') }}
                </p>
            </div>
        </div>
        
        <!-- Meta Info -->
        <div class="meta-info">
            <div class="grid grid-cols-4 gap-3 text-center">
                <div>
                    <div class="text-lg font-bold text-primary-600">{{ $post->views }}</div>
                    <div class="text-xs text-gray-600">👁️ Views</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-primary-600">{{ $post->reading_time }}</div>
                    <div class="text-xs text-gray-600">🕰️ Min</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-primary-600">{{ str_word_count(strip_tags($post->content)) }}</div>
                    <div class="text-xs text-gray-600">📝 Palavras</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-primary-600">{{ $post->category }}</div>
                    <div class="text-xs text-gray-600">🏷️ Categoria</div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Compartilhamento Social (Desktop) -->
            <div class="hidden lg:block lg:w-20">
                <div class="social-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->share_url) }}" 
                       class="share-btn" style="background: #1877f2;" target="_blank" rel="noopener" title="Compartilhar no Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($post->share_url) }}&text={{ urlencode($post->title) }}" 
                       class="share-btn" style="background: #1da1f2;" target="_blank" rel="noopener" title="Compartilhar no Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - ' . $post->share_url) }}" 
                       class="share-btn" style="background: #25d366;" target="_blank" rel="noopener" title="Compartilhar no WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($post->share_url) }}" 
                       class="share-btn" style="background: #0077b5;" target="_blank" rel="noopener" title="Compartilhar no LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    
                    <button onclick="copyToClipboard('{{ $post->share_url }}')" 
                            class="share-btn" style="background: #6b7280;" title="Copiar Link">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="flex-1 max-w-4xl">
                <article id="article-content" class="post-content">
                    {!! $post->content !!}
                </article>

                <!-- Tags -->
                @if($post->tags)
                <div class="tags-section">
                    <div class="flex items-center mb-6">
                        <div class="w-1 h-8 bg-gradient-to-b from-blue-400 to-purple-500 rounded-full mr-4"></div>
                        <h3 class="text-2xl font-display font-bold text-gray-800">🏷️ Tags do Artigo</h3>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach($post->tags as $tag)
                            <span class="tag">
                                <i class="fas fa-hashtag mr-2"></i>{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Compartilhamento (Mobile) -->
                <div class="lg:hidden">
                    <div class="tags-section">
                        <div class="text-center">
                            <h3 class="text-2xl font-display font-bold mb-6 text-gray-800">
                                🚀 Gostou? Compartilhe!
                            </h3>
                            <p class="text-gray-600 mb-6">Ajude outros a encontrarem este conteúdo útil</p>
                            <div class="flex justify-center gap-4 flex-wrap">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->share_url) }}" 
                                   class="share-btn" style="background: #1877f2;" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode($post->share_url) }}&text={{ urlencode($post->title) }}" 
                                   class="share-btn" style="background: #1da1f2;" target="_blank" rel="noopener">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - ' . $post->share_url) }}" 
                                   class="share-btn" style="background: #25d366;" target="_blank" rel="noopener">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($post->share_url) }}" 
                                   class="share-btn" style="background: #0077b5;" target="_blank" rel="noopener">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <button onclick="copyToClipboard('{{ $post->share_url }}')" 
                                        class="share-btn" style="background: #6b7280;">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

        <!-- Navegação entre Posts -->
        @if($postAnterior || $proximoPost)
        <div class="navigation-posts">
            @if($postAnterior)
            <div class="nav-post group">
                <div class="flex items-center text-sm text-gray-500 mb-3">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="font-medium">Artigo Anterior</span>
                </div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-primary-600 transition-colors">
                    <a href="{{ route('blog.show', $postAnterior->slug) }}">
                        {{ $postAnterior->title }}
                    </a>
                </h3>
                @if($postAnterior->excerpt)
                <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ Str::limit($postAnterior->excerpt, 100) }}</p>
                @endif
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-calendar mr-1"></i>
                    <span>{{ $postAnterior->formatted_published_at }}</span>
                    <span class="mx-2">•</span>
                    <i class="fas fa-eye mr-1"></i>
                    <span>{{ $postAnterior->views }} views</span>
                </div>
            </div>
            @else
            <div></div>
            @endif

            @if($proximoPost)
            <div class="nav-post text-right group">
                <div class="flex items-center justify-end text-sm text-gray-500 mb-3">
                    <span class="font-medium">Próximo Artigo</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-primary-600 transition-colors">
                    <a href="{{ route('blog.show', $proximoPost->slug) }}">
                        {{ $proximoPost->title }}
                    </a>
                </h3>
                @if($proximoPost->excerpt)
                <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ Str::limit($proximoPost->excerpt, 100) }}</p>
                @endif
                <div class="flex items-center justify-end text-xs text-gray-500">
                    <i class="fas fa-calendar mr-1"></i>
                    <span>{{ $proximoPost->formatted_published_at }}</span>
                    <span class="mx-2">•</span>
                    <i class="fas fa-eye mr-1"></i>
                    <span>{{ $proximoPost->views }} views</span>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Posts Relacionados -->
        @if($postsRelacionados->count() > 0)
        <div class="related-posts">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full mb-6">
                    <i class="fas fa-lightbulb text-2xl text-white"></i>
                </div>
                <h3 class="text-4xl font-display font-black mb-4 text-gray-800">
                    🕰️ Continue Aprendendo
                </h3>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Explore estes artigos relacionados e torne-se um especialista em <strong>{{ $post->category }}</strong>
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($postsRelacionados as $index => $relacionado)
                <article class="related-post-card group" style="animation-delay: {{ $index * 0.1 }}s">
                    <div class="related-post-image">
                        @if($relacionado->featured_image && \App\Helpers\BlogHelper::imageExists($relacionado->featured_image))
                            <img src="{{ \App\Helpers\BlogHelper::getImageUrl($relacionado->featured_image) }}" alt="{{ $relacionado->title }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center relative">
                                <span class="text-white text-2xl font-black">{{ strtoupper(substr($relacionado->title, 0, 2)) }}</span>
                                <div class="absolute top-3 right-3 bg-white bg-opacity-20 backdrop-blur-sm rounded-full p-2">
                                    <i class="fas fa-tools text-white text-sm"></i>
                                </div>
                            </div>
                        @endif
                        <!-- Reading time overlay -->
                        <div class="absolute bottom-3 left-3 bg-black bg-opacity-70 text-white px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                            <i class="fas fa-clock mr-1"></i>{{ $relacionado->reading_time_text }}
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $relacionado->category }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $relacionado->formatted_published_at }}</span>
                        </div>
                        <h4 class="font-bold text-lg mb-3 line-clamp-2 group-hover:text-primary-600 transition-colors leading-tight">
                            <a href="{{ route('blog.show', $relacionado->slug) }}">
                                {{ $relacionado->title }}
                            </a>
                        </h4>
                        <p class="text-gray-600 text-sm line-clamp-3 mb-4 leading-relaxed">{{ $relacionado->excerpt }}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-eye mr-1"></i>
                                <span>{{ $relacionado->views }}</span>
                            </div>
                            <a href="{{ route('blog.show', $relacionado->slug) }}" class="group inline-flex items-center text-primary-600 hover:text-primary-700 font-medium text-sm transition-all duration-200">
                                Ler agora
                                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-200"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            
            <!-- CTA para ver mais artigos -->
            <div class="text-center mt-12">
                <a href="{{ route('blog.categoria', $post->category) }}" class="inline-flex items-center bg-gradient-to-r from-primary-600 to-purple-600 text-white font-bold px-8 py-4 rounded-2xl hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-grid-3x3 mr-3"></i>
                    Ver todos os artigos de {{ ucfirst($post->category) }}
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global functions
function scrollToContent() {
    document.getElementById('article-content').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $post->title }}',
            text: '{{ $post->excerpt }}',
            url: '{{ $post->share_url }}'
        });
    } else {
        copyToClipboard('{{ $post->share_url }}');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.style.background = '#10b981';
        
        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.style.background = '#6b7280';
        }, 2000);
        
        // Show toast
        showToast('Link copiado com sucesso! 🚀');
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Reading progress bar
    const progressBar = document.querySelector('.reading-progress');
    const scrollIndicator = document.querySelector('.scroll-indicator-progress');
    const content = document.getElementById('article-content');
    
    function updateProgress() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = scrollTop / docHeight * 100;
        
        if (progressBar) {
            progressBar.style.width = Math.min(scrollPercent, 100) + '%';
        }
        
        if (scrollIndicator) {
            scrollIndicator.style.height = Math.min(scrollPercent, 100) + '%';
        }
    }
    
    window.addEventListener('scroll', updateProgress);
    
    // Floating particles animation
    const particles = document.querySelectorAll('.particle');
    particles.forEach((particle, index) => {
        particle.style.animationDelay = `${index * 2}s`;
    });
    
    // Enhanced typography
    const contentImages = content.querySelectorAll('img');
    contentImages.forEach(img => {
        img.addEventListener('click', function() {
            // Create lightbox effect
            const lightbox = document.createElement('div');
            lightbox.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 cursor-pointer';
            lightbox.innerHTML = `
                <img src="${this.src}" alt="${this.alt}" class="max-w-90vw max-h-90vh object-contain">
                <button class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            lightbox.addEventListener('click', function() {
                document.body.removeChild(lightbox);
            });
            
            document.body.appendChild(lightbox);
        });
    });
    
    // Table of contents generation
    const headings = content.querySelectorAll('h2, h3');
    if (headings.length > 2) {
        const toc = document.createElement('div');
        toc.className = 'bg-gray-50 border-l-4 border-primary-500 p-6 rounded-lg mb-8';
        toc.innerHTML = '<h3 class="font-bold text-lg mb-4 text-gray-800">📜 Índice do Artigo</h3>';
        
        const tocList = document.createElement('ul');
        tocList.className = 'space-y-2';
        
        headings.forEach((heading, index) => {
            const id = `heading-${index}`;
            heading.id = id;
            
            const li = document.createElement('li');
            const level = heading.tagName === 'H2' ? 'font-medium' : 'font-normal ml-4 text-sm';
            li.innerHTML = `
                <a href="#${id}" class="${level} text-gray-700 hover:text-primary-600 transition-colors block py-1">
                    ${heading.textContent}
                </a>
            `;
            tocList.appendChild(li);
        });
        
        toc.appendChild(tocList);
        content.insertBefore(toc, content.firstChild);
    }
    
    // Smooth scroll for anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Reading time calculation and display
    const wordCount = content.textContent.trim().split(/\s+/).length;
    const readingTime = Math.ceil(wordCount / 200); // 200 words per minute
    
    // Enhanced share buttons
    const shareButtons = document.querySelectorAll('.share-btn');
    shareButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // Track sharing
            if (typeof gtag !== 'undefined') {
                const platform = this.href.includes('facebook') ? 'facebook' :
                              this.href.includes('twitter') ? 'twitter' :
                              this.href.includes('whatsapp') ? 'whatsapp' :
                              this.href.includes('linkedin') ? 'linkedin' : 'other';
                              
                gtag('event', 'share', {
                    method: platform,
                    content_type: 'article',
                    item_id: '{{ $post->slug }}'
                });
            }
        });
    });
    
    // Auto-highlight code blocks
    const codeBlocks = content.querySelectorAll('pre code');
    codeBlocks.forEach(block => {
        block.className += ' language-javascript'; // Default highlighting
    });
    
    // Lazy loading for related posts images
    const relatedImages = document.querySelectorAll('.related-post-card img');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
                img.onload = () => {
                    img.style.opacity = '1';
                };
                observer.unobserve(img);
            }
        });
    });
    
    relatedImages.forEach(img => imageObserver.observe(img));
    
    // Analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            page_title: '{{ $post->title }}',
            page_location: '{{ $post->canonical_url }}',
            content_group1: 'Blog Post',
            content_group2: '{{ $post->category }}'
        });
        
        // Track reading progress
        let readingMilestones = [25, 50, 75, 100];
        let milestonesReached = [];
        
        window.addEventListener('scroll', function() {
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            
            readingMilestones.forEach(milestone => {
                if (scrollPercent >= milestone && !milestonesReached.includes(milestone)) {
                    milestonesReached.push(milestone);
                    gtag('event', 'scroll', {
                        event_category: 'Reading Progress',
                        event_label: '{{ $post->title }}',
                        value: milestone
                    });
                }
            });
        });
    }
    
    // Add estimated reading time
    const metaInfo = document.querySelector('.meta-info');
    if (metaInfo && readingTime) {
        const readingTimeDiv = metaInfo.querySelector('div:nth-child(2)');
        if (readingTimeDiv) {
            readingTimeDiv.querySelector('.text-2xl').textContent = readingTime;
        }
    }
    
    console.log('🚀 Artigo carregado com sucesso!');
    console.log(`📚 Palavras: ${wordCount} | Tempo estimado: ${readingTime} min`);
    console.log(`🏷️ Categoria: {{ $post->category }}`);
});
</script>
@endpush
