<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Details - LiveScore PRO</title>
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
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Smooth tab transition */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="max-w-[800px] mx-auto py-6 px-4">
        
        <!-- TOP NAV: Permit Easy Reversal (Keep Users in Control) -->
        <div class="mb-6">
            <a href="{{ route('livescore.index') }}" class="inline-flex items-center gap-2 text-[#a0aab8] hover:text-white bg-[#1a1d24] px-4 py-2 rounded-lg border border-[#2d333b] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Kembali
            </a>
        </div>

        <!-- HEADER MATCH: Reduce Short-Term Memory Load (Sticky / Always on Top) -->
        @php
            $isLiveStatus = in_array($match['fixture']['status']['short'], ['1H', '2H', 'HT', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE']);
            if ($isLiveStatus) {
                $shortStatus = $match['fixture']['status']['short'];
                if ($shortStatus === 'HT') {
                    $statusDisplay = "Halftime (HT)";
                } elseif ($shortStatus === 'INT') {
                    $statusDisplay = "Interrupted (INT)";
                } elseif ($shortStatus === 'SUSP') {
                    $statusDisplay = "Suspended (SUSP)";
                } elseif (in_array($shortStatus, ['P', 'PEN'])) {
                    $statusDisplay = "Penalty";
                } else {
                    $elapsed = $match['fixture']['status']['elapsed'] ?? '';
                    $statusDisplay = "LIVE " . ($elapsed ? $elapsed . "'" : "");
                }
            } else {
                $statusDisplay = $match['fixture']['status']['short'];
            }
        @endphp
        
        <div class="bg-[#1a1d24] rounded-2xl border border-[#2d333b] p-6 mb-6 shadow-lg sticky top-4 z-50">
            <div class="text-center text-[#a0aab8] text-sm font-semibold mb-6 flex justify-center items-center gap-2">
                <img src="{{ $match['league']['logo'] ?? '' }}" alt="League" class="w-5 h-5 bg-white rounded-full" onerror="this.style.display='none'">
                {{ $match['league']['name'] ?? 'League' }} - {{ $match['league']['season'] ?? '' }}
            </div>
            
            <div class="flex justify-between items-center">
                <!-- Home Team -->
                <div class="flex flex-col items-center w-1/3">
                    <div class="w-16 h-16 bg-[#2d333b] rounded-full flex justify-center items-center p-2 mb-3 overflow-hidden">
                        <img src="{{ $match['teams']['home']['logo'] ?? '' }}" alt="{{ $match['teams']['home']['name'] ?? 'Home' }}" class="w-full h-full object-contain" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23a0aab8\'><circle cx=\'12\' cy=\'12\' r=\'10\'/></svg>'">
                    </div>
                    <h3 class="text-lg font-bold text-center">{{ $match['teams']['home']['name'] ?? 'Home' }}</h3>
                </div>
                
                <!-- Score -->
                <div class="w-1/3 text-center flex flex-col items-center">
                    <span class="text-xs font-bold px-3 py-1 rounded-full mb-3 {{ $isLiveStatus ? 'bg-hotColor/20 text-hotColor' : 'bg-[#2d333b] text-[#a0aab8]' }}">
                        {{ $statusDisplay }}
                    </span>
                    <div class="text-4xl md:text-5xl font-extrabold tracking-tight">
                        {{ $match['goals']['home'] ?? '-' }} <span class="text-[#a0aab8] mx-2">:</span> {{ $match['goals']['away'] ?? '-' }}
                    </div>
                </div>
                
                <!-- Away Team -->
                <div class="flex flex-col items-center w-1/3">
                    <div class="w-16 h-16 bg-[#2d333b] rounded-full flex justify-center items-center p-2 mb-3 overflow-hidden">
                        <img src="{{ $match['teams']['away']['logo'] ?? '' }}" alt="{{ $match['teams']['away']['name'] ?? 'Away' }}" class="w-full h-full object-contain" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23a0aab8\'><circle cx=\'12\' cy=\'12\' r=\'10\'/></svg>'">
                    </div>
                    <h3 class="text-lg font-bold text-center">{{ $match['teams']['away']['name'] ?? 'Away' }}</h3>
                </div>
            </div>
        </div>

        <!-- TABS NAV: Kesinambungan & Konsistensi -->
        <div class="flex bg-[#1a1d24] rounded-xl border border-[#2d333b] p-1 mb-6">
            <button onclick="openTab('events')" id="tab-btn-events" class="flex-1 py-2 text-sm font-semibold rounded-lg bg-accentColor text-[#0f1115] transition-all">Match Events</button>
            <button onclick="openTab('stats')" id="tab-btn-stats" class="flex-1 py-2 text-sm font-semibold rounded-lg text-[#a0aab8] hover:text-white transition-all">Statistics</button>
            <button onclick="openTab('lineups')" id="tab-btn-lineups" class="flex-1 py-2 text-sm font-semibold rounded-lg text-[#a0aab8] hover:text-white transition-all">Line-Ups</button>
        </div>

        <!-- TAB CONTENT: EVENTS -->
        <div id="tab-events" class="tab-content active bg-[#1a1d24] rounded-xl border border-[#2d333b] p-6">
            <h3 class="text-lg font-bold mb-6 text-white border-b border-[#2d333b] pb-2">Timeline Kejadian</h3>
            
            @if(isset($match['events']) && count($match['events']) > 0)
                <div class="relative space-y-6 py-4">
                    <!-- Central Line -->
                    <div class="absolute left-1/2 top-0 bottom-0 w-px bg-[#2d333b] transform -translate-x-1/2"></div>
                    
                    @foreach($match['events'] as $event)
                        @php
                            $isHome = $event['team']['id'] == $match['teams']['home']['id'];
                        @endphp
                        <div class="relative flex items-center justify-between w-full group">
                            
                            <!-- Dot Timeline di Tengah -->
                            <div class="absolute left-1/2 w-3 h-3 bg-accentColor rounded-full border-[3px] border-[#1a1d24] transform -translate-x-1/2 shadow-[0_0_8px_rgba(0,255,136,0.5)] z-10"></div>
                            
                            <!-- Kiri (Home) -->
                            <div class="w-1/2 {{ $isHome ? 'pr-6 md:pr-8 text-right' : 'opacity-0' }}">
                                @if($isHome)
                                    <span class="text-xs font-bold text-accentColor mb-1 block">{{ $event['time']['elapsed'] }}' {{ $event['time']['extra'] ? '+'.$event['time']['extra'] : '' }}</span>
                                    <div class="bg-[#0f1115] border border-[#2d333b] hover:border-[#a0aab8] transition-colors rounded-lg p-3 inline-flex items-center gap-3 flex-row-reverse text-right shadow-md ml-auto max-w-full">
                                        <div class="text-xl flex-shrink-0">
                                            @if($event['type'] == 'Goal') ⚽
                                            @elseif($event['type'] == 'Card' && str_contains($event['detail'], 'Yellow')) 🟨
                                            @elseif($event['type'] == 'Card' && str_contains($event['detail'], 'Red')) 🟥
                                            @elseif($event['type'] == 'subst') 🔄
                                            @else ℹ️ @endif
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="font-semibold text-sm truncate">{{ $event['player']['name'] ?? 'Unknown' }}</div>
                                            <div class="text-xs text-[#a0aab8] truncate">
                                                @if($event['type'] == 'subst')
                                                    In: {{ $event['player']['name'] }} | Out: {{ $event['assist']['name'] ?? 'Unknown' }}
                                                @elseif($event['type'] == 'Goal')
                                                    {{ $event['detail'] }} (Assist: {{ $event['assist']['name'] ?? 'None' }})
                                                @else
                                                    {{ $event['detail'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Kanan (Away) -->
                            <div class="w-1/2 {{ !$isHome ? 'pl-6 md:pl-8 text-left' : 'opacity-0' }}">
                                @if(!$isHome)
                                    <span class="text-xs font-bold text-accentColor mb-1 block">{{ $event['time']['elapsed'] }}' {{ $event['time']['extra'] ? '+'.$event['time']['extra'] : '' }}</span>
                                    <div class="bg-[#0f1115] border border-[#2d333b] hover:border-[#a0aab8] transition-colors rounded-lg p-3 inline-flex items-center gap-3 shadow-md mr-auto max-w-full">
                                        <div class="text-xl flex-shrink-0">
                                            @if($event['type'] == 'Goal') ⚽
                                            @elseif($event['type'] == 'Card' && str_contains($event['detail'], 'Yellow')) 🟨
                                            @elseif($event['type'] == 'Card' && str_contains($event['detail'], 'Red')) 🟥
                                            @elseif($event['type'] == 'subst') 🔄
                                            @else ℹ️ @endif
                                        </div>
                                        <div class="overflow-hidden text-left">
                                            <div class="font-semibold text-sm truncate">{{ $event['player']['name'] ?? 'Unknown' }}</div>
                                            <div class="text-xs text-[#a0aab8] truncate">
                                                @if($event['type'] == 'subst')
                                                    In: {{ $event['player']['name'] }} | Out: {{ $event['assist']['name'] ?? 'Unknown' }}
                                                @elseif($event['type'] == 'Goal')
                                                    {{ $event['detail'] }} (Assist: {{ $event['assist']['name'] ?? 'None' }})
                                                @else
                                                    {{ $event['detail'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-[#a0aab8]">Belum ada kejadian yang tercatat.</div>
            @endif
        </div>

        <!-- TAB CONTENT: STATS -->
        <div id="tab-stats" class="tab-content bg-[#1a1d24] rounded-xl border border-[#2d333b] p-6">
            <h3 class="text-lg font-bold mb-6 text-white border-b border-[#2d333b] pb-2">Team Statistics</h3>
            
            @if(isset($match['statistics']) && count($match['statistics']) >= 2)
                @php
                    $homeStats = $match['statistics'][0]['statistics'];
                    $awayStats = $match['statistics'][1]['statistics'];
                @endphp
                
                <div class="space-y-4">
                    @for($i = 0; $i < count($homeStats); $i++)
                        @php
                            $homeVal = $homeStats[$i]['value'] ?? 0;
                            $awayVal = $awayStats[$i]['value'] ?? 0;
                            
                            $hNum = floatval(str_replace('%', '', $homeVal));
                            $aNum = floatval(str_replace('%', '', $awayVal));
                            $total = $hNum + $aNum;
                            $hPct = $total > 0 ? ($hNum / $total) * 100 : 50;
                            $aPct = $total > 0 ? ($aNum / $total) * 100 : 50;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm font-semibold mb-1">
                                <span>{{ is_null($homeVal) ? '0' : $homeVal }}</span>
                                <span class="text-[#a0aab8]">{{ $homeStats[$i]['type'] }}</span>
                                <span>{{ is_null($awayVal) ? '0' : $awayVal }}</span>
                            </div>
                            <div class="flex h-2 w-full rounded-full overflow-hidden bg-[#2d333b]">
                                <div class="bg-accentColor transition-all duration-500" style="width: {{ $hPct }}%"></div>
                                <div class="bg-hotColor transition-all duration-500" style="width: {{ $aPct }}%"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            @else
                <div class="text-center py-8 text-[#a0aab8]">Data statistik tidak tersedia.</div>
            @endif
        </div>

        <!-- TAB CONTENT: LINEUPS -->
        <div id="tab-lineups" class="tab-content bg-[#1a1d24] rounded-xl border border-[#2d333b] p-6">
            <h3 class="text-lg font-bold mb-6 text-white border-b border-[#2d333b] pb-2">Line-Ups</h3>
            
            @if(isset($match['lineups']) && count($match['lineups']) >= 2)
                <div class="flex gap-4">
                    <!-- Home Lineup -->
                    <div class="w-1/2 border-r border-[#2d333b] pr-4">
                        <div class="font-bold text-center mb-2 text-accentColor">{{ $match['lineups'][0]['formation'] ?? '' }}</div>
                        <div class="font-semibold mb-4 text-[#a0aab8] border-b border-[#2d333b] inline-block pb-1">Starting XI</div>
                        <div class="space-y-3">
                            @foreach($match['lineups'][0]['startXI'] ?? [] as $player)
                                <div class="text-sm flex gap-3 items-center">
                                    <span class="w-6 text-[#a0aab8] font-mono font-bold text-right">{{ $player['player']['number'] ?? '-' }}</span> 
                                    <span class="font-semibold">{{ $player['player']['name'] ?? 'Unknown' }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="font-semibold mt-8 mb-4 text-[#a0aab8] border-b border-[#2d333b] inline-block pb-1">Substitutes</div>
                        <div class="space-y-3">
                            @foreach($match['lineups'][0]['substitutes'] ?? [] as $player)
                                <div class="text-sm flex gap-3 items-center">
                                    <span class="w-6 text-[#a0aab8] font-mono text-right">{{ $player['player']['number'] ?? '-' }}</span> 
                                    <span>{{ $player['player']['name'] ?? 'Unknown' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Away Lineup -->
                    <div class="w-1/2 pl-4">
                        <div class="font-bold text-center mb-2 text-hotColor">{{ $match['lineups'][1]['formation'] ?? '' }}</div>
                        <div class="font-semibold mb-4 text-[#a0aab8] border-b border-[#2d333b] inline-block pb-1">Starting XI</div>
                        <div class="space-y-3">
                            @foreach($match['lineups'][1]['startXI'] ?? [] as $player)
                                <div class="text-sm flex gap-3 items-center">
                                    <span class="w-6 text-[#a0aab8] font-mono font-bold text-right">{{ $player['player']['number'] ?? '-' }}</span> 
                                    <span class="font-semibold">{{ $player['player']['name'] ?? 'Unknown' }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="font-semibold mt-8 mb-4 text-[#a0aab8] border-b border-[#2d333b] inline-block pb-1">Substitutes</div>
                        <div class="space-y-3">
                            @foreach($match['lineups'][1]['substitutes'] ?? [] as $player)
                                <div class="text-sm flex gap-3 items-center">
                                    <span class="w-6 text-[#a0aab8] font-mono text-right">{{ $player['player']['number'] ?? '-' }}</span> 
                                    <span>{{ $player['player']['name'] ?? 'Unknown' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-[#a0aab8]">Data Line-ups belum tersedia.</div>
            @endif
        </div>

    </div>

    <!-- Script for Tabs (Interaksi Minimal) -->
    <script>
        function openTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            // Reset all tab buttons
            document.querySelectorAll('[id^="tab-btn-"]').forEach(el => {
                el.classList.remove('bg-accentColor', 'text-[#0f1115]');
                el.classList.add('text-[#a0aab8]');
            });
            
            // Show selected tab content
            document.getElementById('tab-' + tabName).classList.add('active');
            // Highlight selected button
            const btn = document.getElementById('tab-btn-' + tabName);
            btn.classList.remove('text-[#a0aab8]');
            btn.classList.add('bg-accentColor', 'text-[#0f1115]');
        }
    </script>
</body>
</html>
