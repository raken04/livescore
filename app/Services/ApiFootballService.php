<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiFootballService
{
    /**
     * Fetch fixtures for a specific date with caching.
     *
     * @param string $date (YYYY-MM-DD)
     * @param string $timezone (e.g. Asia/Jakarta)
     * @return array
     */
    public function getFixturesByDate(string $date, string $timezone)
    {
        // Kunci cache yang unik berdasarkan tanggal dan zona waktu
        $cacheKey = "fixtures_{$date}_{$timezone}";
        
        // Cek apakah tanggal yang dicari adalah hari ini atau besok.
        // Jika ya, kita cache sebentar saja (misal 5 menit) karena skor masih bisa berubah.
        // Jika kemarin atau sebelumnya, kita cache sangat lama (misal 1 hari) karena skor sudah final (FT).
        $isPastDate = strtotime($date) < strtotime(date('Y-m-d'));
        
        // Cache::remember akan otomatis mengecek apakah data ada di cache.
        // Jika ada, langsung dikembalikan. Jika tidak, ia akan menjalankan Closure (fungsi di dalamnya),
        // menyimpan hasilnya ke cache sesuai waktu yang ditentukan, lalu mengembalikannya.
        $ttl = $isPastDate ? now()->addDay() : now()->addMinutes(5);

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
                return $data['response'] ?? [];
            }

            return [];
        });
    }
}
