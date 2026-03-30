<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MatchDay — Live Football Scores')</title>
    <meta name="description" content="Live scores, results and upcoming fixtures for football matches worldwide.">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    <style>
        :root {
            --bg-primary:     #0a0a0f;
            --bg-secondary:   #111118;
            --bg-card:        #16161f;
            --bg-card-hover:  #1d1d28;
            --bg-elevated:    #1e1e2a;
            --accent:         #00e5ff;
            --accent-dim:     rgba(0,229,255,.12);
            --accent-glow:    rgba(0,229,255,.25);
            --green:          #00d084;
            --green-dim:      rgba(0,208,132,.12);
            --amber:          #ffb800;
            --amber-dim:      rgba(255,184,0,.12);
            --red:            #ff4757;
            --red-dim:        rgba(255,71,87,.12);
            --text-primary:   #f0f0f8;
            --text-secondary: #8888aa;
            --text-muted:     #55556a;
            --border:         rgba(255,255,255,.06);
            --border-hover:   rgba(255,255,255,.12);
            --radius-sm:      6px;
            --radius-md:      10px;
            --radius-lg:      16px;
            --radius-xl:      24px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-primary); }
        ::-webkit-scrollbar-thumb { background: var(--bg-elevated); border-radius: 3px; }

        /* ─── Header ─── */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(10,10,15,.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-icon svg { color: #000; }

        .logo-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 22px;
            letter-spacing: 1px;
            color: var(--text-primary);
        }

        .logo-text span { color: var(--accent); }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .header-nav a {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all .15s;
        }

        .header-nav a:hover { color: var(--text-primary); background: var(--bg-elevated); }
        .header-nav a.active { color: var(--accent); background: var(--accent-dim); }

        .live-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: var(--red-dim);
            border: 1px solid rgba(255,71,87,.25);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--red);
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background: var(--red);
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(.7); }
        }

        /* ─── Main ─── */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* ─── Hero ─── */
        .hero {
            margin-bottom: 2rem;
        }

        .hero-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(36px, 6vw, 64px);
            line-height: 1;
            letter-spacing: 1px;
            color: var(--text-primary);
            margin-bottom: .5rem;
        }

        .hero-title span { color: var(--accent); }

        .hero-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* ─── Stats Row ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: border-color .2s;
        }

        .stat-card:hover { border-color: var(--border-hover); }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon.total   { background: var(--accent-dim); color: var(--accent); }
        .stat-icon.live    { background: var(--red-dim); color: var(--red); }
        .stat-icon.done    { background: var(--green-dim); color: var(--green); }
        .stat-icon.soon    { background: var(--amber-dim); color: var(--amber); }

        .stat-label { font-size: 11px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--text-primary); line-height: 1; }

        /* ─── Tabs ─── */
        .tabs-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 1.5rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 6px;
            width: fit-content;
        }

        .tab-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-md);
            background: transparent;
            cursor: pointer;
            color: var(--text-secondary);
            font-family: inherit;
            text-decoration: none;
            transition: all .2s;
        }

        .tab-btn:hover { color: var(--text-primary); background: var(--bg-elevated); }

        .tab-btn.active {
            background: var(--accent);
            color: #000;
        }

        .tab-btn .tab-day  { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .tab-btn .tab-date { font-size: 12px; font-weight: 500; opacity: .7; }

        /* ─── League Section ─── */
        .league-section {
            margin-bottom: 1.5rem;
        }

        .league-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: .8rem 1.1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            cursor: pointer;
            user-select: none;
            transition: background .15s;
        }

        .league-header:hover { background: var(--bg-card-hover); }

        .league-logo {
            width: 24px;
            height: 24px;
            object-fit: contain;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,.5));
        }

        .league-flag {
            width: 18px;
            height: 13px;
            object-fit: cover;
            border-radius: 2px;
        }

        .league-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
        .league-country { font-size: 11px; color: var(--text-muted); }
        .league-round { font-size: 11px; color: var(--text-muted); margin-left: auto; }
        .league-count {
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
        }

        .toggle-icon { color: var(--text-muted); transition: transform .25s; flex-shrink: 0; }
        .league-header.collapsed .toggle-icon { transform: rotate(-90deg); }

        /* ─── Match Row ─── */
        .matches-list {
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            overflow: hidden;
        }

        .match-row {
            display: grid;
            grid-template-columns: 80px 1fr auto 1fr 80px;
            align-items: center;
            gap: 1rem;
            padding: .9rem 1.1rem;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
            transition: background .15s;
        }

        .match-row:last-child { border-bottom: none; }
        .match-row:hover { background: var(--bg-card-hover); }

        /* Time / Status */
        .match-time-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .match-time {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        .status-badge.ns      { background: var(--bg-elevated); color: var(--text-muted); }
        .status-badge.live    { background: var(--red-dim); color: var(--red); border: 1px solid rgba(255,71,87,.3); animation: pulse-border 2s infinite; }
        .status-badge.ht      { background: var(--amber-dim); color: var(--amber); }
        .status-badge.ft      { background: var(--green-dim); color: var(--green); }
        .status-badge.pst, .status-badge.canc { background: var(--bg-elevated); color: var(--text-muted); }

        @keyframes pulse-border {
            0%,100% { border-color: rgba(255,71,87,.3); }
            50%      { border-color: rgba(255,71,87,.7); }
        }

        /* Team */
        .team-col {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .team-col.away { justify-content: flex-end; flex-direction: row-reverse; }

        .team-logo {
            width: 28px;
            height: 28px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,.4));
        }

        .team-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .team-name.winner { color: var(--green); font-weight: 600; }

        /* Score */
        .score-col {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .score-box {
            min-width: 36px;
            height: 36px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: .5px;
        }

        .score-box.live { border-color: rgba(255,71,87,.3); color: var(--red); }
        .score-box.ft   { border-color: var(--border-hover); }

        .score-sep {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 300;
        }

        /* Elapsed */
        .elapsed-badge {
            font-size: 10px;
            font-weight: 600;
            color: var(--red);
            background: var(--red-dim);
            padding: 1px 5px;
            border-radius: 3px;
        }

        /* ─── Empty state ─── */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--text-muted);
        }

        .empty-state svg { opacity: .3; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 16px; font-weight: 500; color: var(--text-secondary); margin-bottom: .5rem; }
        .empty-state p { font-size: 14px; }

        /* ─── Footer ─── */
        .site-footer {
            border-top: 1px solid var(--border);
            padding: 2rem 1.5rem;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        .site-footer a { color: var(--accent); text-decoration: none; }
        .site-footer a:hover { text-decoration: underline; }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .header-nav { display: none; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .match-row {
                grid-template-columns: 64px 1fr auto 1fr 64px;
                gap: .6rem;
                padding: .75rem .75rem;
            }
            .team-name { font-size: 12px; }
            .score-box { min-width: 30px; height: 30px; font-size: 15px; }
            .tabs-wrapper { width: 100%; }
            .tab-btn { flex: 1; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .hero-title { font-size: 36px; }
            .match-row { grid-template-columns: 56px 1fr auto 1fr 56px; gap: .4rem; }
            .team-logo { width: 22px; height: 22px; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ─── HEADER ─── --}}
<header class="site-header">
    <div class="header-inner">
        <a href="{{ route('matches.index') }}" class="logo">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8l2.5 5H9.5L12 8z"/>
                    <line x1="12" y1="2" x2="12" y2="4"/>
                    <line x1="12" y1="20" x2="12" y2="22"/>
                </svg>
            </div>
            <span class="logo-text">Match<span>Day</span></span>
        </a>

        <nav class="header-nav">
            <a href="{{ route('matches.index', ['tab' => 'yesterday']) }}" class="{{ request('tab') === 'yesterday' ? 'active' : '' }}">Yesterday</a>
            <a href="{{ route('matches.index') }}"                         class="{{ !request('tab') || request('tab') === 'today' ? 'active' : '' }}">Today</a>
            <a href="{{ route('matches.index', ['tab' => 'tomorrow']) }}"  class="{{ request('tab') === 'tomorrow' ? 'active' : '' }}">Tomorrow</a>
        </nav>

        <div class="live-badge">
            <span class="live-dot"></span> Live
        </div>
    </div>
</header>

{{-- ─── CONTENT ─── --}}
<main>
    @yield('content')
</main>

{{-- ─── FOOTER ─── --}}
<footer class="site-footer">
    <p>Powered by <a href="https://dashboard.api-football.com" target="_blank">API-Football</a> &nbsp;·&nbsp; Created By &copy;Maged3mer&nbsp;·&nbsp; &copy; {{ date('Y') }} MatchDay</p>
</footer>

<script>
    // Init Lucide icons
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();

        // Collapsible league sections
        document.querySelectorAll('.league-header').forEach(header => {
            header.addEventListener('click', () => {
                header.classList.toggle('collapsed');
                const list = header.nextElementSibling;
                if (list) {
                    list.style.display = header.classList.contains('collapsed') ? 'none' : '';
                }
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>