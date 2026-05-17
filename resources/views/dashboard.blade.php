@extends('partials.layout')
@section('title', 'Admin Dashboard')
@section('container')
    <style>
        /* ================ Layout ================ */
        .dashboard-wrapper {
            padding: 2rem 1.5rem 3rem;
            position: relative;
            min-height: calc(100vh - 80px);
        }

        .dashboard-wrapper::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(800px circle at 0% 0%, rgba(118, 75, 162, .06), transparent 50%),
                radial-gradient(600px circle at 100% 100%, rgba(56, 239, 125, .05), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .dashboard-wrapper > * { position: relative; z-index: 1; }

        /* ================ Header ================ */
        .dashboard-heading {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;
            animation: fadeInDown .6s ease both;
        }
        .dashboard-heading h1 {
            font-weight: 700; color: #2c3e50; font-size: 1.75rem; margin: 0;
            display: flex; align-items: center; gap: .65rem;
            letter-spacing: -.02em;
        }
        .dashboard-heading h1 .folder-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff; font-size: 1.2rem;
            box-shadow: 0 8px 20px rgba(78, 115, 223, .35);
        }
        .dashboard-subtitle {
            color: #7f8c8d; font-size: .95rem; margin: -1.25rem 0 2rem;
            animation: fadeInDown .7s ease .1s both;
        }

        .back-link {
            display: inline-flex; align-items: center; gap: .4rem;
            color: #6c757d; text-decoration: none; font-size: .9rem;
            padding: .5rem 1rem; background: #fff; border: 1px solid #e3e6f0;
            border-radius: 2rem; transition: all .25s ease;
            font-weight: 500;
        }
        .back-link:hover {
            color: #fff; background: #4e73df; border-color: #4e73df;
            transform: translateX(-3px);
            box-shadow: 0 6px 18px rgba(78, 115, 223, .35);
        }

        /* ================ Section layout ================ */
        .section-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.75rem; margin-bottom: 1rem; }
        @media (max-width: 992px) { .section-row { grid-template-columns: 1fr; } }

        .section-block {
            margin-bottom: 1.75rem;
            animation: fadeInUp .7s ease both;
        }

        .section-title {
            display: flex; align-items: center; gap: .55rem;
            color: #2c3e50; font-size: 1.05rem; font-weight: 700;
            margin-bottom: 1rem; padding-left: .25rem;
        }
        .section-title .section-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: linear-gradient(135deg, #4e73df, #6f42c1);
            box-shadow: 0 0 0 4px rgba(78, 115, 223, .15);
        }
        .section-title i { color: #6c757d; }

        .card-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
        .card-grid.one { grid-template-columns: 1fr; max-width: 50%; }
        @media (max-width: 768px) {
            .card-grid { grid-template-columns: 1fr; }
            .card-grid.one { max-width: 100%; }
        }

        /* ================ Module tile grid (group → modules) ================ */
        .module-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
        }
        @media (max-width: 992px) { .module-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .module-grid { grid-template-columns: 1fr; } }

        .module-tile {
            position: relative; overflow: hidden;
            border-radius: 20px; padding: 1.6rem 1.4rem;
            color: #fff; text-decoration: none;
            display: flex; flex-direction: column; gap: .65rem;
            min-height: 180px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15), 0 2px 4px rgba(0, 0, 0, .06);
            transition: transform .35s cubic-bezier(.2, .8, .2, 1),
                        box-shadow .35s cubic-bezier(.2, .8, .2, 1);
            isolation: isolate;
            animation: cardPopIn .55s cubic-bezier(.2, .8, .2, 1) both;
        }
        .module-tile:hover {
            transform: translateY(-6px) scale(1.015);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .25);
            color: #fff; text-decoration: none;
        }
        .module-tile::before,
        .module-tile::after {
            content: ''; position: absolute; border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            transition: transform .8s ease;
            pointer-events: none;
        }
        .module-tile::after { width: 180px; height: 180px; top: -60px; right: -60px; }
        .module-tile::before { width: 120px; height: 120px; bottom: -40px; left: -40px; background: rgba(255, 255, 255, .08); }
        .module-tile:hover::after { transform: translate(-15px, 15px) scale(1.1); }
        .module-tile:hover::before { transform: translate(20px, -15px) scale(1.05); }

        .module-tile .shine {
            position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.18) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .9s ease;
            pointer-events: none;
        }
        .module-tile:hover .shine { transform: translateX(100%); }

        .module-tile .mt-inner { position: relative; z-index: 2; display: flex; flex-direction: column; gap: .55rem; }
        .module-tile .mt-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(255, 255, 255, .25);
            backdrop-filter: blur(4px);
            color: #fff; font-size: 1.4rem;
            box-shadow: 0 0 0 5px rgba(255, 255, 255, .07);
        }
        .module-tile .mt-title { font-weight: 800; font-size: 1.1rem; letter-spacing: -.01em; }
        .module-tile .mt-desc  { font-size: .85rem; opacity: .92; line-height: 1.45; }
        .module-tile .mt-cta {
            margin-top: auto;
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255, 255, 255, .22);
            padding: .4rem 1rem; border-radius: 2rem;
            font-weight: 600; font-size: .82rem;
            align-self: flex-start;
            border: 1px solid rgba(255, 255, 255, .15);
            transition: all .25s ease;
        }
        .module-tile:hover .mt-cta { background: rgba(255, 255, 255, .4); }
        .module-tile .mt-cta i { transition: transform .25s ease; }
        .module-tile:hover .mt-cta i { transform: translateX(3px); }

        /* ================ Count cards ================ */
        .count-card {
            position: relative; overflow: hidden;
            border-radius: 20px; padding: 1.75rem 1.5rem 1.5rem;
            color: #fff; text-decoration: none;
            display: flex; flex-direction: column; align-items: center;
            text-align: center; min-height: 230px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15), 0 2px 4px rgba(0, 0, 0, .06);
            transition: transform .35s cubic-bezier(.2, .8, .2, 1),
                        box-shadow .35s cubic-bezier(.2, .8, .2, 1);
            isolation: isolate;
            animation: cardPopIn .55s cubic-bezier(.2, .8, .2, 1) both;
        }
        .count-card:hover {
            transform: translateY(-8px) scale(1.015);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .25);
            color: #fff; text-decoration: none;
        }
        .count-card::before,
        .count-card::after {
            content: ''; position: absolute; border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            transition: transform .8s ease;
            pointer-events: none;
        }
        .count-card::after { width: 200px; height: 200px; top: -70px; right: -70px; }
        .count-card::before { width: 140px; height: 140px; bottom: -50px; left: -50px; background: rgba(255, 255, 255, .08); }
        .count-card:hover::after  { transform: translate(-20px, 20px) scale(1.15); }
        .count-card:hover::before { transform: translate(30px, -20px) scale(1.1); }

        .count-card .shine {
            position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.18) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform .9s ease;
            pointer-events: none;
        }
        .count-card:hover .shine { transform: translateX(100%); }

        .count-card .cc-inner {
            position: relative; z-index: 2; width: 100%;
            display: flex; flex-direction: column; align-items: center;
        }
        .count-card .cc-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 64px; height: 64px; border-radius: 50%;
            background: rgba(255, 255, 255, .25);
            backdrop-filter: blur(4px);
            color: #fff; font-size: 1.7rem; margin-bottom: .9rem;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, .08);
            transition: transform .4s cubic-bezier(.2, .8, .2, 1);
        }
        .count-card:hover .cc-icon { transform: scale(1.1) rotate(-6deg); }

        .count-card .cc-number {
            font-size: 2.75rem; font-weight: 800; line-height: 1;
            margin-bottom: .4rem; letter-spacing: -.03em;
            text-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        }
        .count-card .cc-title {
            text-transform: uppercase; font-weight: 700; font-size: .9rem;
            letter-spacing: .04em; margin-bottom: 1.1rem; opacity: .95;
        }
        .count-card .cc-cta {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255, 255, 255, .22); padding: .45rem 1.1rem;
            border-radius: 2rem; font-weight: 600; font-size: .85rem;
            transition: all .25s ease;
            border: 1px solid rgba(255, 255, 255, .15);
        }
        .count-card:hover .cc-cta { background: rgba(255, 255, 255, .4); transform: translateX(2px); }
        .count-card .cc-cta i { transition: transform .25s ease; }
        .count-card:hover .cc-cta i { transform: translateX(3px); }

        /* ================ Gradient palette ================ */
        .bg-grad-1  { background: linear-gradient(135deg, #ff5858 0%, #f857a6 100%); }
        .bg-grad-2  { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
        .bg-grad-3  { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); }
        .bg-grad-4  { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
        .bg-grad-5  { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
        .bg-grad-6  { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-grad-7  { background: linear-gradient(135deg, #614385 0%, #516395 100%); }
        .bg-grad-8  { background: linear-gradient(135deg, #f7b733 0%, #fc4a1a 100%); }
        .bg-grad-9  { background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); }
        .bg-grad-10 { background: linear-gradient(135deg, #4776e6 0%, #8e54e9 100%); }

        /* ================ Group chooser ================ */
        .chooser-wrapper {
            min-height: calc(100vh - 200px);
            display: flex; flex-direction: column; justify-content: center;
            padding-top: 2rem;
        }
        .chooser-heading {
            text-align: center; margin-bottom: 2.5rem;
            animation: fadeInDown .6s ease both;
        }
        .chooser-heading h1 {
            font-weight: 800; color: #2c3e50; font-size: 2.2rem; margin: 0 0 .5rem;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #4e73df, #6f42c1);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .chooser-heading p { color: #7f8c8d; font-size: 1rem; }

        .group-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;
            max-width: 980px; margin: 0 auto;
        }
        @media (max-width: 768px) { .group-grid { grid-template-columns: 1fr; } }

        .group-card {
            border-radius: 28px; padding: 3rem 2.25rem; color: #fff;
            text-decoration: none; position: relative; overflow: hidden;
            box-shadow: 0 14px 36px rgba(0, 0, 0, .14);
            transition: transform .4s cubic-bezier(.2, .8, .2, 1),
                        box-shadow .4s cubic-bezier(.2, .8, .2, 1);
            display: block;
            isolation: isolate;
            animation: cardPopIn .7s cubic-bezier(.2, .8, .2, 1) both;
        }
        .group-card:nth-child(1) { animation-delay: .1s; }
        .group-card:nth-child(2) { animation-delay: .2s; }
        .group-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 28px 56px rgba(0, 0, 0, .25);
            color: #fff;
        }
        .group-card.baby { background: linear-gradient(135deg, #ff6b9d 0%, #c06aef 100%); }
        .group-card.ngd  { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }

        .group-card::after {
            content: ''; position: absolute;
            width: 280px; height: 280px; border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            top: -80px; right: -80px;
            transition: transform .8s ease;
        }
        .group-card::before {
            content: ''; position: absolute;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            bottom: -60px; left: -60px;
            transition: transform .8s ease;
        }
        .group-card:hover::after  { transform: translate(-30px, 30px) scale(1.1); }
        .group-card:hover::before { transform: translate(40px, -30px) scale(1.05); }

        .group-card-inner { position: relative; z-index: 1; }
        .group-icon {
            font-size: 3.5rem; margin-bottom: 1rem; opacity: .95;
            display: inline-block;
            animation: floaty 4s ease-in-out infinite;
            text-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        .group-card.ngd .group-icon { animation-delay: 1.5s; }
        .group-title {
            font-size: 2.1rem; font-weight: 800; margin-bottom: .5rem;
            letter-spacing: -.02em;
        }
        .group-subtitle {
            font-size: .95rem; opacity: .92; margin-bottom: 1.5rem;
            line-height: 1.55;
        }
        .module-pill {
            display: inline-block; background: rgba(255, 255, 255, .22);
            padding: .35rem .8rem; border-radius: 1rem; margin: .25rem .15rem;
            font-size: .75rem; font-weight: 600;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, .12);
        }
        .group-cta {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255, 255, 255, .28); padding: .65rem 1.3rem;
            border-radius: 2rem; font-weight: 700; font-size: .9rem;
            margin-top: 1.25rem;
            border: 1px solid rgba(255, 255, 255, .2);
            transition: all .25s ease;
        }
        .group-card:hover .group-cta { background: rgba(255, 255, 255, .42); padding-right: 1.5rem; }
        .group-cta i { transition: transform .25s ease; }
        .group-card:hover .group-cta i { transform: translateX(4px); }

        /* ================ Animations ================ */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes cardPopIn {
            from { opacity: 0; transform: translateY(20px) scale(.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes floaty {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
    </style>

    <div class="dashboard-wrapper">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($view === 'chooser')
            {{-- ============ Group chooser ============ --}}
            <div class="chooser-wrapper">
                <div class="chooser-heading">
                    <h1>NGD Admin Dashboard</h1>
                    <p>Choose a module group to get started</p>
                </div>

                <div class="group-grid">
                    <a href="{{ route('dashboard.selectGroup', 'baby') }}" class="group-card baby">
                        <div class="group-card-inner">
                            <div class="group-icon"><i class="bi bi-heart-fill"></i></div>
                            <div class="group-title">AI Baby</div>
                            <div class="group-subtitle">Manage all Baby-focused AI modules for image, video, frames, and home screen sliders.</div>
                            <div>
                                <span class="module-pill">AI Baby Image</span>
                                <span class="module-pill">AI Baby Video</span>
                                <span class="module-pill">Dynamic Photo Frame</span>
                                <span class="module-pill">Home Screen Slider</span>
                                <span class="module-pill">Sticker</span>
                                <span class="module-pill">Font</span>
                                <span class="module-pill">Doodle</span>
                            </div>
                            <span class="group-cta">Open AI Baby <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>

                    <a href="{{ route('dashboard.selectGroup', 'ngd') }}" class="group-card ngd">
                        <div class="group-card-inner">
                            <div class="group-icon"><i class="bi bi-robot"></i></div>
                            <div class="group-title">AI NGD</div>
                            <div class="group-subtitle">Manage all NGD AI modules — image, video, filters, lips sync, and top sliders.</div>
                            <div>
                                <span class="module-pill">AI Image NGD</span>
                                <span class="module-pill">AI Video NGD</span>
                                <span class="module-pill">Filter AI Image</span>
                                <span class="module-pill">Top Slider</span>
                                <span class="module-pill">Lips Sync</span>
                            </div>
                            <span class="group-cta">Open AI NGD <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            </div>

        @elseif ($view === 'modules')
            {{-- ============ Group → Module tiles ============ --}}
            <div class="dashboard-heading">
                <h1>
                    <span class="folder-icon">
                        <i class="bi {{ $group === 'baby' ? 'bi-heart-fill' : 'bi-robot' }}"></i>
                    </span>
                    {{ $group === 'baby' ? 'AI Baby' : 'AI NGD' }} Dashboard
                </h1>
                <a class="back-link" href="{{ route('dashboard.clearGroup') }}">
                    <i class="bi bi-arrow-left"></i> Back to group selection
                </a>
            </div>
            <p class="dashboard-subtitle">Choose a module to open its dashboard</p>

            <div class="module-grid">
                @foreach ($modules as $i => $mod)
                    <a href="{{ route('dashboard.module', $mod['key']) }}"
                       class="module-tile {{ $mod['gradient'] }}"
                       style="animation-delay: {{ $i * 0.05 }}s">
                        <span class="shine"></span>
                        <div class="mt-inner">
                            <span class="mt-icon"><i class="bi {{ $mod['icon'] }}"></i></span>
                            <div class="mt-title">{{ $mod['title'] }}</div>
                            <div class="mt-desc">{{ $mod['description'] }}</div>
                            <span class="mt-cta">Open <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>

        @elseif ($view === 'module')
            {{-- ============ Module landing page ============ --}}
            <div class="dashboard-heading">
                <h1>
                    <span class="folder-icon"><i class="bi {{ $config['icon'] }}"></i></span>
                    {{ $config['title'] }}
                </h1>
                <a class="back-link" href="{{ route('dashboard') }}">
                    <i class="bi bi-arrow-left"></i> Back to modules
                </a>
            </div>
            <p class="dashboard-subtitle">{{ $config['subtitle'] }}</p>

            <div class="card-grid {{ count($config['cards']) === 1 ? 'one' : '' }}">
                @foreach ($config['cards'] as $i => $card)
                    <a href="{{ $card['route'] }}" class="count-card {{ $card['gradient'] }}" style="--i:{{ $i }}">
                        <span class="shine"></span>
                        <div class="cc-inner">
                            <span class="cc-icon"><i class="bi {{ $card['icon'] }}"></i></span>
                            <div class="cc-number" data-count="{{ $card['count'] }}">0</div>
                            <div class="cc-title">{{ $card['title'] }}</div>
                            <span class="cc-cta">View <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const easeOutQuart = t => 1 - Math.pow(1 - t, 4);

            function animateCount(el) {
                if (el.dataset.animated) return;
                el.dataset.animated = '1';
                const target = parseInt(el.dataset.count || '0', 10);
                if (!target) { el.textContent = '0'; return; }
                const duration = Math.min(1400, 600 + target * 8);
                const start = performance.now();

                function step(now) {
                    const t = Math.min(1, (now - start) / duration);
                    const value = Math.round(easeOutQuart(t) * target);
                    el.textContent = value.toLocaleString();
                    if (t < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }

            const counters = document.querySelectorAll('.cc-number[data-count]');
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateCount(entry.target);
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.3 });
                counters.forEach(el => io.observe(el));
            } else {
                counters.forEach(animateCount);
            }
        });
    </script>
@endsection
