@extends('layouts.app')

@section('title', $match['home']['name'] . ' vs ' . $match['away']['name'] . ' — MatchDay')

@push('styles')
<style>
    .match-detail-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 1.5rem 4rem;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-secondary);
        text-decoration: none;
        margin-bottom: 1.5rem;
        transition: color .15s;
    }
    .back-link:hover { color: var(--accent); }

    .match-hero-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 2.5rem 2rem;
        margin-bottom: 1.5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .match-hero-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--accent);
    }

    .match-hero-card.live-match::before {
        background: var(--red);
        animation: pulse-bar 2s infinite;
    }

    @keyframes pulse-bar {
        0%,100% { opacity: 1; }
        50%      { opacity: .4; }
    }

    .match-league-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 2rem;
        font-size: 12px;
        color: var(--text-muted);
    }

    .match-league-info img { width: 18px; height: 18px; object-fit: contain; }

    .teams-score-area {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detail-team {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .detail-team-logo {
        width: 72px;
        height: 72px;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,.5));
    }

    .detail-team-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        text-align: center;
    }

    .detail-score-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .detail-score {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 64px;
        line-height: 1;
        letter-spacing: 4px;
        color: var(--text-primary);
    }

    .detail-score.live { color: var(--red); }

    .detail-status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .detail-elapsed {
        font-size: 12px;
        color: var(--red);
        font-weight: 600;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .info-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.1rem 1.25rem;
    }

    .info-card-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: .75rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .4rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: var(--text-secondary); }
    .info-row .value { color: var(--text-primary); font-weight: 500; }
</style>
@endpush

@section('content')
<div class="match-detail-container">

    <a href="{{ url()->previous(route('matches.index')) }}" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to matches
    </a>

    @php
        $statusClass = match($match['status']) {
            'LIVE','1H','2H','ET','P' => 'live',
            'HT'                      => 'ht',
            'FT','AET','PEN'          => 'ft',
            default                   => 'ns',
        };
        $isLive     = $statusClass === 'live';
        $isFinished = $statusClass === 'ft';
        $showScore  = $isLive || $isFinished || $match['status'] === 'HT';
    @endphp

    {{-- Main Match Card --}}
    <div class="match-hero-card {{ $isLive ? 'live-match' : '' }}">

        <div class="match-league-info">
            @if(!empty($match['league']['logo']))
                <img src="{{ $match['league']['logo'] }}" alt="{{ $match['league']['name'] }}" loading="lazy">
            @endif
            {{ $match['league']['name'] }}
            @if(!empty($match['league']['country']))
                · {{ $match['league']['country'] }}
            @endif
            @if(!empty($match['league']['round']))
                · {{ $match['league']['round'] }}
            @endif
        </div>

        <div class="teams-score-area">

            {{-- Home --}}
            <div class="detail-team">
                @if(!empty($match['home']['logo']))
                    <img src="{{ $match['home']['logo'] }}" alt="{{ $match['home']['name'] }}"
                         class="detail-team-logo" loading="lazy" onerror="this.style.display='none'">
                @endif
                <div class="detail-team-name">{{ $match['home']['name'] }}</div>
            </div>

            {{-- Score --}}
            <div class="detail-score-area">
                @if($showScore)
                    <div class="detail-score {{ $isLive ? 'live' : '' }}">
                        {{ $match['score']['home'] ?? 0 }} : {{ $match['score']['away'] ?? 0 }}
                    </div>
                @else
                    <div class="detail-score" style="color: var(--text-muted);">
                        {{ $match['time'] }}
                    </div>
                @endif

                <span class="detail-status-badge status-badge {{ $statusClass }}">
                    @if($isLive && $match['elapsed'])
                        {{ $match['elapsed'] }}'
                    @else
                        {{ $match['status_long'] }}
                    @endif
                </span>

                @if($isLive && $match['elapsed'])
                    <span class="detail-elapsed">{{ $match['elapsed'] }}' played</span>
                @endif
            </div>

            {{-- Away --}}
            <div class="detail-team">
                @if(!empty($match['away']['logo']))
                    <img src="{{ $match['away']['logo'] }}" alt="{{ $match['away']['name'] }}"
                         class="detail-team-logo" loading="lazy" onerror="this.style.display='none'">
                @endif
                <div class="detail-team-name">{{ $match['away']['name'] }}</div>
            </div>

        </div>

    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="info-card-title">Match Info</div>
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">
                    {{ $match['date'] ? \Carbon\Carbon::parse($match['date'])->format('d M Y') : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="label">Kick-off</span>
                <span class="value">{{ $match['time'] }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status</span>
                <span class="value">{{ $match['status_long'] }}</span>
            </div>
            @if(!empty($match['venue']))
            <div class="info-row">
                <span class="label">Venue</span>
                <span class="value">{{ $match['venue'] }}</span>
            </div>
            @endif
        </div>

        <div class="info-card">
            <div class="info-card-title">Score Summary</div>
            <div class="info-row">
                <span class="label">Full Time</span>
                <span class="value">{{ $match['score']['fulltime'] !== '-' ? $match['score']['fulltime'] : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Half Time</span>
                <span class="value">{{ $match['score']['halftime'] !== '-' ? $match['score']['halftime'] : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Home</span>
                <span class="value">{{ $match['home']['name'] }}</span>
            </div>
            <div class="info-row">
                <span class="label">Away</span>
                <span class="value">{{ $match['away']['name'] }}</span>
            </div>
        </div>
    </div>

</div>
@endsection