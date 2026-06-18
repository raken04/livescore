<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiFootballService;
use Carbon\Carbon;

class LiveScoreController extends Controller
{
    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        // 1. Ambil Parameter dari URL (Contoh: ?date=2026-06-16&league=Premier+League)
        // Default tanggal adalah hari ini
        $dateParam = $request->query('date', date('Y-m-d'));
        $activeLeagueId = $request->query('league', 'All'); // Kita sekarang menangkap ID, bukan Nama
        $timezone = $request->query('timezone', 'Asia/Jakarta'); // Ambil zona waktu dari request, default ke Asia/Jakarta

        // Validasi format tanggal menggunakan Carbon, fallback ke hari ini jika tidak valid
        try {
            $selectedDate = Carbon::parse($dateParam);
        } catch (\Exception $e) {
            $selectedDate = Carbon::now();
        }

        // 2. Ambil data dari API (Sudah termasuk sistem Caching di dalamnya)
        $matchesArray = $this->apiService->getFixturesByDate($selectedDate->format('Y-m-d'), $timezone);
        
        // Ubah array biasa menjadi Laravel Collection agar mudah difilter dan dimanipulasi
        $matches = collect($matchesArray);

        // 3. Ekstrak daftar Liga unik untuk FilterBar
        $priorityLeagueIds = [
            1, 2, 3, 848, 39, 140, 135, 78, 61
        ];

        // Ekstrak objek liga (isi id, name, logo)
                $leagues = $matches->pluck('league')
            ->unique('id') // Pastikan tidak ganda
            ->filter(function ($league) use ($priorityLeagueIds) {
                // Buang liga yang ID-nya tidak ada di daftar prioritas
                return in_array($league['id'], $priorityLeagueIds);
            })
            ->sortBy(function ($league) use ($priorityLeagueIds) {
                // Urutkan sesuai posisi di array $priorityLeagueIds
                return array_search($league['id'], $priorityLeagueIds);
            })
            ->values()
            ->toArray();

        // 4. Filter pertandingan berdasarkan Id liga
        if ($activeLeagueId !== 'All') {
            // Jika user menekan tombol spesifik (Misal: Premier League)
            $filteredMatches = $matches->filter(function ($m) use ($activeLeagueId) {
                return $m['league']['id'] == $activeLeagueId;
            });
        } else {
            // Jika "Semua", ambil berdasarkan ID prioritas
            $filteredMatches = $matches->filter(function ($m) use ($priorityLeagueIds) {
                return in_array($m['league']['id'], $priorityLeagueIds);
            });
        }

        // 5. Tentukan "Big Match"
        $featuredMatch = $this->getFeaturedMatch($filteredMatches);

        // 6. Kelompokkan pertandingan sisanya (Live, Upcoming, Finished)
        $categorized = $this->categorizeMatches($filteredMatches, $featuredMatch['fixture']['id'] ?? null);

        // // 7. Kirim data ke tampilan (View) Blade
        // return view('livescore.index', [
        //     'selectedDate' => $selectedDate,
        //     'activeLeague' => $activeLeague,
        //     'timezone' => $timezone,
        //     'leagues' => $leagues,
        //     'totalMatches' => $filteredMatches->count(),
        //     'featuredMatch' => $featuredMatch,
        //     'live' => $categorized['live'],
        //     'upcoming' => $categorized['upcoming'],
        //     'finished' => $categorized['finished'],
        // ]);

        // 7. Kirim data ke View (Perhatikan kita kirim activeLeagueId)
        return view('livescore.index', [
            'selectedDate' => $selectedDate,
            'activeLeague' => $activeLeagueId, // Ini sekarang menyimpan ID
            'timezone' => $timezone,
            'leagues' => $leagues,
            'totalMatches' => $filteredMatches->count(),
            'featuredMatch' => $featuredMatch,
            'live' => $categorized['live'],
            'upcoming' => $categorized['upcoming'],
            'finished' => $categorized['finished'],
        ]);
    }

    /**
     * Logika untuk mencari pertandingan Big Match prioritas.
     */
    private function getFeaturedMatch($matches)
    {
        if ($matches->isEmpty()) return null;

        $bigTeams = [
            'Argentina', 
            'France', 
            'Spain', 
            'England', 
            'Brazil', 
            'Morroco', 
            'Portugal', 
            'Netherlands', 
            'Germany', 
            'Belgium', 
            'Colombia', 
            'Italy', 
            'Mexico', 
            'Croatia', 
            'USA', 
            'Senegal', 
            'Japan', 
            'Uruguay', 
            'Switzerland', 
            'Denmark', 
            'Austria', 
            'Korea Republic', 
            'Nigeria', 
            'Turkiye', 
            'Norway', 
            'Canada', 
            'Egypt', 
            'Sweden', 
            'Poland', 
            'Wales', 
            'Arsenal', 
            'Chelsea', 
            'Liverpool', 
            'Manchester City', 
            'Manchester United', 
            'Tottenham Hotspur', 
            'Real Madrid', 
            'Barcelona', 
            'Juventus', 
            'Inter Milan', 
            'AC Milan', 
            'Napoli', 
            'Inter'
        ];

        $bigLeagueIds = [1, 2, 3, 848, 39, 140, 135, 78, 61]; // ID Liga Besar

        // Kita gunakan fitur sortByDesc bawaan Laravel untuk mengurutkan dari skor tertinggi
        $sortedMatches = $matches->sortByDesc(function ($m) use ($bigTeams, $bigLeagueIds) {
            $score = 0; // Mulai dengan skor 0
            
            // 1. Cek Tim Besar (Maksimal dapat 2 poin jika dua-duanya tim besar)
            if (in_array($m['teams']['home']['name'], $bigTeams)) $score += 1;
            if (in_array($m['teams']['away']['name'], $bigTeams)) $score += 1;
            
            // 2. Cek Liga Besar
            if (in_array($m['league']['id'], $bigLeagueIds)) $score += 2;
            
            // 3. Cek Status LIVE (Poin tertinggi agar selalu menang melawan match yang belum main)
            $status = $m['fixture']['status']['short'];
            if (in_array($status, ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'])) {
                $score += 10;
            }
            
            return $score; // Kembalikan skor akhir pertandingan ini
        });
        // Ambil urutan pertama (yang skornya paling tinggi)
        return $sortedMatches->first();
    }

    /**
     * Membagi pertandingan ke 3 grup (Live, Upcoming, Finished).
     */
    private function categorizeMatches($matches, $featuredId)
    {
        $live = [];
        $upcoming = [];
        $finished = [];

        foreach ($matches as $m) {
            if ($m['fixture']['id'] === $featuredId) continue;

            $status = $m['fixture']['status']['short'];
            if (in_array($status, ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'])) {
                $live[] = $m;
            } elseif (in_array($status, ['TBD', 'NS'])) {
                $upcoming[] = $m;
            } elseif (in_array($status, ['FT', 'AET', 'PEN', 'PST', 'CANC', 'ABD', 'AWD', 'WO'])) {
                $finished[] = $m;
            }
        }

        return compact('live', 'upcoming', 'finished');
    }
}
