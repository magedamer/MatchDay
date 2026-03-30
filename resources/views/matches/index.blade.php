@extends('layouts.app')

@section('title', 'MatchDay — ' . ucfirst($tab) . "'s Football Matches")

@section('content')
<div class="main-container">

    {{-- Hero --}}
    <div class="hero">
        <h1 class="hero-title">
            @if($tab === 'yesterday')
                <span>Yesterday's</span> Matches
            @elseif($tab === 'tomorrow')
                <span>Tomorrow's</span> Fixtures
            @else
                <span>Today's</span> Matches
            @endif
        </h1>
        <p class="hero-subtitle">
            {{ \Carbon\Carbon::parse($tab === 'yesterday' ? $yesterday : ($tab === 'tomorrow' ? $tomorrow : $today))->format('l, F j, Y') }}
            &nbsp;·&nbsp; {{ $stats['total'] }} matches across {{ count($leagues) }} competitions
        </p>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <div>
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon live">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            </div>
            <div>
                <div class="stat-label">Live</div>
                <div class="stat-value">{{ $stats['live'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon done">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <div>
                <div class="stat-label">Finished</div>
                <div class="stat-value">{{ $stats['finished'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon soon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <div class="stat-label">Upcoming</div>
                <div class="stat-value">{{ $stats['upcoming'] }}</div>
            </div>
        </div>
    </div>

    {{-- Date Tabs --}}
    <div class="tabs-wrapper">
        <a href="{{ route('matches.index', ['tab' => 'yesterday']) }}"
           class="tab-btn {{ $tab === 'yesterday' ? 'active' : '' }}">
            <span class="tab-day">Yesterday</span>
            <span class="tab-date">{{ \Carbon\Carbon::parse($yesterday)->format('d M') }}</span>
        </a>
        <a href="{{ route('matches.index') }}"
           class="tab-btn {{ $tab === 'today' ? 'active' : '' }}">
            <span class="tab-day">Today</span>
            <span class="tab-date">{{ \Carbon\Carbon::parse($today)->format('d M') }}</span>
        </a>
        <a href="{{ route('matches.index', ['tab' => 'tomorrow']) }}"
           class="tab-btn {{ $tab === 'tomorrow' ? 'active' : '' }}">
            <span class="tab-day">Tomorrow</span>
            <span class="tab-date">{{ \Carbon\Carbon::parse($tomorrow)->format('d M') }}</span>
        </a>
    </div>

    {{-- Matches by League --}}
    @if(count($leagues) > 0)
        @foreach($leagues as $leagueId => $leagueData)
            @php
                $league  = $leagueData['info'];
                $matches = $leagueData['matches'];
                $liveCount = collect($matches)->where('status', 'LIVE')->count();
            @endphp

            <div class="league-section">

                {{-- League Header --}}
                <div class="league-header">
                    @if(!empty($league['logo']))
                        <img src="{{ $league['logo'] }}" alt="{{ $league['name'] }}" class="league-logo" loading="lazy"
                             onerror="this.style.display='none'">
                    @endif

                    @if(!empty($league['flag']))
                        <img src="{{ $league['flag'] }}" alt="{{ $league['country'] }}" class="league-flag" loading="lazy"
                             onerror="this.style.display='none'">
                    @endif

                    <div>
                        <div class="league-name">{{ $league['name'] }}</div>
                        <div class="league-country">{{ $league['country'] }}</div>
                    </div>

                    @if($liveCount > 0)
                        <span class="status-badge live" style="margin-left: 8px;">{{ $liveCount }} live</span>
                    @endif

                    <span class="league-round">{{ $league['round'] }}</span>
                    <span class="league-count">{{ count($matches) }}</span>

                    <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>

                {{-- Match Rows --}}
                <div class="matches-list">
                    @foreach($matches as $match)
                        @php
                            $statusClass = match($match['status']) {
                                'LIVE','1H','2H','ET','P' => 'live',
                                'HT'                      => 'ht',
                                'FT','AET','PEN'          => 'ft',
                                default                   => 'ns',
                            };
                            $isLive    = $statusClass === 'live';
                            $isFinished = $statusClass === 'ft';
                            $showScore = $isLive || $isFinished || $match['status'] === 'HT';
                            $homeWin   = $match['home']['winner'] ?? false;
                            $awayWin   = $match['away']['winner'] ?? false;
                        @endphp

                        <a href="{{ route('matches.show', $match['id']) }}" class="match-row">

                            {{-- Time & Status --}}
                            <div class="match-time-col">
                                <span class="match-time">{{ $match['time'] }}</span>
                                <span class="status-badge {{ $statusClass }}">
                                    @if($isLive && $match['elapsed'])
                                        {{ $match['elapsed'] }}'
                                    @elseif($match['status'] === 'HT')
                                        HT
                                    @elseif($isFinished)
                                        FT
                                    @else
                                        {{ $match['status'] }}
                                    @endif
                                </span>
                            </div>

                            {{-- Home Team --}}
                            <div class="team-col home">
                                @if(!empty($match['home']['logo']))
                                    <img src="{{ $match['home']['logo'] }}" alt="{{ $match['home']['name'] }}"
                                         class="team-logo" loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="team-name {{ $homeWin ? 'winner' : '' }}">
                                    {{ $match['home']['name'] }}
                                </span>
                            </div>

                            {{-- Score --}}
                            <div class="score-col">
                                <div class="score-box {{ $statusClass }}">
                                    {{ $showScore && $match['score']['home'] !== null ? $match['score']['home'] : '-' }}
                                </div>
                                <span class="score-sep">:</span>
                                <div class="score-box {{ $statusClass }}">
                                    {{ $showScore && $match['score']['away'] !== null ? $match['score']['away'] : '-' }}
                                </div>
                            </div>

                            {{-- Away Team --}}
                            <div class="team-col away">
                                <span class="team-name {{ $awayWin ? 'winner' : '' }}">
                                    {{ $match['away']['name'] }}
                                </span>
                                @if(!empty($match['away']['logo']))
                                    <img src="{{ $match['away']['logo'] }}" alt="{{ $match['away']['name'] }}"
                                         class="team-logo" loading="lazy" onerror="this.style.display='none'">
                                @endif
                            </div>

                            {{-- Right Meta --}}
                            <div class="match-time-col">
                                @if($isLive && $match['elapsed'])
                                    <span class="elapsed-badge">{{ $match['elapsed'] }}'</span>
                                @elseif($isFinished && !empty($match['score']['halftime']) && $match['score']['halftime'] !== '-')
                                    <span style="font-size:10px; color: var(--text-muted);">HT {{ $match['score']['halftime'] }}</span>
                                @endif
                            </div>

                        </a>
                    @endforeach
                </div>

            </div>
        @endforeach
    @else
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <h3>No matches found</h3>
            <p>There are no scheduled matches for this date.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh live matches every 60 seconds
    @if($tab === 'today')
    setInterval(() => {
        if (document.querySelector('.status-badge.live')) {
            window.location.reload();
        }
    }, 60000);
    @endif
</script>
@endpush