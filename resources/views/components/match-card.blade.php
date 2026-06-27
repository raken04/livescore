@props(['match', 'size' => 'default'])

@php
    $isUpcoming = in_array($match['fixture']['status']['short'], ['NS', 'TBD']);
    $matchTime = \Carbon\Carbon::parse($match['fixture']['date'])->format('H:i');
    
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
        $statusDisplay = $isUpcoming ? $matchTime : $match['fixture']['status']['short'];
    }
    
    $isLive = str_starts_with($statusDisplay, 'LIVE');
    $isSmall = $size === 'small';
@endphp

@php
    if (!function_exists('formatPlaceholderName')) {
        function formatPlaceholderName($name) {
            if (!$name) return 'TBD';
            $name = str_replace('Winner Group ', 'Juara Grup ', $name);
            $name = str_replace('Runner-up Group ', 'Pos 2 Grup ', $name);
            if (str_starts_with($name, '3rd Group')) {
                $groups = str_replace('3rd Group ', '', $name);
                return "Pos 3 Terbaik ($groups)";
            }
            return $name;
        }
    }
    
    $homeName = formatPlaceholderName($match['teams']['home']['name']);
    $awayName = formatPlaceholderName($match['teams']['away']['name']);
@endphp

<a href="{{ route('livescore.show', ['id' => $match['fixture']['id'], 'source' => $match['_source'] ?? 'apisports']) }}"
   class="block bg-[#1a1d24] rounded-[16px] border border-[#2d333b] transition-all duration-300 hover:-translate-y-[5px] hover:border-gray-500 hover:shadow-[0_10px_20px_rgba(0,0,0,0.2)] {{ $isSmall ? 'p-[15px]' : 'p-[20px] min-w-[300px]' }}">

    <div class="flex justify-between text-[#a0aab8] font-semibold {{ $isSmall ? 'text-[11px] mb-[10px]' : 'text-[13px] mb-[20px]' }}">
        <span class="truncate max-w-[60%]">{{ $match['league']['name'] }}</span>
        <span class="{{ $isLive ? 'text-accentColor' : '' }}">{{ $statusDisplay }}</span>
    </div>
    
    <div class="flex justify-between items-center">
        <div class="flex flex-col items-center w-[35%]">
            <div class="{{ $isSmall ? 'w-[36px] h-[36px] mb-[8px]' : 'w-[48px] h-[48px] mb-[12px]' }} bg-[#2d333b] rounded-full flex justify-center items-center text-[18px] font-bold text-[#a0aab8] overflow-hidden p-1">
                <img src="{{ $match['teams']['home']['logo'] }}" alt="{{ $homeName }}" class="w-full h-full object-contain" />
            </div>
            <span class="font-semibold text-center line-clamp-2 leading-tight w-full text-white {{ $isSmall ? 'text-[12px]' : 'text-[14px]' }}" title="{{ $homeName }}">
                {{ $homeName }}
            </span>
        </div>
        
        <div class="w-[30%] text-center">
            <span class="font-extrabold text-white {{ $isSmall ? 'text-[20px]' : 'text-[28px]' }}">
                {{ $match['goals']['home'] ?? 0 }} - {{ $match['goals']['away'] ?? 0 }}
            </span>
        </div>
        
        <div class="flex flex-col items-center w-[35%]">
            <div class="{{ $isSmall ? 'w-[36px] h-[36px] mb-[8px]' : 'w-[48px] h-[48px] mb-[12px]' }} bg-[#2d333b] rounded-full flex justify-center items-center text-[18px] font-bold text-[#a0aab8] overflow-hidden p-1">
                <img src="{{ $match['teams']['away']['logo'] }}" alt="{{ $awayName }}" class="w-full h-full object-contain" />
            </div>
            <span class="font-semibold text-center line-clamp-2 leading-tight w-full text-white {{ $isSmall ? 'text-[12px]' : 'text-[14px]' }}" title="{{ $awayName }}">
                {{ $awayName }}
            </span>
        </div>
    </div>

</a>
