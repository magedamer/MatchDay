<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballApiService
{
    protected string $baseUrl  = 'https://v3.football.api-sports.io';
    protected string $apiKey;
    protected int    $cacheTtl = 1800; // 30 minutes

    public function __construct()
    {
        $this->apiKey = config('services.football_api.key', '');
    }

    /**
     * Fetch all matches for a given date (YYYY-MM-DD).
     */
    public function getMatchesByDate(string $date): array
    {
        $cacheKey = "matches_{$date}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($date) {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'v3.football.api-sports.io',
                    'x-rapidapi-key'  => $this->apiKey,
                ])->get("{$this->baseUrl}/fixtures", [
                    'date'     => $date,
                    'timezone' => config('app.timezone', 'UTC'),
                ]);

                if ($response->successful()) {
                    return $this->normalizeFixtures($response->json('response', []));
                }

                Log::error('Football API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return $this->getFallbackData($date);
            } catch (\Throwable $e) {
                Log::error('Football API exception', ['message' => $e->getMessage()]);
                return $this->getFallbackData($date);
            }
        });
    }

    /**
     * Fetch a single match by fixture ID.
     */
    public function getMatchById(int $id): ?array
    {
        $cacheKey = "match_{$id}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'v3.football.api-sports.io',
                    'x-rapidapi-key'  => $this->apiKey,
                ])->get("{$this->baseUrl}/fixtures", ['id' => $id]);

                if ($response->successful()) {
                    $fixtures = $response->json('response', []);
                    if (!empty($fixtures)) {
                        return $this->normalizeFixture($fixtures[0]);
                    }
                }

                return null;
            } catch (\Throwable $e) {
                Log::error('Football API exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Normalize a collection of raw fixtures.
     */
    private function normalizeFixtures(array $fixtures): array
    {
        return array_map([$this, 'normalizeFixture'], $fixtures);
    }

    /**
     * Normalize a single raw fixture into a clean array for views.
     */
    private function normalizeFixture(array $fixture): array
    {
        $f = $fixture['fixture'] ?? [];
        $l = $fixture['league']  ?? [];
        $h = $fixture['teams']['home'] ?? [];
        $a = $fixture['teams']['away'] ?? [];
        $g = $fixture['goals'] ?? [];
        $s = $fixture['score'] ?? [];

        $statusShort = $f['status']['short'] ?? 'NS';
        $statusLong  = $f['status']['long']  ?? 'Not Started';
        $elapsed     = $f['status']['elapsed'] ?? null;

        return [
            'id'          => $f['id'] ?? 0,
            'date'        => $f['date'] ?? null,
            'time'        => $f['date'] ? date('H:i', strtotime($f['date'])) : '--:--',
            'status'      => $statusShort,
            'status_long' => $statusLong,
            'elapsed'     => $elapsed,
            'venue'       => $f['venue']['name'] ?? null,
            'league'      => [
                'id'      => $l['id'] ?? 0,
                'name'    => $l['name'] ?? 'Unknown League',
                'country' => $l['country'] ?? '',
                'logo'    => $l['logo'] ?? null,
                'flag'    => $l['flag'] ?? null,
                'round'   => $l['round'] ?? '',
            ],
            'home' => [
                'id'     => $h['id'] ?? 0,
                'name'   => $h['name'] ?? 'Home',
                'logo'   => $h['logo'] ?? null,
                'winner' => $h['winner'] ?? null,
            ],
            'away' => [
                'id'     => $a['id'] ?? 0,
                'name'   => $a['name'] ?? 'Away',
                'logo'   => $a['logo'] ?? null,
                'winner' => $a['winner'] ?? null,
            ],
            'score' => [
                'home'     => $g['home'],
                'away'     => $g['away'],
                'halftime' => ($s['halftime']['home'] ?? '-') . '-' . ($s['halftime']['away'] ?? '-'),
                'fulltime' => ($s['fulltime']['home'] ?? '-') . '-' . ($s['fulltime']['away'] ?? '-'),
            ],
        ];
    }

    /**
     * Demo / fallback data when no API key is configured or request fails.
     */
    private function getFallbackData(string $date): array
    {
        $leagues = [
            ['id' => 39,  'name' => 'Premier League',       'country' => 'England', 'logo' => 'https://media.api-sports.io/football/leagues/39.png',  'flag' => 'https://media.api-sports.io/flags/gb.svg', 'round' => 'Regular Season - 30'],
            ['id' => 140, 'name' => 'La Liga',               'country' => 'Spain',   'logo' => 'https://media.api-sports.io/football/leagues/140.png', 'flag' => 'https://media.api-sports.io/flags/es.svg', 'round' => 'Regular Season - 28'],
            ['id' => 135, 'name' => 'Serie A',               'country' => 'Italy',   'logo' => 'https://media.api-sports.io/football/leagues/135.png', 'flag' => 'https://media.api-sports.io/flags/it.svg', 'round' => 'Regular Season - 27'],
            ['id' => 78,  'name' => 'Bundesliga',            'country' => 'Germany', 'logo' => 'https://media.api-sports.io/football/leagues/78.png',  'flag' => 'https://media.api-sports.io/flags/de.svg', 'round' => 'Regular Season - 26'],
            ['id' => 2,   'name' => 'UEFA Champions League', 'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/2.png',   'flag' => null, 'round' => 'Quarter-finals'],
        ];

        $teams = [
            ['id' => 33,  'name' => 'Manchester United',    'logo' => 'https://media.api-sports.io/football/teams/33.png'],
            ['id' => 40,  'name' => 'Liverpool',            'logo' => 'https://media.api-sports.io/football/teams/40.png'],
            ['id' => 50,  'name' => 'Manchester City',      'logo' => 'https://media.api-sports.io/football/teams/50.png'],
            ['id' => 49,  'name' => 'Chelsea',              'logo' => 'https://media.api-sports.io/football/teams/49.png'],
            ['id' => 42,  'name' => 'Arsenal',              'logo' => 'https://media.api-sports.io/football/teams/42.png'],
            ['id' => 47,  'name' => 'Tottenham',            'logo' => 'https://media.api-sports.io/football/teams/47.png'],
            ['id' => 529, 'name' => 'Barcelona',            'logo' => 'https://media.api-sports.io/football/teams/529.png'],
            ['id' => 541, 'name' => 'Real Madrid',          'logo' => 'https://media.api-sports.io/football/teams/541.png'],
            ['id' => 489, 'name' => 'AC Milan',             'logo' => 'https://media.api-sports.io/football/teams/489.png'],
            ['id' => 505, 'name' => 'Inter',                'logo' => 'https://media.api-sports.io/football/teams/505.png'],
            ['id' => 157, 'name' => 'Bayern Munich',        'logo' => 'https://media.api-sports.io/football/teams/157.png'],
            ['id' => 165, 'name' => 'Borussia Dortmund',   'logo' => 'https://media.api-sports.io/football/teams/165.png'],
            ['id' => 496, 'name' => 'Juventus',             'logo' => 'https://media.api-sports.io/football/teams/496.png'],
            ['id' => 85,  'name' => 'Paris Saint-Germain',  'logo' => 'https://media.api-sports.io/football/teams/85.png'],
        ];

        $statuses = [
            ['short' => 'NS',   'long' => 'Not Started',   'elapsed' => null],
            ['short' => 'LIVE', 'long' => 'Match In Play', 'elapsed' => rand(10, 85)],
            ['short' => 'HT',   'long' => 'Halftime',      'elapsed' => 45],
            ['short' => 'FT',   'long' => 'Match Finished','elapsed' => 90],
        ];

        $fixtures = [];
        $times    = ['13:00', '15:00', '15:30', '16:00', '17:30', '18:00', '19:00', '20:00', '20:45', '21:00'];
        $id       = 1000;

        foreach ($leagues as $league) {
            $count = rand(2, 4);
            for ($i = 0; $i < $count; $i++) {
                $teamIndices = array_rand($teams, 2);
                $home   = $teams[$teamIndices[0]];
                $away   = $teams[$teamIndices[1]];
                $status = $statuses[array_rand($statuses)];
                $time   = $times[array_rand($times)];

                $homeGoals = $awayGoals = null;
                if (in_array($status['short'], ['LIVE', 'HT', 'FT'])) {
                    $homeGoals = rand(0, 4);
                    $awayGoals = rand(0, 4);
                }

                $fixtures[] = [
                    'id'          => $id++,
                    'date'        => "{$date}T{$time}:00+00:00",
                    'time'        => $time,
                    'status'      => $status['short'],
                    'status_long' => $status['long'],
                    'elapsed'     => $status['elapsed'],
                    'venue'       => 'Demo Stadium',
                    'league'      => $league,
                    'home'        => array_merge($home, ['winner' => null]),
                    'away'        => array_merge($away, ['winner' => null]),
                    'score'       => [
                        'home'     => $homeGoals,
                        'away'     => $awayGoals,
                        'halftime' => '-',
                        'fulltime' => '-',
                    ],
                ];
            }
        }

        usort($fixtures, fn($a, $b) => strcmp($a['time'], $b['time']));

        return $fixtures;
    }
}