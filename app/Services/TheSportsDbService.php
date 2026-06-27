<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TheSportsDbService
{
    /**
     * Fetch standings from TheSportsDB
     * Endpoint: https://www.thesportsdb.com/api/v1/json/3/lookuptable.php?l={id}&s={season}
     */
    public function getStandings(int $sportsDbLeagueId, string $season)
    {
        // === MENGGUNAKAN API GITHUB (WORLDCUP 2026) ===
        // Jika liga yang diminta adalah World Cup (ID TheSportsDB untuk WC adalah 4429)
        if ($sportsDbLeagueId === 4429) {
            $cacheKey = "wc2026_standings_{$season}";
            $ttl = now()->addMinutes(1); // Realtime, cache 1 menit

            return Cache::remember($cacheKey, $ttl, function () {
                // Ambil data tim untuk nama dan logo
                $teamsResponse = Http::withoutVerifying()->get("https://worldcup26.ir/get/teams");
                
                // Jika request gagal, return array kosong agar tidak di-cache secara permanen dengan data Unknown
                if (!$teamsResponse->successful()) {
                    return [];
                }
                
                $teamsData = [];
                foreach ($teamsResponse->json()['teams'] ?? [] as $t) {
                    $teamsData[$t['id']] = $t;
                }

                $response = Http::withoutVerifying()->get("https://worldcup26.ir/get/groups");

                if ($response->successful()) {
                    $data = $response->json();
                    $groups = $data['groups'] ?? [];
                    
                    $groupedStandings = [];

                    foreach ($groups as $group) {
                        $groupName = "Group " . $group['name'];
                        $groupedStandings[$groupName] = [];
                        
                        // Urutkan tim berdasarkan poin (pts) dan selisih gol (gd) secara menurun
                        $teams = $group['teams'];
                        usort($teams, function($a, $b) {
                            if ($a['pts'] == $b['pts']) {
                                return $b['gd'] - $a['gd']; // Jika poin sama, cek selisih gol
                            }
                            return $b['pts'] - $a['pts']; // Urutkan poin terbesar di atas
                        });

                        $rank = 1;
                        foreach ($teams as $team) {
                            $teamInfo = $teamsData[$team['team_id']] ?? null;
                            
                            // Format menjadi seperti data TheSportsDB agar tidak perlu ubah View
                            $groupedStandings[$groupName][] = [
                                'intRank' => $rank,
                                'strTeam' => $teamInfo ? $teamInfo['name_en'] : 'Unknown',
                                'strBadge' => $teamInfo ? $teamInfo['flag'] : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23a0aab8"><circle cx="12" cy="12" r="10"/></svg>',
                                'intPlayed' => $team['mp'],
                                'intWin' => $team['w'],
                                'intDraw' => $team['d'],
                                'intLoss' => $team['l'],
                                'intGoalDifference' => $team['gd'],
                                'intPoints' => $team['pts'],
                                'strGroup' => $groupName // Supaya view tahu nama grupnya
                            ];
                            $rank++;
                        }
                    }

                    // Urutkan nama grup (Group A, Group B, dst)
                    ksort($groupedStandings);

                    return $groupedStandings;
                }

                return [];
            });
        }

        return [];
    }
}
