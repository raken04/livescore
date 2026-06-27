<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiveScore PRO (Laravel)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bgDark: '#0f1115',
                        cardBg: '#1a1d24',
                        textMain: '#e1e6f0',
                        textMuted: '#a0aab8',
                        accentColor: '#00ff88',
                        hotColor: '#ff3366',
                        borderColor: '#2d333b'
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap');
        
        body {
            background-color: #0f1115;
            color: #e1e6f0;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body>
    <div class="max-w-[1200px] mx-auto py-[30px] px-[20px]">
        
        <x-header />

        <x-date-picker :selected-date="$selectedDate" :timezone="$timezone" />

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-6 rounded-xl text-center mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(count($leagues) > 0)
            <x-filter-bar :leagues="$leagues" :active-league="$activeLeague" :selected-date="$selectedDate" />
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <p class="text-[#a0aab8] text-sm">
                        Menampilkan {{ $totalMatches }} pertandingan
                    </p>
                    
                    @if($activeLeague !== 'All')
                        @php
                            $activeLeagueObj = collect($leagues)->firstWhere('id', $activeLeague);
                            $activeLeagueName = $activeLeagueObj['name'] ?? 'Liga';
                            $activeLeagueSeason = $activeLeagueObj['season'] ?? date('Y');
                        @endphp
                        <a href="{{ route('standings.index', ['league' => $activeLeague, 'league_name' => $activeLeagueName, 'season' => $activeLeagueSeason]) }}" 
                           class="bg-accentColor/10 text-accentColor border border-accentColor/30 hover:bg-accentColor/20 px-4 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-all shadow-[0_0_10px_rgba(0,255,136,0.1)]">
                            🏆 Lihat Klasemen {{ $activeLeagueName }}
                        </a>
                    @endif
                </div>

                <a href="{{ url()->current() }}?{{ http_build_query(request()->query()) }}" class="text-xs text-accentColor border border-accentColor/30 hover:bg-accentColor/10 px-3 py-1 rounded-full transition-colors">
                    ↻ Refresh
                </a>
            </div>
        @endif

        <main>
            @if($totalMatches === 0)
                <div class="text-center py-20 text-[#a0aab8]">
                    <p class="text-lg">Tidak ada pertandingan untuk tanggal/kategori ini.</p>
                </div>
            @else
                
                @if($featuredMatch)
                    <x-featured-match :match="$featuredMatch" />
                @endif

                @if(count($live) > 0)
                    <section class="mb-10">
                        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-accentColor rounded-full animate-pulse"></span>
                            Sedang Berlangsung (Live)
                        </h2>
                        <div class="flex gap-[20px] overflow-x-auto pb-4 snap-x snap-mandatory hide-scrollbar">
                            @foreach($live as $match)
                                <div class="snap-start shrink-0">
                                    <x-match-card :match="$match" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if(count($upcoming) > 0)
                    <section class="mb-10">
                        <h2 class="text-xl font-bold mb-4 text-[#a0aab8]">Akan Datang (Upcoming)</h2>
                        <div class="flex gap-[20px] overflow-x-auto pb-4 snap-x snap-mandatory hide-scrollbar">
                            @foreach($upcoming as $match)
                                <div class="snap-start shrink-0">
                                    <x-match-card :match="$match" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if(count($finished) > 0)
                    <section class="mb-10">
                        <h2 class="text-xl font-bold mb-4 text-[#a0aab8]">Selesai (Finished)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[15px]">
                            @foreach($finished as $match)
                                <x-match-card :match="$match" size="small" />
                            @endforeach
                        </div>
                    </section>
                @endif

            @endif
        </main>
    </div>
</body>
</html>
