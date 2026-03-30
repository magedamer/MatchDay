<?php

namespace App\Http\Controllers;

use App\Services\FootballApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MatchController extends Controller
{
    protected FootballApiService $footballApi;

    /**
     * Whitelist of important league IDs from api-sports.io.
     * Add or remove IDs to control which leagues appear on the site.
     */
    protected array $importantLeagueIds = [
        2,    // UEFA Champions League
        3,    // UEFA Europa League
        5,    // UEFA Nations League
        848,  // UEFA Europa Conference League
        531,  // UEFA Super Cup

        // ─── England ───────────────────────────────
        39,   // Premier League (England)
        45,   // FA Cup
        48,   // League Cup
        40,   // Championship (England)

        // ─── Spain ───────────────────────────────
        140,  // La Liga (Spain)
        143,  // Copa del Rey
        556,  // Super Cup
        140,  // La Liga (Spain)

        // ─── Germany ───────────────────────────────
        78,   // Bundesliga (Germany)
        79,   // B2. Bundesliga (Germany)
        529,  // Super Cup (Germany)

        // ─── Italy ───────────────────────────────
        135,  // Serie A (Italy)
        137,  // Coppa Italia (Italy)
        547,  // Super Cup (Italy)
        
        // ─── France ───────────────────────────────
        61,   // Ligue 1 (France)
        66,   // Coupe de France (France)
        526,  // Trophée des Champions (France)
        
        // ─── Egypt ───────────────────────────────
        233,  // Egyptian Premier League
        234,  // Egypt Cup
        887,  // Egyptian Second League - Group A
        888,  // Egyptian Second League - Group B
        895,  // Egyptian League Cup
        539,  // Egyptian Super Cup

        // ─── World Cup ───────────────────────────
        1,    // World Cup
        9,    // World Cup Qualification - Africa
        10,   // World Cup Qualification - Asia
        11,   // World Cup Qualification - CONCACAF
        12,   // World Cup Qualification - Europe
        13,   // World Cup Qualification - South America
        14,   // World Cup Qualification - Oceania
        29,   // Africa Cup of Nations (AFCON)
        30,   // Africa Cup of Nations Qualification
        6,    // FIFA Confederations Cup
        15,   // Copa America
        17,   // AFC Asian Cup
        18,   // AFC Asian Cup Qualification
    ];

    public function __construct(FootballApiService $footballApi)
    {
        $this->footballApi = $footballApi;
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'today');

        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $today     = Carbon::today()->format('Y-m-d');
        $tomorrow  = Carbon::tomorrow()->format('Y-m-d');

        $date = match ($tab) {
            'yesterday' => $yesterday,
            'tomorrow'  => $tomorrow,
            default     => $today,
        };

        $matches = $this->footballApi->getMatchesByDate($date);
        $matches = $this->filterByImportantLeagues($matches);

        $leagues = $this->groupByLeague($matches);

        $stats = [
            'total'    => count($matches),
            'live'     => count(array_filter($matches, fn($m) => $m['status'] === 'LIVE')),
            'finished' => count(array_filter($matches, fn($m) => $m['status'] === 'FT')),
            'upcoming' => count(array_filter($matches, fn($m) => $m['status'] === 'NS')),
        ];

        return view('matches.index', compact('leagues', 'stats', 'tab', 'today', 'yesterday', 'tomorrow'));
    }

    public function show(int $id)
    {
        $match = $this->footballApi->getMatchById($id);

        if (!$match) {
            abort(404);
        }

        return view('matches.show', compact('match'));
    }

    public function apiMatches(Request $request)
    {
        $date    = $request->query('date', Carbon::today()->format('Y-m-d'));
        $matches = $this->footballApi->getMatchesByDate($date);
        $matches = $this->filterByImportantLeagues($matches);

        return response()->json($matches);
    }

    /**
     * Keep only matches belonging to the important leagues whitelist.
     */
    private function filterByImportantLeagues(array $matches): array
    {
        return array_values(array_filter(
            $matches,
            fn($m) => in_array($m['league']['id'] ?? 0, $this->importantLeagueIds, true)
        ));
    }

    /**
     * Group an array of matches by their league ID.
     */
    private function groupByLeague(array $matches): array
    {
        $grouped = [];

        foreach ($matches as $match) {
            $leagueKey = $match['league']['id'] ?? 'unknown';

            if (!isset($grouped[$leagueKey])) {
                $grouped[$leagueKey] = [
                    'info'    => $match['league'],
                    'matches' => [],
                ];
            }

            $grouped[$leagueKey]['matches'][] = $match;
        }

        return $grouped;
    }
}