<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $siteUrl = 'https://libnextgen.alwaysdata.net';
        $seoTitle = 'LibNextGen · Library Management Software with Floor Planner';
        $seoDescription = 'LibNextGen is the all-in-one SaaS platform for study libraries and reading halls: admissions, a drag-and-drop seat floor planner, fee collection and attendance across every branch.';
        $seoImage = $siteUrl.'/images/marketing/og-image.png';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="library management software, library SaaS, library ERP, seat allocation software, library floor planner, study library management, reading hall software, library fee collection software, student library management system">
    <meta name="author" content="LibNextGen">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $siteUrl }}/">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <meta name="theme-color" content="#3453c4">

    {{-- Open Graph (Facebook, LinkedIn, WhatsApp, Slack, Discord, iMessage) --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="LibNextGen">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $siteUrl }}/">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:secure_url" content="{{ $seoImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="LibNextGen — library management software with floor planner, fee collection and attendance">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:image:alt" content="LibNextGen — library management software with floor planner, fee collection and attendance">

    {{-- Structured data --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'LibNextGen',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'description' => $seoDescription,
        'url' => $siteUrl.'/',
        'image' => $seoImage,
        'offers' => $plans->map(fn ($plan) => [
            '@type' => 'Offer',
            'name' => $plan->name,
            'price' => (string) $plan->price,
            'priceCurrency' => 'INR',
            'url' => $siteUrl.route('register', [], false),
        ])->values()->all(),
    ], JSON_UNESCAPED_SLASHES) !!}
    </script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --lp-ease: cubic-bezier(.22,1,.36,1); }
        html { scroll-behavior: smooth; }
        html, body { overflow-x: hidden; max-width: 100%; }

        .lp-wrap { max-width: 90rem; margin-inline: auto; padding-inline: 1.5rem; }
        @media (min-width: 1280px) { .lp-wrap { padding-inline: 3rem; } }
        .lp-nav { position: sticky; top: 0; z-index: 40; background: rgba(255,255,255,.85); border-bottom: 1px solid var(--color-line); backdrop-filter: blur(8px); }
        .lp-nav-inner { display: flex; align-items: center; justify-content: space-between; height: 4rem; }
        .lp-logo { display: flex; align-items: center; gap: .625rem; text-decoration: none; color: var(--color-ink); }
        .lp-logo-mark { width: 2rem; height: 2rem; border-radius: .5rem; background: var(--color-brand-500); color: #fff; display: grid; place-items: center; font-weight: 600; }
        .lp-nav-links { display: none; align-items: center; gap: 2rem; font-size: 13.5px; font-weight: 500; color: var(--color-muted); }
        @media (min-width: 768px) { .lp-nav-links { display: flex; } }
        .lp-nav-links a { color: inherit; text-decoration: none; }
        .lp-nav-links a:hover { color: var(--color-ink); }
        .lp-nav-actions { display: flex; align-items: center; gap: .5rem; }
        .lp-nav-toggle { display: inline-flex; align-items: center; justify-content: center; width: 2.1rem; height: 2.1rem; border-radius: .5rem; border: 1px solid var(--color-line); background: #fff; color: var(--color-ink); cursor: pointer; }
        @media (min-width: 768px) { .lp-nav-toggle { display: none; } }
        .lp-nav-login-desktop { display: none; }
        @media (min-width: 768px) { .lp-nav-login-desktop { display: inline-flex; } }
        .lp-nav-mobile { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s var(--lp-ease); border-top: 1px solid transparent; }
        .lp-nav-mobile.is-open { grid-template-rows: 1fr; border-top-color: var(--color-line); }
        .lp-nav-mobile-inner { overflow: hidden; min-height: 0; }
        .lp-nav-mobile a { display: block; padding: .85rem 1.5rem; font-size: 14px; font-weight: 500; color: var(--color-ink); text-decoration: none; border-bottom: 1px solid var(--color-line); }
        .lp-nav-mobile a:last-child { border-bottom: none; }
        .lp-nav-mobile-login { color: var(--color-brand-600) !important; font-weight: 600 !important; }
        @media (min-width: 768px) { .lp-nav-mobile { display: none; } }

        /* ---- Animations ---- */
        @keyframes lp-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
        @keyframes lp-float-slow { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(4deg); } }
        @keyframes lp-pulse-ring { 0% { transform: scale(.95); opacity: .7; } 70% { transform: scale(1.4); opacity: 0; } 100% { opacity: 0; } }
        @keyframes lp-fade-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .7s var(--lp-ease), transform .7s var(--lp-ease); }
        .reveal.is-visible { opacity: 1; transform: translateY(0); }
        .reveal-delay-1.is-visible { transition-delay: .08s; }
        .reveal-delay-2.is-visible { transition-delay: .16s; }
        .reveal-delay-3.is-visible { transition-delay: .24s; }
        .reveal-delay-4.is-visible { transition-delay: .32s; }
        .reveal-delay-5.is-visible { transition-delay: .4s; }

        .lp-hero { position: relative; overflow: hidden; background: radial-gradient(ellipse at top right, var(--color-brand-100) 0%, transparent 55%); }
        .lp-blob { position: absolute; border-radius: 9999px; filter: blur(2px); opacity: .55; pointer-events: none; }
        .lp-blob-1 { width: 90px; height: 90px; background: var(--color-brand-200); top: 18%; left: 6%; animation: lp-float 7s ease-in-out infinite; }
        .lp-blob-2 { width: 54px; height: 54px; background: #fde68a; top: 62%; left: 11%; animation: lp-float-slow 8.5s ease-in-out infinite; }
        .lp-blob-3 { width: 70px; height: 70px; background: var(--color-brand-100); top: 12%; right: 8%; animation: lp-float-slow 9s ease-in-out infinite; }
        .lp-blob-4 { width: 42px; height: 42px; background: #bbf7d0; top: 70%; right: 12%; animation: lp-float 6.5s ease-in-out infinite; }
        @media (max-width: 900px) { .lp-blob { display: none; } }

        .lp-hero-inner { position: relative; z-index: 1; text-align: center; padding-top: 4rem; padding-bottom: 2.5rem; }
        @media (min-width: 1024px) { .lp-hero-inner { padding-top: 6rem; padding-bottom: 3rem; } }
        .lp-pill { display: inline-flex; align-items: center; gap: .5rem; padding: .3rem .8rem; border-radius: 9999px; background: var(--color-brand-50); color: var(--color-brand-700); font-size: 12.5px; font-weight: 500; }
        .lp-pill-dot { position: relative; width: 7px; height: 7px; border-radius: 50%; background: var(--color-brand-600); }
        .lp-pill-dot::after { content: ''; position: absolute; inset: 0; border-radius: 50%; background: var(--color-brand-500); animation: lp-pulse-ring 1.8s ease-out infinite; }
        .lp-h1 { margin-top: 1.25rem; font-size: 32px; line-height: 1.2; font-weight: 600; letter-spacing: -0.01em; max-width: 46rem; margin-inline: auto; }
        @media (min-width: 640px) { .lp-h1 { font-size: 44px; line-height: 1.1; } }
        .lp-h1 .lp-accent { color: var(--color-brand-600); }
        .lp-sub { margin-top: 1rem; font-size: 15px; color: var(--color-muted); max-width: 36rem; margin-inline: auto; line-height: 1.6; }
        @media (min-width: 640px) { .lp-sub { font-size: 16px; } }
        .lp-cta-row { margin-top: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .75rem; }
        @media (min-width: 640px) { .lp-cta-row { flex-direction: row; } }
        .lp-btn-lift { transition: transform .2s var(--lp-ease), box-shadow .2s var(--lp-ease); }
        .lp-btn-lift:hover { transform: translateY(-2px); box-shadow: 0 10px 24px -8px rgba(66,100,217,.45); }
        .lp-fineprint { margin-top: .75rem; font-size: 12.5px; color: var(--color-muted); }
        .lp-stats { margin-top: 3.5rem; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; max-width: 32rem; margin-inline: auto; text-align: center; }
        .lp-stat-num { font-size: 26px; font-weight: 600; color: var(--color-ink); }
        .lp-stat-label { font-size: 12.5px; color: var(--color-muted); margin-top: .25rem; }

        /* ---- Screenshot slider ---- */
        .lp-slider-wrap { max-width: 78rem; margin-inline: auto; padding-inline: 1.5rem; padding-bottom: 4rem; position: relative; z-index: 1; }
        @media (min-width: 1280px) { .lp-slider-wrap { padding-inline: 3rem; } }
        @media (min-width: 1024px) { .lp-slider-wrap { padding-bottom: 5rem; } }
        .lp-slider { border-radius: .75rem; border: 1px solid var(--color-line); box-shadow: 0 30px 60px -20px rgba(28,34,48,0.28); overflow: hidden; background: #fff; }
        .lp-slider-bar { height: 2.25rem; background: var(--color-canvas); border-bottom: 1px solid var(--color-line); display: flex; align-items: center; gap: .375rem; padding-inline: 1rem; min-width: 0; }
        .lp-dot-win { width: .625rem; height: .625rem; border-radius: 9999px; background: #cbd5e1; flex-shrink: 0; }
        .lp-slider-url { margin-left: .75rem; font-size: 11.5px; color: var(--color-muted); font-family: ui-monospace, monospace; min-width: 0; flex: 1 1 auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .lp-slides { position: relative; aspect-ratio: 16 / 9; background: #f8fafc; overflow: hidden; }
        .lp-slide { position: absolute; inset: 0; opacity: 0; transform: scale(1.01); transition: opacity .6s var(--lp-ease), transform .6s var(--lp-ease); }
        .lp-slide.is-active { opacity: 1; transform: scale(1); z-index: 1; }
        .lp-slide img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
        .lp-slide-caption { position: absolute; left: 1rem; bottom: 1rem; background: rgba(28,34,48,.85); color: #fff; font-size: 12px; font-weight: 500; padding: .35rem .75rem; border-radius: 9999px; backdrop-filter: blur(4px); }

        .lp-slider-nav { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-top: 1.25rem; }
        .lp-slider-dot { width: 8px; height: 8px; border-radius: 9999px; background: var(--color-line); border: none; cursor: pointer; padding: 0; transition: all .25s var(--lp-ease); }
        .lp-slider-dot.is-active { width: 22px; background: var(--color-brand-600); }
        .lp-slider-arrow { display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 9999px; border: 1px solid var(--color-line); background: #fff; cursor: pointer; color: var(--color-ink); transition: background .2s, transform .2s; }
        .lp-slider-arrow:hover { background: var(--color-canvas); transform: scale(1.06); }
        .lp-slider-controls { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 1.25rem; }

        .lp-flow-steps { margin-top: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: .5rem; font-size: 12.5px; color: var(--color-muted); }
        @media (max-width: 640px) { .lp-flow-steps { display: none; } }
        .lp-flow-step { display: flex; align-items: center; gap: .375rem; padding: .3rem .7rem; border-radius: 9999px; border: 1px solid var(--color-line); cursor: pointer; background: #fff; transition: all .2s var(--lp-ease); }
        .lp-flow-step.is-active { background: var(--color-brand-50); border-color: var(--color-brand-200); color: var(--color-brand-700); font-weight: 600; }
        .lp-flow-step-num { width: 16px; height: 16px; border-radius: 9999px; background: var(--color-line); color: var(--color-muted); font-size: 10px; display: grid; place-items: center; font-weight: 700; }
        .lp-flow-step.is-active .lp-flow-step-num { background: var(--color-brand-600); color: #fff; }

        .lp-strip { border-top: 1px solid var(--color-line); border-bottom: 1px solid var(--color-line); background: var(--color-canvas); }
        .lp-strip-inner { padding-block: 1.5rem; text-align: center; font-size: 12.5px; color: var(--color-muted); }

        .lp-section { padding-block: 5rem; }
        @media (min-width: 1024px) { .lp-section { padding-block: 7rem; } }
        .lp-section-head { text-align: center; max-width: 42rem; margin-inline: auto; }
        .lp-h2 { font-size: 26px; font-weight: 600; letter-spacing: -0.01em; }
        @media (min-width: 640px) { .lp-h2 { font-size: 32px; } }
        .lp-section-head p { margin-top: .75rem; color: var(--color-muted); }

        /* ---- Floor planner spotlight ---- */
        .lp-spotlight { background: linear-gradient(180deg, var(--color-brand-50) 0%, #fff 70%); border-top: 1px solid var(--color-line); border-bottom: 1px solid var(--color-line); }
        .lp-spotlight-alt { background: linear-gradient(180deg, #fff 0%, var(--color-canvas) 70%); border-top: none; }
        .lp-spotlight-grid { margin-top: 3rem; display: grid; gap: 2.5rem; align-items: center; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1024px) { .lp-spotlight-grid { grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); } }
        @media (min-width: 1024px) { .lp-spotlight-alt .lp-spotlight-grid { grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); } }
        .lp-spotlight-shot { min-width: 0; }
        .lp-spotlight-copy .lp-pill { margin-bottom: 1rem; }
        .lp-spotlight-copy h3 { font-size: 24px; font-weight: 600; letter-spacing: -0.01em; }
        @media (min-width: 640px) { .lp-spotlight-copy h3 { font-size: 28px; } }
        .lp-spotlight-copy p { margin-top: .75rem; color: var(--color-muted); line-height: 1.65; }
        .lp-spotlight-list { margin-top: 1.5rem; display: flex; flex-direction: column; gap: .75rem; }
        .lp-spotlight-list li { display: flex; align-items: flex-start; gap: .625rem; font-size: 13.5px; list-style: none; }
        .lp-spotlight-check { width: 1.1rem; height: 1.1rem; margin-top: 1px; color: var(--color-brand-600); flex-shrink: 0; }
        .lp-spotlight-shot { border-radius: .75rem; overflow: hidden; border: 1px solid var(--color-line); box-shadow: 0 30px 60px -22px rgba(66,100,217,.35); position: relative; animation: lp-float 9s ease-in-out infinite; }
        .lp-spotlight-shot .lp-slider-bar { }
        .lp-spotlight-shot img { width: 100%; display: block; }

        .lp-grid { margin-top: 3.5rem; display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .lp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .lp-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .lp-feature { transition: transform .25s var(--lp-ease), box-shadow .25s var(--lp-ease); }
        .lp-feature:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -16px rgba(28,34,48,.22); }
        .lp-feature-icon { width: 2.5rem; height: 2.5rem; border-radius: .5rem; background: var(--color-brand-50); display: grid; place-items: center; font-size: 18px; transition: transform .3s var(--lp-ease); }
        .lp-feature:hover .lp-feature-icon { transform: scale(1.12) rotate(-6deg); }
        .lp-feature h3 { margin-top: 1rem; font-weight: 600; font-size: 15px; }
        .lp-feature p { margin-top: .375rem; font-size: 13.5px; color: var(--color-muted); line-height: 1.6; }

        .lp-pricing-grid { margin-top: 3.5rem; display: grid; gap: 1.5rem; max-width: 72rem; margin-inline: auto; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 768px) { .lp-pricing-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 767px) { .lp-plan.is-highlight { margin-top: .5rem; } }
        .lp-plan { position: relative; transition: transform .25s var(--lp-ease), box-shadow .25s var(--lp-ease); }
        .lp-plan:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -16px rgba(28,34,48,.22); }
        .lp-plan.is-highlight { border-color: var(--color-brand-500); box-shadow: 0 0 0 3px var(--color-brand-100); }
        .lp-plan-badge { position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%); background: var(--color-brand-600); color: #fff; font-size: 11px; font-weight: 500; padding: .25rem .75rem; border-radius: 9999px; white-space: nowrap; }
        .lp-plan h3 { font-weight: 600; font-size: 16px; }
        .lp-price { margin-top: .75rem; display: flex; align-items: baseline; gap: .25rem; }
        .lp-price-num { font-size: 30px; font-weight: 600; letter-spacing: -0.01em; }
        .lp-price-cycle { color: var(--color-muted); font-size: 13px; }
        .lp-plan-features { margin-top: 1.5rem; display: flex; flex-direction: column; gap: .625rem; font-size: 13.5px; }
        .lp-plan-features li { display: flex; align-items: flex-start; gap: .5rem; list-style: none; }
        .lp-check { width: 1rem; height: 1rem; margin-top: 2px; color: var(--color-brand-600); flex-shrink: 0; }

        .lp-faq-list { margin-top: 3rem; max-width: 44rem; margin-inline: auto; display: flex; flex-direction: column; gap: .75rem; }
        .lp-faq-item { border: 1px solid var(--color-line); border-radius: .75rem; background: #fff; transition: border-color .2s var(--lp-ease), box-shadow .2s var(--lp-ease); }
        .lp-faq-item.is-open { border-color: var(--color-brand-200); box-shadow: 0 8px 20px -12px rgba(66,100,217,.25); }
        .lp-faq-trigger { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; background: none; border: none; text-align: left; font-size: 14.5px; font-weight: 600; color: var(--color-ink); cursor: pointer; }
        .lp-faq-chevron { flex-shrink: 0; color: var(--color-muted); transition: transform .25s var(--lp-ease); }
        .lp-faq-item.is-open .lp-faq-chevron { transform: rotate(180deg); color: var(--color-brand-600); }
        .lp-faq-panel { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s var(--lp-ease); }
        .lp-faq-item.is-open .lp-faq-panel { grid-template-rows: 1fr; }
        .lp-faq-panel-inner { overflow: hidden; min-height: 0; padding: 0 1.25rem; font-size: 13.5px; color: var(--color-muted); line-height: 1.65; }
        .lp-faq-item.is-open .lp-faq-panel-inner { padding-bottom: 1.1rem; }

        .lp-cta { text-align: center; }
        .lp-cta h2 { max-width: 40rem; margin-inline: auto; }
        .lp-cta p { margin-top: .75rem; color: var(--color-muted); max-width: 28rem; margin-inline: auto; }

        .lp-footer { background: #fff; }
        .lp-footer-banner { height: 48px; background: linear-gradient(100deg, var(--color-brand-700) 0%, var(--color-brand-500) 55%, var(--color-brand-200) 100%); border-radius: 0 0 40px 40px; }
        @media (min-width: 768px) { .lp-footer-banner { height: 64px; } }
        .lp-footer-body { padding-block: 2.5rem 1.75rem; }
        .lp-footer-top { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; }
        .lp-footer-brand { display: flex; align-items: center; gap: .75rem; }
        .lp-footer-mark { width: 2.25rem; height: 2.25rem; border-radius: .65rem; background: var(--color-brand-500); color: #fff; display: grid; place-items: center; font-weight: 600; font-size: 15px; }
        .lp-footer-brand-name { font-weight: 700; font-size: 18px; letter-spacing: -0.01em; }
        .lp-footer-tagline { font-size: 13px; color: var(--color-muted); margin-top: .35rem; margin-left: calc(2.25rem + .75rem); }
        .lp-footer-open { display: inline-flex; align-items: center; gap: .3rem; font-size: 13.5px; font-weight: 600; color: var(--color-brand-600); text-decoration: none; }
        .lp-footer-open:hover { color: var(--color-brand-700); }

        .lp-footer-card { margin-top: 2rem; border: 1px solid var(--color-line); border-radius: .85rem; background: linear-gradient(90deg, var(--color-brand-50), #fff 55%, #fef2f4); padding: 1.1rem 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
        .lp-footer-credit { font-size: 13.5px; color: var(--color-muted); }
        .lp-footer-credit b { color: var(--color-ink); font-weight: 700; }
        .lp-footer-heart { color: #ef4444; }
        .lp-footer-links { display: flex; flex-wrap: wrap; gap: .6rem; }
        .lp-footer-link { display: inline-flex; align-items: center; gap: .3rem; padding: .45rem .9rem; border-radius: 9999px; border: 1px solid var(--color-line); background: #fff; font-size: 12.5px; font-weight: 600; color: var(--color-ink); text-decoration: none; transition: all .2s var(--lp-ease); }
        .lp-footer-link:hover { border-color: var(--color-brand-200); color: var(--color-brand-700); transform: translateY(-1px); }

        .lp-footer-bottom { margin-top: 1.75rem; padding-top: 1.5rem; border-top: 1px solid var(--color-line); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; font-size: 12px; color: var(--color-muted); }
    </style>
</head>
<body class="bg-white text-ink antialiased">

    {{-- Nav --}}
    <header class="lp-nav">
        <div class="lp-wrap lp-nav-inner">
            <a href="{{ url('/') }}" class="lp-logo">
                <span class="lp-logo-mark"><x-brand-icon class="" style="width:1rem;height:1rem" /></span>
                <span class="font-semibold" style="font-size:16px">LibNextGen</span>
            </a>
            <nav class="lp-nav-links">
                <a href="#features">Features</a>
                <a href="#floor-planner">Floor Planner</a>
                <a href="#pricing">Pricing</a>
                <a href="#faq">FAQ</a>
            </nav>
            <div class="lp-nav-actions">
                <a href="{{ route('login') }}" class="btn btn-sm lp-nav-login-desktop">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm lp-btn-lift">Start free trial</a>
                <button type="button" class="lp-nav-toggle" data-nav-toggle aria-label="Open menu" aria-expanded="false">
                    <svg data-nav-icon-open width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <svg data-nav-icon-close width="18" height="18" viewBox="0 0 20 20" fill="none" style="display:none"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
        <div class="lp-nav-mobile" data-nav-mobile>
            <div class="lp-nav-mobile-inner">
                <a href="#features" data-nav-close>Features</a>
                <a href="#floor-planner" data-nav-close>Floor Planner</a>
                <a href="#pricing" data-nav-close>Pricing</a>
                <a href="#faq" data-nav-close>FAQ</a>
                <a href="{{ route('login') }}" class="lp-nav-mobile-login">Log in</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="lp-hero">
        <span class="lp-blob lp-blob-1"></span>
        <span class="lp-blob lp-blob-2"></span>
        <span class="lp-blob lp-blob-3"></span>
        <span class="lp-blob lp-blob-4"></span>

        <div class="lp-wrap lp-hero-inner">
            <span class="lp-pill reveal is-visible"><span class="lp-pill-dot"></span> Built for study libraries &amp; reading halls</span>
            <h1 class="lp-h1 reveal is-visible reveal-delay-1">Run every library branch — <span class="lp-accent">and every seat</span> — from one place.</h1>
            <p class="lp-sub reveal is-visible reveal-delay-2">Admissions, a drag-and-drop floor planner for seat allocation, fee collection and attendance — one dashboard for every branch, staff member and student.</p>
            <div class="lp-cta-row reveal is-visible reveal-delay-3">
                <a href="{{ route('register') }}" class="btn btn-primary !h-10 px-5 lp-btn-lift">Start 14-day free trial</a>
                <a href="#screenshots" class="btn !h-10 px-5 lp-btn-lift">See it in action</a>
            </div>
            <p class="lp-fineprint reveal is-visible reveal-delay-3">No credit card required · Cancel anytime</p>

            <dl class="lp-stats reveal is-visible reveal-delay-4">
                <div><dd class="lp-stat-num" data-count="148">0</dd><dt class="lp-stat-label">Libraries</dt></div>
                <div><dd class="lp-stat-num" data-count="12480">0</dd><dt class="lp-stat-label">Students</dt></div>
                <div><dd class="lp-stat-num" data-count="213">0</dd><dt class="lp-stat-label">Branches</dt></div>
            </dl>
        </div>

        {{-- Screenshot slider --}}
        <div id="screenshots" class="lp-slider-wrap reveal">
            @php
                $slides = [
                    ['title' => 'Sign in', 'path' => '/login', 'img' => 'shot-1-login.png'],
                    ['title' => 'Owner dashboard', 'path' => '/dashboard', 'img' => 'shot-2-dashboard.png'],
                    ['title' => 'Floor planner designer', 'path' => '/halls/1/floor-plan', 'img' => 'shot-3-floorplanner.png'],
                    ['title' => 'Branches', 'path' => '/branches', 'img' => 'shot-4-branches.png'],
                    ['title' => 'Students', 'path' => '/students', 'img' => 'shot-5-students.png'],
                ];
            @endphp
            <div class="lp-slider" data-slider>
                <div class="lp-slider-bar">
                    <span class="lp-dot-win"></span>
                    <span class="lp-dot-win"></span>
                    <span class="lp-dot-win"></span>
                    <span class="lp-slider-url" data-slider-url>https://libnextgen.alwaysdata.net{{ $slides[0]['path'] }}</span>
                </div>
                <div class="lp-slides">
                    @foreach ($slides as $i => $slide)
                        <div class="lp-slide {{ $i === 0 ? 'is-active' : '' }}" data-slide data-url="https://libnextgen.alwaysdata.net{{ $slide['path'] }}">
                            <img src="{{ asset('images/marketing/'.$slide['img']) }}" alt="LibNextGen — {{ $slide['title'] }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                            <span class="lp-slide-caption">{{ $i + 1 }}. {{ $slide['title'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lp-slider-controls">
                <button type="button" class="lp-slider-arrow" data-slider-prev aria-label="Previous screenshot">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="lp-slider-nav" data-slider-dots>
                    @foreach ($slides as $i => $slide)
                        <button type="button" class="lp-slider-dot {{ $i === 0 ? 'is-active' : '' }}" data-slider-dot="{{ $i }}" aria-label="Show {{ $slide['title'] }}"></button>
                    @endforeach
                </div>
                <button type="button" class="lp-slider-arrow" data-slider-next aria-label="Next screenshot">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <div class="lp-flow-steps">
                @foreach ($slides as $i => $slide)
                    <button type="button" class="lp-flow-step {{ $i === 0 ? 'is-active' : '' }}" data-slider-dot="{{ $i }}">
                        <span class="lp-flow-step-num">{{ $i + 1 }}</span> {{ $slide['title'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="lp-strip">
        <div class="lp-wrap lp-strip-inner">
            Trusted by study libraries, coaching centers and reading halls across the country
        </div>
    </section>

    {{-- Floor planner spotlight --}}
    <section id="floor-planner" class="lp-spotlight">
        <div class="lp-wrap lp-section">
            <div class="lp-spotlight-grid">
                <div class="lp-spotlight-copy reveal">
                    <span class="lp-pill"><span class="lp-pill-dot"></span> Our signature feature</span>
                    <h3>Design your library floor, seat by seat.</h3>
                    <p>Most library software stops at a student list. LibNextGen ships a full drag-and-drop floor planner — lay out walls, entry gates, reception desks and individual seats exactly like your real reading hall, then allocate students to seats and shifts straight from the canvas.</p>
                    <ul class="lp-spotlight-list">
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Drag-and-drop seats, walls, entry gates &amp; reception</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Allocate students to seats and shifts in real time</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Multiple floors per hall, per branch</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Import / export your layout as JSON anytime</li>
                    </ul>
                    <div class="lp-cta-row" style="justify-content:flex-start; margin-top: 1.75rem;">
                        <a href="{{ route('register') }}" class="btn btn-primary !h-10 px-5 lp-btn-lift">Try the floor planner free</a>
                    </div>
                </div>
                <div class="reveal reveal-delay-2">
                    <div class="lp-spotlight-shot">
                        <div class="lp-slider-bar">
                            <span class="lp-dot-win"></span>
                            <span class="lp-dot-win"></span>
                            <span class="lp-dot-win"></span>
                            <span class="lp-slider-url">https://libnextgen.alwaysdata.net/halls/1/floor-plan</span>
                        </div>
                        <img src="{{ asset('images/marketing/shot-3-floorplanner.png') }}" alt="LibNextGen floor planner designer with seats, reception and entry gate laid out">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Student management spotlight --}}
    <section id="students" class="lp-spotlight lp-spotlight-alt">
        <div class="lp-wrap lp-section">
            <div class="lp-spotlight-grid">
                <div class="reveal reveal-delay-2">
                    <div class="lp-spotlight-shot">
                        <div class="lp-slider-bar">
                            <span class="lp-dot-win"></span>
                            <span class="lp-dot-win"></span>
                            <span class="lp-dot-win"></span>
                            <span class="lp-slider-url">https://libnextgen.alwaysdata.net/students</span>
                        </div>
                        <img src="{{ asset('images/marketing/shot-5-students.png') }}" alt="LibNextGen student management screen listing enrolled students across branches">
                    </div>
                </div>
                <div class="lp-spotlight-copy reveal">
                    <span class="lp-pill"><span class="lp-pill-dot"></span> Student management</span>
                    <h3>Every student, every branch, one list.</h3>
                    <p>Enroll students in minutes with auto-generated codes, assign them to a branch, shift and seat, and track their status at a glance. Search, filter by branch or status, and export the full list whenever you need it.</p>
                    <ul class="lp-spotlight-list">
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Fast admissions with auto-generated student codes</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Search and filter by branch, shift or status</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Track fees, seat and attendance from one profile</li>
                        <li><svg class="lp-spotlight-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Archive and export your student list anytime</li>
                    </ul>
                    <div class="lp-cta-row" style="justify-content:flex-start; margin-top: 1.75rem;">
                        <a href="{{ route('register') }}" class="btn btn-primary !h-10 px-5 lp-btn-lift">Manage your students free</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="lp-wrap lp-section">
        <div class="lp-section-head reveal">
            <h2 class="lp-h2">Everything your library operations need</h2>
            <p>Replace spreadsheets and registers with one connected system your whole team can use.</p>
        </div>

        <div class="lp-grid">
            @php
                $features = [
                    ['icon' => '🏬', 'title' => 'Multi-branch control', 'desc' => 'Manage every branch, hall and seat layout from a single owner dashboard.'],
                    ['icon' => '🧑‍🎓', 'title' => 'Student admissions', 'desc' => 'Enroll students in minutes with codes, shifts, seats and document capture.'],
                    ['icon' => '💺', 'title' => 'Drag-and-drop floor planner', 'desc' => 'Visual floor plans to design halls and assign, swap and track seats across shifts in real time.'],
                    ['icon' => '🧾', 'title' => 'Fee invoicing & receipts', 'desc' => 'Automated invoices, partial payments, overdue tracking and printable receipts.'],
                    ['icon' => '🕒', 'title' => 'Attendance tracking', 'desc' => 'Student check-in/check-out with live attendance across branches and shifts.'],
                    ['icon' => '🔐', 'title' => 'Roles & permissions', 'desc' => 'Custom staff roles for librarians, accountants and front-desk teams.'],
                ];
            @endphp
            @foreach ($features as $i => $f)
                <div class="card p-6 lp-feature reveal reveal-delay-{{ ($i % 3) + 1 }}">
                    <div class="lp-feature-icon">{{ $f['icon'] }}</div>
                    <h3>{{ $f['title'] }}</h3>
                    <p>{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="lp-strip">
        <div class="lp-wrap lp-section">
            <div class="lp-section-head reveal">
                <h2 class="lp-h2">Simple, transparent pricing</h2>
                <p>Start free for 14 days. Upgrade as you add branches and students.</p>
            </div>

            <div class="lp-pricing-grid">
                @forelse ($plans as $i => $plan)
                    @php $highlight = $plan->id === $mostPopularId; @endphp
                    <div class="card p-6 lp-plan reveal reveal-delay-{{ $i + 1 }} {{ $highlight ? 'is-highlight' : '' }}">
                        @if ($highlight)
                            <span class="lp-plan-badge">Most popular</span>
                        @endif
                        <h3>{{ $plan->name }}</h3>
                        <p class="lp-price">
                            <span class="lp-price-num">₹{{ number_format($plan->price) }}</span>
                            <span class="lp-price-cycle">/month</span>
                        </p>
                        <ul class="lp-plan-features">
                            @foreach ($plan->features ?? [] as $feat)
                                <li>
                                    <svg class="lp-check" fill="none" viewBox="0 0 20 20"><path d="M5 10l3.5 3.5L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="btn {{ $highlight ? 'btn-primary' : '' }} w-full mt-7 !h-10 lp-btn-lift">Start free trial</a>
                    </div>
                @empty
                    <p class="text-center text-muted" style="grid-column: 1 / -1;">Pricing plans are being updated — check back shortly.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="lp-wrap lp-section">
        <div class="lp-section-head reveal">
            <h2 class="lp-h2">Frequently asked questions</h2>
            <p>Everything you need to know before you start your free trial.</p>
        </div>

        <div class="lp-faq-list">
            @php
                $faqs = [
                    ['q' => 'Is there a free trial?', 'a' => 'Yes — every plan starts with a 14-day free trial, no credit card required. You can cancel anytime during the trial with nothing charged.'],
                    ['q' => 'How does the floor planner work?', 'a' => 'Open any hall\'s floor planner, then drag seats, walls, entry gates, reception desks and more onto a grid canvas. Save your layout, allocate students to specific seats and shifts, and export or import the layout as JSON whenever you need.'],
                    ['q' => 'Can I manage multiple branches?', 'a' => 'Yes. LibNextGen is built for multi-branch libraries — add unlimited halls per branch (plan limits apply to branch count) and manage students, staff, fees and attendance for every branch from one owner dashboard.'],
                    ['q' => 'Can I switch plans later?', 'a' => 'Absolutely — upgrade or downgrade anytime as your number of branches and students changes. Billing is prorated automatically.'],
                    ['q' => 'Is my data secure?', 'a' => 'Each library\'s data is isolated by tenant, access is controlled with role-based permissions for owners, librarians and accountants, and regular backups keep your records safe.'],
                ];
            @endphp
            @foreach ($faqs as $i => $faq)
                <div class="lp-faq-item reveal reveal-delay-{{ ($i % 5) + 1 }}" data-faq-item>
                    <button type="button" class="lp-faq-trigger" data-faq-trigger aria-expanded="false">
                        <span>{{ $faq['q'] }}</span>
                        <svg class="lp-faq-chevron" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="lp-faq-panel" data-faq-panel>
                        <div class="lp-faq-panel-inner">{{ $faq['a'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="lp-wrap lp-section lp-cta reveal">
        <h2 class="lp-h2">Bring your library online in a day</h2>
        <p>No setup fees, no long contracts. Import your students and start collecting fees digitally today.</p>
        <div class="lp-cta-row">
            <a href="{{ route('register') }}" class="btn btn-primary !h-10 px-5 lp-btn-lift">Create your library account</a>
            <a href="{{ route('login') }}" class="btn !h-10 px-5 lp-btn-lift">Log in</a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="lp-footer">
        <div class="lp-footer-banner"></div>
        <div class="lp-wrap lp-footer-body">
            <div class="lp-footer-top">
                <div>
                    <div class="lp-footer-brand">
                        <span class="lp-footer-mark"><x-brand-icon class="" style="width:1.1rem;height:1.1rem" /></span>
                        <span class="lp-footer-brand-name">LibNextGen</span>
                    </div>
                    <p class="lp-footer-tagline">Admissions. Seats. Fees. Simplified.</p>
                </div>
                <a href="{{ route('login') }}" class="lp-footer-open">
                    Open application
                    <svg width="13" height="13" viewBox="0 0 20 20" fill="none"><path d="M6 14L14 6M14 6H7M14 6V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            <div class="lp-footer-card">
                <p class="lp-footer-credit">Crafted with <span class="lp-footer-heart">❤</span> and passion by <b>Divyesh Panchal</b></p>
                <div class="lp-footer-links">
                    <a href="https://www.linkedin.com/in/divyeshhpanchal/" class="lp-footer-link" target="_blank" rel="noopener">LinkedIn
                        <svg width="11" height="11" viewBox="0 0 20 20" fill="none"><path d="M6 14L14 6M14 6H7M14 6V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <a href="https://divyesh-panchal.vercel.app/" class="lp-footer-link" target="_blank" rel="noopener">Portfolio
                        <svg width="11" height="11" viewBox="0 0 20 20" fill="none"><path d="M6 14L14 6M14 6H7M14 6V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <a href="tel:+919157762508" class="lp-footer-link">Contact
                        <svg width="11" height="11" viewBox="0 0 20 20" fill="none"><path d="M6 14L14 6M14 6H7M14 6V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>

            <div class="lp-footer-bottom">
                <p>© 2026 LibNextGen. All rights reserved.</p>
                <p>Built for libraries. Designed with <span class="lp-footer-heart">❤</span> privacy in mind.</p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            // Scroll-reveal
            var revealEls = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                revealEls.forEach(function (el) { io.observe(el); });
            } else {
                revealEls.forEach(function (el) { el.classList.add('is-visible'); });
            }

            // Mobile nav toggle
            var navToggle = document.querySelector('[data-nav-toggle]');
            var navMobile = document.querySelector('[data-nav-mobile]');
            if (navToggle && navMobile) {
                var iconOpen = navToggle.querySelector('[data-nav-icon-open]');
                var iconClose = navToggle.querySelector('[data-nav-icon-close]');
                var closeMenu = function () {
                    navMobile.classList.remove('is-open');
                    navToggle.setAttribute('aria-expanded', 'false');
                    if (iconOpen) iconOpen.style.display = '';
                    if (iconClose) iconClose.style.display = 'none';
                };
                navToggle.addEventListener('click', function () {
                    var isOpen = navMobile.classList.toggle('is-open');
                    navToggle.setAttribute('aria-expanded', String(isOpen));
                    if (iconOpen) iconOpen.style.display = isOpen ? 'none' : '';
                    if (iconClose) iconClose.style.display = isOpen ? '' : 'none';
                });
                navMobile.querySelectorAll('[data-nav-close]').forEach(function (link) {
                    link.addEventListener('click', closeMenu);
                });
            }

            // Animated stat counters
            var statEls = document.querySelectorAll('[data-count]');
            if (statEls.length && 'IntersectionObserver' in window) {
                var countIo = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        countIo.unobserve(entry.target);
                        var el = entry.target;
                        var target = parseFloat(el.getAttribute('data-count'));
                        var suffix = el.getAttribute('data-count-suffix') || '';
                        var duration = 1200;
                        var startTime = null;
                        function tick(ts) {
                            if (!startTime) startTime = ts;
                            var progress = Math.min((ts - startTime) / duration, 1);
                            var eased = 1 - Math.pow(1 - progress, 3);
                            var value = Math.round(target * eased);
                            el.textContent = value.toLocaleString('en-IN') + suffix;
                            if (progress < 1) requestAnimationFrame(tick);
                        }
                        requestAnimationFrame(tick);
                    });
                }, { threshold: 0.4 });
                statEls.forEach(function (el) { countIo.observe(el); });
            }

            // FAQ accordion
            document.querySelectorAll('[data-faq-item]').forEach(function (item) {
                var trigger = item.querySelector('[data-faq-trigger]');
                var panel = item.querySelector('[data-faq-panel]');
                if (!trigger || !panel) return;
                trigger.addEventListener('click', function () {
                    var isOpen = item.classList.toggle('is-open');
                    trigger.setAttribute('aria-expanded', String(isOpen));
                });
            });

            // Screenshot slider
            var root = document.querySelector('[data-slider]');
            if (root) {
                var wrap = root.closest('.lp-slider-wrap');
                var slides = Array.prototype.slice.call(wrap.querySelectorAll('[data-slide]'));
                var dots = Array.prototype.slice.call(wrap.querySelectorAll('[data-slider-dot]'));
                var urlEl = wrap.querySelector('[data-slider-url]');
                var current = 0;
                var timer = null;

                var show = function (index) {
                    current = (index + slides.length) % slides.length;
                    slides.forEach(function (s, i) { s.classList.toggle('is-active', i === current); });
                    dots.forEach(function (d) { d.classList.toggle('is-active', Number(d.getAttribute('data-slider-dot')) === current); });
                    if (urlEl) urlEl.textContent = slides[current].getAttribute('data-url');
                };

                var next = function () { show(current + 1); };
                var prev = function () { show(current - 1); };

                var start = function () { timer = setInterval(next, 4000); };
                var stop = function () { if (timer) clearInterval(timer); };

                dots.forEach(function (d) {
                    d.addEventListener('click', function () {
                        show(Number(d.getAttribute('data-slider-dot')));
                        stop(); start();
                    });
                });

                var nextBtn = wrap.querySelector('[data-slider-next]');
                var prevBtn = wrap.querySelector('[data-slider-prev]');
                if (nextBtn) nextBtn.addEventListener('click', function () { next(); stop(); start(); });
                if (prevBtn) prevBtn.addEventListener('click', function () { prev(); stop(); start(); });

                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', start);

                show(0);
                start();
            }
        })();
    </script>

</body>
</html>
