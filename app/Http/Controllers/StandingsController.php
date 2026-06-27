<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TheSportsDbService;

class StandingsController extends Controller
{
    protected $tsdbService;

    // Mapping ID API-Football ke TheSportsDB
    protected $leagueMapping = [
        1 => 4429,   // World Cup
        39 => 4328,  // EPL
        78 => 4331,  // Bundesliga
        135 => 4332, // Serie A
        61 => 4334,  // Ligue 1
        140 => 4335  // La Liga
    ];

    public function __construct(TheSportsDbService $tsdbService)
    {
        $this->tsdbService = $tsdbService;
    }

    public function index(Request $request)
    {
        $apiFootballLeagueId = $request->query('league');
        $leagueName = $request->query('league_name', 'Klasemen Liga');
        $apiSeason = $request->query('season', date('Y')); // cth: 2023 atau 2025

        if (!$apiFootballLeagueId || !isset($this->leagueMapping[$apiFootballLeagueId])) {
            // Jika ID tidak dipetakan, kita belum mensupportnya secara gratis
            return view('livescore.standings', [
                'error' => 'Data klasemen gratis untuk liga ini belum dipetakan. Silakan cek dokumentasi TheSportsDB.',
                'leagueName' => $leagueName,
                'season' => $apiSeason,
                'standingsGroups' => []
            ]);
        }

        $sportsDbLeagueId = $this->leagueMapping[$apiFootballLeagueId];

        // Format Season: Jika bukan World Cup (bukan ID 1), formatnya "YYYY-YYYY+1"
        if ((int)$apiFootballLeagueId !== 1) {
            $nextYear = (int)$apiSeason + 1;
            $sportsDbSeason = "{$apiSeason}-{$nextYear}";
        } else {
            // World Cup (ID 1) formatnya hanya tahun berjalan
            $sportsDbSeason = $apiSeason;
        }

        // Ambil data klasemen dari TheSportsDB
        $standingsGroups = $this->tsdbService->getStandings($sportsDbLeagueId, $sportsDbSeason);

        $knockoutMatches = [];
        if ((int)$apiFootballLeagueId === 1) {
            $apiService = app(\App\Services\ApiFootballService::class);
            $knockoutMatches = $apiService->getWorldCupKnockoutMatches();
        }

        if (empty($standingsGroups) && empty($knockoutMatches)) {
            return view('livescore.standings', [
                'error' => "Data klasemen belum tersedia di TheSportsDB untuk musim {$sportsDbSeason}.",
                'leagueName' => $leagueName,
                'season' => $sportsDbSeason,
                'standingsGroups' => [],
                'knockoutMatches' => []
            ]);
        }

        return view('livescore.standings', [
            'leagueName' => $leagueName,
            'season' => $sportsDbSeason, // Akan memunculkan "2023-2024" atau "2026"
            'standingsGroups' => $standingsGroups,
            'knockoutMatches' => $knockoutMatches ?? [],
            'error' => null
        ]);
    }
}
