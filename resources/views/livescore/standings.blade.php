<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasemen {{ $leagueName }} - LiveScore PRO</title>
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
                        borderColor: '#2d333b'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f1115; color: #e1e6f0; font-family: sans-serif; }
    </style>
</head>
<body>
    <div class="max-w-[1000px] mx-auto py-[30px] px-[20px]">
        
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('livescore.index') }}" class="bg-cardBg border border-borderColor hover:bg-borderColor px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                ← Kembali
            </a>
            <h1 class="text-2xl font-black">
                Klasemen <span class="text-accentColor">{{ $leagueName }}</span> ({{ $season }})
            </h1>
        </div>

        @if($error || session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-6 rounded-xl text-center mb-8">
                {{ $error ?? session('error') }}
            </div>
        @endif

        @if(!$error)

        @if(!empty($knockoutMatches))
            <!-- Tab Navigation -->
            <div class="flex flex-wrap gap-4 mb-8">
                <button id="btn-tab-group" onclick="switchTab('group')" class="{{ !empty($standingsGroups) ? 'px-6 py-3 rounded-xl font-bold bg-accentColor text-bgDark' : 'px-6 py-3 rounded-xl font-bold bg-cardBg border border-borderColor text-textMuted' }} transition-all duration-300">
                    Group Stage
                </button>
                <button id="btn-tab-knockout" onclick="switchTab('knockout')" class="{{ empty($standingsGroups) ? 'px-6 py-3 rounded-xl font-bold bg-accentColor text-bgDark' : 'px-6 py-3 rounded-xl font-bold bg-cardBg border border-borderColor hover:border-gray-500' }} transition-all duration-300">
                    Knockout Stage
                </button>
            </div>
            
            <script>
                function switchTab(tab) {
                    const btnGroup = document.getElementById('btn-tab-group');
                    const btnKnockout = document.getElementById('btn-tab-knockout');
                    const contentGroup = document.getElementById('tab-group-stage');
                    const contentKnockout = document.getElementById('tab-knockout-stage');

                    if (tab === 'group') {
                        // Activate group
                        btnGroup.className = 'px-6 py-3 rounded-xl font-bold bg-accentColor text-bgDark transition-all duration-300';
                        btnKnockout.className = 'px-6 py-3 rounded-xl font-bold bg-cardBg border border-borderColor hover:border-gray-500 transition-all duration-300';
                        contentGroup.classList.remove('hidden');
                        contentKnockout.classList.add('hidden');
                    } else {
                        // Activate knockout
                        btnKnockout.className = 'px-6 py-3 rounded-xl font-bold bg-accentColor text-bgDark transition-all duration-300';
                        btnGroup.className = 'px-6 py-3 rounded-xl font-bold bg-cardBg border border-borderColor hover:border-gray-500 transition-all duration-300';
                        contentKnockout.classList.remove('hidden');
                        contentGroup.classList.add('hidden');
                    }
                }
            </script>
        @endif

        <div id="tab-group-stage" class="{{ empty($standingsGroups) ? 'hidden' : '' }}">
            @foreach($standingsGroups as $groupData)
                <div class="bg-cardBg rounded-xl border border-borderColor mb-8 overflow-hidden">
                    
                    @php
                        // Cek apakah ini format grup (misal: "Group A") atau format liga biasa
                        $groupName = $groupData[0]['strGroup'] ?? 'Klasemen';
                    @endphp

                    <div class="bg-[#2d333b] px-6 py-3 font-bold text-sm tracking-wider">
                        {{ $groupName }}
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-textMuted border-b border-borderColor">
                                <tr>
                                    <th class="px-6 py-4 font-semibold w-[60px]">#</th>
                                    <th class="px-6 py-4 font-semibold">Klub/Negara</th>
                                    <th class="px-4 py-4 font-semibold text-center" title="Main">M</th>
                                    <th class="px-4 py-4 font-semibold text-center" title="Menang">W</th>
                                    <th class="px-4 py-4 font-semibold text-center" title="Seri">D</th>
                                    <th class="px-4 py-4 font-semibold text-center" title="Kalah">L</th>
                                    <th class="px-4 py-4 font-semibold text-center" title="Selisih Gol">GD</th>
                                    <th class="px-6 py-4 font-bold text-accentColor text-center" title="Poin">PTS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupData as $teamRow)
                                    <tr class="border-b border-borderColor/50 hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 font-bold">
                                            {{ $teamRow['intRank'] }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $teamRow['strBadge'] }}" alt="Logo" class="w-6 h-6 object-contain">
                                                <span class="font-bold">{{ $teamRow['strTeam'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">{{ $teamRow['intPlayed'] }}</td>
                                        <td class="px-4 py-4 text-center text-green-400">{{ $teamRow['intWin'] }}</td>
                                        <td class="px-4 py-4 text-center text-yellow-400">{{ $teamRow['intDraw'] }}</td>
                                        <td class="px-4 py-4 text-center text-red-400">{{ $teamRow['intLoss'] }}</td>
                                        <td class="px-4 py-4 text-center">{{ $teamRow['intGoalDifference'] }}</td>
                                        <td class="px-6 py-4 text-center font-black text-accentColor text-base">
                                            {{ $teamRow['intPoints'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @endforeach
        </div>

        @if(!empty($knockoutMatches))
        <div id="tab-knockout-stage" class="{{ !empty($standingsGroups) ? 'hidden' : '' }}">
            <div class="grid grid-cols-1 gap-10">
                @foreach($knockoutMatches as $roundName => $matches)
                    <div>
                        <h2 class="text-xl font-black mb-6 flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-accentColor rounded-full inline-block"></span>
                            {{ $roundName }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($matches as $match)
                                @include('components.match-card', ['match' => $match])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @endif

    </div>
</body>
</html>
