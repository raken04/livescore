@props(['match', 'size' => 'default'])

@php
    $isUpcoming = in_array($match['fixture']['status']['short'], ['NS', 'TBD']);
    $matchTime = \Carbon\Carbon::parse($match['fixture']['date'])->format('H:i');
    
    $isLiveStatus = in_array($match['fixture']['status']['short'], ['1H', '2H', 'HT', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE']);
    
    if ($isLiveStatus) {
        $statusDisplay = "LIVE " . ($match['fixture']['status']['elapsed'] ?? '') . "'";
    } else {
        $statusDisplay = $isUpcoming ? $matchTime : $match['fixture']['status']['short'];
    }
    
    $isLive = str_starts_with($statusDisplay, 'LIVE');
    $isSmall = $size === 'small';
@endphp

<div class="bg-[#1a1d24] rounded-[16px] border border-[#2d333b] transition-all duration-300 hover:-translate-y-[5px] hover:border-gray-500 hover:shadow-[0_10px_20px_rgba(0,0,0,0.2)] {{ $isSmall ? 'p-[15px]' : 'p-[20px] min-w-[300px]' }}">
    <div class="flex justify-between text-[#a0aab8] font-semibold {{ $isSmall ? 'text-[11px] mb-[10px]' : 'text-[13px] mb-[20px]' }}">
        <span class="truncate max-w-[60%]">{{ $match['league']['name'] }}</span>
        <span class="{{ $isLive ? 'text-accentColor' : '' }}">{{ $statusDisplay }}</span>
    </div>
    
    <div class="flex justify-between items-center">
        <div class="flex flex-col items-center w-[35%]">
            <div class="{{ $isSmall ? 'w-[36px] h-[36px] mb-[8px]' : 'w-[48px] h-[48px] mb-[12px]' }} bg-[#2d333b] rounded-full flex justify-center items-center text-[18px] font-bold text-[#a0aab8] overflow-hidden p-1">
                <img src="{{ $match['teams']['home']['logo'] }}" alt="{{ $match['teams']['home']['name'] }}" class="w-full h-full object-contain" />
            </div>
            <span class="font-semibold text-center truncate w-full text-white {{ $isSmall ? 'text-[13px]' : 'text-[15px]' }}">
                {{ $match['teams']['home']['name'] }}
            </span>
        </div>
        
        <div class="w-[30%] text-center">
            <span class="font-extrabold text-white {{ $isSmall ? 'text-[20px]' : 'text-[28px]' }}">
                {{ $match['goals']['home'] ?? 0 }} - {{ $match['goals']['away'] ?? 0 }}
            </span>
        </div>
        
        <div class="flex flex-col items-center w-[35%]">
            <div class="{{ $isSmall ? 'w-[36px] h-[36px] mb-[8px]' : 'w-[48px] h-[48px] mb-[12px]' }} bg-[#2d333b] rounded-full flex justify-center items-center text-[18px] font-bold text-[#a0aab8] overflow-hidden p-1">
                <img src="{{ $match['teams']['away']['logo'] }}" alt="{{ $match['teams']['away']['name'] }}" class="w-full h-full object-contain" />
            </div>
            <span class="font-semibold text-center truncate w-full text-white {{ $isSmall ? 'text-[13px]' : 'text-[15px]' }}">
                {{ $match['teams']['away']['name'] }}
            </span>
        </div>
    </div>
</div>
