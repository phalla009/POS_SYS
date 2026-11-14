<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cafe Menu</title>
    <link rel="icon" type="image/x-icon" href="/image/logokr.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #FAF7F2;           /* Warm oat milk */
            --surface: #FFFFFF;      /* Crisp porcelain */
            --surface-alt: #F3EDE4;  /* Steamed foam */
            --ink: #231812;          /* Dark espresso roast */
            --muted: #7C6E65;        /* Roasted hazelnut */
            --accent: #B86B35;       /* Caramel / Terracotta */
            --accent-soft: #EFE3D6;  /* Latte tint */
            --border: rgba(35, 24, 18, 0.08);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* Editorial Serif Headings */
        .brand, .hero h1, .cat-title {
            font-family: 'Fraunces', serif;
        }

        /* Sticky Glass Navbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(250, 247, 242, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }
        .brand {
            font-weight: 700;
            font-size: 1.45rem;
            letter-spacing: -0.02em;
            color: var(--ink);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .brand-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            display: inline-block;
        }

        /* Hero Section */
        .hero {
            padding: 36px 0 16px;
        }
        .hero-eyebrow {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--accent);
            margin-bottom: 6px;
        }
        .hero h1 {
            font-weight: 700;
            font-size: clamp(2rem, 4.5vw, 2.85rem);
            letter-spacing: -0.02em;
            line-height: 1.1;
            margin: 0;
            color: var(--ink);
        }
        .hero p {
            color: var(--muted);
            font-size: 1rem;
            margin: 10px 0 0;
            max-width: 480px;
        }

        /* Category Quick-Jump Pills */
        .cat-nav-wrap {
            position: sticky;
            top: 68px; /* ត្រូវនឹងកម្ពស់ពិតប្រាកដរបស់ Topbar ដើម្បីមិនឱ្យជាន់គ្នា */
            z-index: 15;
            background: rgba(250, 247, 242, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 12px 0;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        .cat-nav {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .cat-nav::-webkit-scrollbar { display: none; }
        .cat-chip {
            flex: 0 0 auto;
            padding: 8px 18px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--ink);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(35, 24, 18, 0.03);
        }
        .cat-chip:hover, .cat-chip:focus {
            background: var(--ink);
            color: var(--bg);
            border-color: var(--ink);
            transform: translateY(-1px);
        }

        /* Category Sections */
        .cat-section {
            padding-top: 28px;
            scroll-margin-top: 135px; /* ទុកគម្លាតពេល scroll ចំចំណងជើង category */
        }
        .cat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 22px;
        }
        .cat-title {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.01em;
            margin: 0;
            color: var(--ink);
        }
        .cat-count {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--accent);
            background: var(--accent-soft);
            padding: 4px 12px;
            border-radius: 999px;
        }

        /* Menu Item Card */
        .product-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            border-color: rgba(184, 107, 53, 0.35);
            box-shadow: 0 18px 36px -10px rgba(35, 24, 18, 0.10);
        }
        .img-wrap {
            background: radial-gradient(circle at center, #F9F6F1 0%, var(--surface-alt) 100%);
            aspect-ratio: 4 / 3.4;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            position: relative;
            overflow: hidden;
        }
        .img-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 14px rgba(35, 24, 18, 0.08));
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .product-card:hover .img-wrap img {
            transform: scale(1.07);
        }

        .card-body-x {
            padding: 16px 18px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }
        .product-name {
            font-weight: 600;
            font-size: 1.02rem;
            line-height: 1.35;
            color: var(--ink);
            margin: 0 0 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.7em;
        }
        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
        }
        .price {
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--accent);
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }
        .price-currency {
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 1px;
        }

        /* Empty State */
        .empty {
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: 24px;
            padding: 64px 20px;
            text-align: center;
            color: var(--muted);
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="container py-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('shop.index') }}" class="brand">
            KR Cafe Shop<span class="brand-dot"></span>
        </a>
        <span class="small fw-medium" style="color: var(--muted);">Freshly Crafted Daily</span>
    </div>
</header>

<main class="container pb-5">

    <section class="hero">
        <div class="hero-eyebrow">Artisan Coffee &amp; Bakery</div>
        <h1>Our Menu</h1>
        <p>Handcrafted espresso, slow-steeped brews, and warm pastries made fresh every morning.</p>
    </section>

    @if ($groups->isNotEmpty())
        {{-- Sticky Category Quick-Jump --}}
        <div class="cat-nav-wrap">
            <div class="container">
                <nav class="cat-nav">
                    @foreach ($groups as $categoryName => $items)
                        <a href="#cat-{{ $loop->index }}" class="cat-chip">{{ $categoryName }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- One section per category --}}
        @foreach ($groups as $categoryName => $items)
            <section class="cat-section" id="cat-{{ $loop->index }}">
                <div class="cat-header">
                    <h2 class="cat-title">{{ $categoryName }}</h2>
                    <span class="cat-count">{{ $items->count() }} {{ \Illuminate\Support\Str::plural('item', $items->count()) }}</span>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2">
                    @foreach ($items as $product)
                        @php
                            $firstImage = $product->images->first();
                            $imageUrl = $firstImage
                                ? asset('images/products/' . $firstImage->image)
                                : 'https://placehold.co/600x450/F3EDE4/7C6E65?text=No+Image';
                        @endphp
                        <div class="col">
                            <article class="product-card">
                                <div class="img-wrap">
                                    @php
                                        $firstImage = $product->images->first();
                                        $imagePath = $firstImage ? public_path('images/products/' . $firstImage->image) : null;
                                    @endphp

                                    @if ($firstImage && file_exists($imagePath))
                                        <img src="{{ asset('images/products/' . $firstImage->image) }}" alt="{{ $product->name }}" loading="lazy">
                                    @else
                                        {{-- បង្ហាញ Icon កាហ្វេជំនួស "No Image" ឱ្យមើលទៅស្អាតបែប Modern --}}
                                        <div class="d-flex flex-column align-items-center justify-content-center text-center">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.6; margin-bottom: 4px;">
                                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                                <line x1="14" y1="1" x2="14" y2="4"></line>
                                            </svg>
                                            <span style="font-size: 0.75rem; color: var(--muted); font-weight: 500;">Cafe Menu</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body-x">
                                    <h3 class="product-name">{{ $product->name }}</h3>
                                    <div class="price-row">
                                        <div class="price">
                                            <span class="price-currency">$</span>{{ number_format($product->price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    @else
        <div class="empty mt-4">No menu items available yet.</div>
    @endif
</main>

</body>
</html>
