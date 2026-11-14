<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cafe Menu</title>
    <link rel="icon" type="image/x-icon" href="/image/logokr.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ==========================================================
           Agrave Pro (self-hosted)
           Put your web font files in: public/fonts/
             AgravePro-Light.woff2
             AgravePro-Regular.woff2
             AgravePro-Bold.woff2
           ========================================================== */
        @font-face {
            font-family: 'Agrave Pro';
            src: url('{{ asset("fonts/AgravePro-Light.woff2") }}') format('woff2');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Agrave Pro';
            src: url('{{ asset("fonts/AgravePro-Regular.woff2") }}') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Agrave Pro';
            src: url('{{ asset("fonts/AgravePro-Bold.woff2") }}') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        :root {
            /* Change these two lines to swap fonts anywhere on the page */
            --font-display: 'Agrave Pro', 'Segoe Script', cursive, serif;
            --font-text: 'Agrave Pro', system-ui, -apple-system, 'Segoe UI', sans-serif;

            --bg: #4A3326;           /* Coffee brown */
            --on-bg: #FAF7F2;        /* Text on coffee background */
            --on-bg-muted: #D8C5B4;  /* Muted text on coffee background */
            --accent-light: #E3A56F; /* Caramel, readable on coffee */
            --line-on-bg: rgba(250, 247, 242, 0.14);
            --card: #6B4C3A;         /* Mocha card */
            --card-img-from: #7B5A47; /* Mocha glow (photo area, center) */
            --card-img-to: #4A3326;   /* Deep coffee (photo area, edge) */
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
            font-family: var(--font-text);
            font-weight: 400;
            background-color: var(--bg);
            color: var(--on-bg);
            -webkit-font-smoothing: antialiased;
        }

        /* Display headings */
        .brand, .hero h1, .cat-title {
            font-family: var(--font-display);
        }

        /* Sticky Glass Navbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(74, 51, 38, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--line-on-bg);
        }
        .brand {
            font-weight: 700;
            font-size: 1.55rem;
            letter-spacing: 0;
            color: var(--on-bg);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .brand-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-light);
            display: inline-block;
        }
        .topbar .fw-medium { font-weight: 400 !important; }

        /* Hero Section */
        .hero {
            padding: 36px 0 16px;
        }
        .hero-eyebrow {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--accent-light);
            margin-bottom: 6px;
        }
        .hero h1 {
            font-weight: 700;
            font-size: clamp(2.2rem, 5vw, 3.1rem);
            letter-spacing: 0;
            line-height: 1.1;
            margin: 0;
            color: var(--on-bg);
        }
        .hero p {
            color: var(--on-bg-muted);
            font-size: 1.05rem;
            margin: 10px 0 0;
            max-width: 480px;
        }

        /* Category Quick-Jump Pills */
        .cat-nav-wrap {
            position: sticky;
            top: 68px; /* matches the real height of the topbar so they don't overlap */
            z-index: 15;
            background: rgba(74, 51, 38, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 12px 0;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--line-on-bg);
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
            background: var(--card);
            border: 1px solid var(--line-on-bg);
            color: var(--on-bg);
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
        }
        .cat-chip:hover, .cat-chip:focus {
            background: var(--accent);
            color: #FFFFFF;
            border-color: var(--accent);
            transform: translateY(-1px);
        }

        /* Category Sections */
        .cat-section {
            padding-top: 28px;
            scroll-margin-top: 135px; /* gap when scrolling to a category title */
        }
        .cat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--line-on-bg);
            padding-bottom: 12px;
            margin-bottom: 22px;
        }
        .cat-title {
            font-weight: 700;
            font-size: 1.7rem;
            letter-spacing: 0;
            margin: 0;
            color: var(--on-bg);
        }
        .cat-count {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent);
            background: var(--accent-soft);
            padding: 4px 12px;
            border-radius: 999px;
        }

        /* Menu Item Card */
        .product-card {
            background: var(--card);
            border: 1px solid var(--line-on-bg);
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-light);
            box-shadow: 0 18px 36px -10px rgba(0, 0, 0, 0.35);
        }
        .img-wrap {
            background: radial-gradient(circle at center, var(--card-img-from) 0%, var(--card-img-to) 100%);
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
            font-weight: 700;
            font-size: 1.1rem;
            line-height: 1.35;
            color: var(--on-bg);
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
            border-top: 1px dashed var(--line-on-bg);
        }
        .price {
            font-weight: 700;
            font-size: 1.25rem;
            color: #FFFFFF;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0;
        }
        .price-currency {
            font-size: 0.9rem;
            font-weight: 700;
            margin-right: 1px;
        }

        /* Empty State */
        .empty {
            background: var(--card);
            border: 1px dashed var(--line-on-bg);
            border-radius: 24px;
            padding: 64px 20px;
            text-align: center;
            color: var(--on-bg-muted);
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="container py-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('shop.index') }}" class="brand">
            KR Cafe Shop<span class="brand-dot"></span>
        </a>
        <span class="small fw-medium" style="color: var(--on-bg-muted);">Freshly Crafted Daily</span>
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
                                        {{-- Coffee cup icon instead of a "No Image" placeholder --}}
                                        <div class="d-flex flex-column align-items-center justify-content-center text-center">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 4px;">
                                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                                <line x1="14" y1="1" x2="14" y2="4"></line>
                                            </svg>
                                            <span style="font-size: 0.8rem; color: #FFFFFF;">Cafe Menu</span>
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
