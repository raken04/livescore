<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiFootballService
{
    // SVG inline sebagai fallback logo ketika URL tidak tersedia
    const FALLBACK_LOGO = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23a0aab8"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>';

    /**
     * Fetch fixtures for a specific date with caching.
     *
     * @param string $date (YYYY-MM-DD)
     * @param string $timezone (e.g. Asia/Jakarta)
     * @return array
     */
    public function getFixturesByDate(string $date, string $timezone)
    {
        // Tentukan apakah tanggal yang direquest adalah hari ini berdasarkan timezone user
        $todayDate = \Carbon\Carbon::now($timezone)->format('Y-m-d');
        $isToday = $date === $todayDate;
        
        if ($isToday) {
            // ==========================================
            // LOGIKA HARI INI: MENGGUNAKAN API-SPORTS
            // ==========================================
            $cacheKey = "fixtures_apisports_{$date}_{$timezone}";
            $ttl = now()->addMinutes(1); // Cache 1 menit karena ini realtime live
            
            return Cache::remember($cacheKey, $ttl, function () use ($date, $timezone) {
                $apiUrl = env('API_FOOTBALL_URL');
                $apiKey = env('API_FOOTBALL_KEY');

                $response = Http::withoutVerifying()->withHeaders([
                    'x-apisports-key' => $apiKey,
                ])->get("{$apiUrl}/fixtures", [
                    'date' => $date,
                    'timezone' => $timezone
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    // Tandai setiap fixture sebagai berasal dari apisports agar bisa diklik ke detail
                    $fixtures = $data['response'] ?? [];
                    foreach ($fixtures as &$f) {
                        $f['_source'] = 'apisports';
                    }
                    return $fixtures;
                }

                return [];
            });
        } else {
            // ==========================================
            // LOGIKA BUKAN HARI INI: MENGGUNAKAN API GITHUB
            // (Mendukung fixture hari kemarin/besok/kapanpun)
            // ==========================================
            $cacheKey = "fixtures_github_{$date}_{$timezone}";
            $isPastDate = strtotime($date) < strtotime($todayDate);
            $ttl = $isPastDate ? now()->addHours(6) : now()->addMinutes(5);
            
            return Cache::remember($cacheKey, $ttl, function () use ($date, $timezone) {
                // 1. Ambil data tim
                $teamsResponse = Http::withoutVerifying()->get("https://worldcup26.ir/get/teams");
                $teamsData = [];
                if ($teamsResponse->successful()) {
                    foreach ($teamsResponse->json()['teams'] ?? [] as $t) {
                        $teamsData[$t['id']] = $t;
                    }
                }

                // 2. Ambil data stadion
                $stadiumsResponse = Http::withoutVerifying()->get("https://worldcup26.ir/get/stadiums");
                $stadiumsData = [];
                if ($stadiumsResponse->successful()) {
                    foreach ($stadiumsResponse->json()['stadiums'] ?? [] as $s) {
                        $stadiumsData[$s['id']] = $s;
                    }
                }

                // 3. Ambil jadwal pertandingan dari GitHub
                $response = Http::withoutVerifying()->get("https://worldcup26.ir/get/games");

                if ($response->successful()) {
                    $data = $response->json();
                    $games = $data['games'] ?? [];
                    
                    $formattedFixtures = [];

                    foreach ($games as $game) {
                        // Tentukan zona waktu (offset) stadion
                        $stadium = $stadiumsData[$game['stadium_id']] ?? null;
                        $offset = '-05:00'; // Default fallback
                        
                        if ($stadium) {
                            if ($stadium['region'] === 'Western') {
                                $offset = '-07:00'; 
                            } elseif ($stadium['region'] === 'Eastern') {
                                $offset = '-04:00'; 
                            } elseif ($stadium['region'] === 'Central') {
                                if ($stadium['country_en'] === 'Mexico') {
                                    $offset = '-06:00'; 
                                } else {
                                    $offset = '-05:00'; 
                                }
                            }
                        }

                        try {
                            $matchDateTime = Carbon::createFromFormat('m/d/Y H:i', $game['local_date'], $offset);
                            $matchDateTime->setTimezone($timezone);
                            $gameDate = $matchDateTime->format('Y-m-d');
                        } catch (\Exception $e) {
                            $gameDate = null;
                            $matchDateTime = Carbon::now();
                        }

                        // Filter berdasarkan tanggal yang di-request
                        if ($gameDate === $date) {
                            $statusShort = 'NS'; 
                            $elapsed = null;
                            
                            if ($game['finished'] === 'TRUE') {
                                $statusShort = 'FT';
                                $elapsed = 90;
                            } elseif (strtolower($game['time_elapsed']) === 'live' || (is_numeric($game['time_elapsed']) && $game['time_elapsed'] > 0)) {
                                $statusShort = 'LIVE'; 
                                if (strtolower($game['time_elapsed']) === 'live') {
                                    $now = Carbon::now($timezone);
                                    $minutesDiff = (int) $matchDateTime->diffInMinutes($now);
                                    if ($minutesDiff < 0) {
                                        $statusShort = 'NS';
                                    } elseif ($minutesDiff > 45 && $minutesDiff <= 60) {
                                        $statusShort = 'HT';
                                        $elapsed = 45; 
                                    } else {
                                        $elapsed = $minutesDiff > 60 ? $minutesDiff - 15 : $minutesDiff;
                                        if ($elapsed > 90) $elapsed = 90; 
                                    }
                                } else {
                                    $elapsed = $game['time_elapsed'];
                                }
                            }

                            $homeLogo = isset($teamsData[$game['home_team_id']]) ? $teamsData[$game['home_team_id']]['flag'] : self::FALLBACK_LOGO;
                            $awayLogo = isset($teamsData[$game['away_team_id']]) ? $teamsData[$game['away_team_id']]['flag'] : self::FALLBACK_LOGO;

                            $formattedFixtures[] = [
                                'fixture' => [
                                    'id' => (int) $game['id'],  // ID GitHub sementara, akan dicoba diganti dengan ID API-Sports
                                    'date' => $matchDateTime->toIso8601String(),
                                    'status' => [
                                        'short' => $statusShort,
                                        'elapsed' => $elapsed
                                    ]
                                ],
                                'league' => [
                                    'id' => 1, 
                                    'name' => 'World Cup',
                                    'logo' => 'https://media.api-sports.io/football/leagues/1.png',
                                    'season' => 2026
                                ],
                                'teams' => [
                                    'home' => [
                                        'name' => $game['home_team_name_en'] ?? $game['home_team_label'] ?? 'TBD',
                                        'logo' => $homeLogo
                                    ],
                                    'away' => [
                                        'name' => $game['away_team_name_en'] ?? $game['away_team_label'] ?? 'TBD',
                                        'logo' => $awayLogo
                                    ]
                                ],
                                'goals' => [
                                    'home' => $game['home_score'] !== '' ? (int)$game['home_score'] : null,
                                    'away' => $game['away_score'] !== '' ? (int)$game['away_score'] : null
                                ],
                                '_source' => 'github' // Tandai asal data
                            ];
                        }
                    }

                    return $formattedFixtures;
                }
                return [];
            });
        }
    }

    public function getStandings(int $leagueId, int $season)
    {
        // Fitur klasemen yang ada di dalam service ini dinonaktifkan
        // karena StandingsController menggunakan TheSportsDbService
        return [];
    }

    /**
     * Fetch knockout matches for World Cup 2026 from GitHub API.
     */
    public function getWorldCupKnockoutMatches(string $timezone = 'Asia/Jakarta')
    {
        $cacheKey = "world_cup_knockout_matches_{$timezone}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($timezone) {
            // 1. Ambil data tim
            $teamsResponse = Http::withoutVerifying()->get("https://worldcup26.ir/get/teams");
            $teamsData = [];
            if ($teamsResponse->successful()) {
                foreach ($teamsResponse->json()['teams'] ?? [] as $t) {
                    $teamsData[$t['id']] = $t;
                }
            }

            // 2. Ambil jadwal pertandingan dari GitHub
            $response = Http::withoutVerifying()->get("https://worldcup26.ir/get/games");

            if ($response->successful()) {
                $data = $response->json();
                $games = $data['games'] ?? [];
                
                $knockoutStages = ['R32', 'R16', 'QF', 'SF', '3RD', 'FINAL'];
                $groupedFixtures = [];

                foreach ($games as $game) {
                    $group = strtoupper($game['group']);
                    if (!in_array($group, $knockoutStages)) {
                        continue;
                    }

                    // Mapping nama grup ke label yang lebih manusiawi
                    $groupLabels = [
                        'R32' => 'Round of 32',
                        'R16' => 'Round of 16',
                        'QF' => 'Quarter-Finals',
                        'SF' => 'Semi-Finals',
                        '3RD' => 'Third Place Play-off',
                        'FINAL' => 'Final'
                    ];
                    $label = $groupLabels[$group] ?? $group;

                    $statusShort = 'NS'; 
                    if ($game['finished'] === 'TRUE') {
                        $statusShort = 'FT';
                    } elseif (strtolower($game['time_elapsed']) === 'live' || (is_numeric($game['time_elapsed']) && $game['time_elapsed'] > 0)) {
                        $statusShort = 'LIVE'; 
                    }

                    $homeLogo = isset($teamsData[$game['home_team_id']]) ? $teamsData[$game['home_team_id']]['flag'] : self::FALLBACK_LOGO;
                    $awayLogo = isset($teamsData[$game['away_team_id']]) ? $teamsData[$game['away_team_id']]['flag'] : self::FALLBACK_LOGO;

                    // Buat struktur data agar mirip dengan yang diterima komponen match-card
                    $fixture = [
                        'fixture' => [
                            'id' => (int) $game['id'], 
                            'date' => $game['local_date'], // Mentahan, bisa diubah ke datetime carbon jika diperlukan
                            'status' => [
                                'short' => $statusShort,
                                'elapsed' => null
                            ]
                        ],
                        'league' => [
                            'id' => 1, 
                            'name' => 'World Cup',
                            'logo' => 'https://media.api-sports.io/football/leagues/1.png',
                            'season' => 2026
                        ],
                        'teams' => [
                            'home' => [
                                'name' => $game['home_team_name_en'] ?? $game['home_team_label'] ?? 'TBD',
                                'logo' => $homeLogo
                            ],
                            'away' => [
                                'name' => $game['away_team_name_en'] ?? $game['away_team_label'] ?? 'TBD',
                                'logo' => $awayLogo
                            ]
                        ],
                        'goals' => [
                            'home' => $game['home_score'] !== '' ? (int)$game['home_score'] : null,
                            'away' => $game['away_score'] !== '' ? (int)$game['away_score'] : null
                        ],
                        '_source' => 'github'
                    ];

                    $groupedFixtures[$label][] = $fixture;
                }
                
                // Pastikan urutannya benar (dari R32 ke Final)
                $sortedGroupedFixtures = [];
                $order = ['Round of 32', 'Round of 16', 'Quarter-Finals', 'Semi-Finals', 'Third Place Play-off', 'Final'];
                foreach ($order as $roundName) {
                    if (isset($groupedFixtures[$roundName])) {
                        $sortedGroupedFixtures[$roundName] = $groupedFixtures[$roundName];
                    }
                }

                return $sortedGroupedFixtures;
            }
            return [];
        });
    }

    /**
     * Fetch match details (events, lineups, statistics) using fixture ID.
     */
    public function getMatchDetails(int $fixtureId)
    {
        $cacheKey = "match_details_{$fixtureId}";
        
        // Cache selama 1 menit karena details (events, stats) sering berubah saat live
        return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($fixtureId) {
            $apiUrl = env('API_FOOTBALL_URL');
            $apiKey = env('API_FOOTBALL_KEY');

            $response = Http::withoutVerifying()->withHeaders([
                'x-apisports-key' => $apiKey,
            ])->get("{$apiUrl}/fixtures", [
                'id' => $fixtureId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['response'][0] ?? null;
            }

            return null;
        });
    }
}
