@props(['match'])

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
@endphp

<div class="bg-[linear-gradient(135deg,#1f2937_0%,#111827_100%)] rounded-[24px] p-[40px] mb-[40px] border border-white/10 shadow-[0_20px_40px_rgba(0,0,0,0.4)] relative overflow-hidden">
    <div class="absolute -top-1/2 -right-1/2 w-full h-full bg-[radial-gradient(circle,rgba(255,255,255,0.05)_0%,transparent_60%)] pointer-events-none"></div>

    <div class="absolute top-[20px] left-[20px] bg-[#ff3366] text-white py-[6px] px-[16px] rounded-[20px] text-[12px] font-extrabold tracking-[1px] shadow-[0_0_15px_rgba(255,51,102,0.5)] z-10">
        🔥 BIG MATCH
    </div>

    <div class="relative z-10 w-full">
        <div class="text-center font-bold mb-[30px] tracking-[2px] text-accentColor">
            {{ $match['league']['name'] }} • <span style="color: {{ $isLive ? 'var(--accent-color)' : '#fff' }}">{{ $statusDisplay }}</span>
        </div>

        <div class="flex justify-center items-center gap-[20px] md:gap-[40px]">
            <div class="flex flex-col items-center flex-1">
                <div class="w-[80px] h-[80px] text-[32px] bg-white/10 border-2 border-white/20 shadow-[0_10px_20px_rgba(0,0,0,0.3)] flex justify-center items-center rounded-full overflow-hidden">
                    <img src="{{ $match['teams']['home']['logo'] }}" alt="{{ $match['teams']['home']['name'] }}" class="w-full h-full object-cover p-2" />
                </div>
                <span class="text-[18px] md:text-[24px] font-extrabold mt-[15px] drop-shadow-md text-center text-white">
                    {{ $match['teams']['home']['name'] }}
                </span>
            </div>

            <div class="text-[40px] md:text-[64px] font-black tracking-[5px] drop-shadow-lg text-center min-w-[120px] text-white">
                {{ $match['goals']['home'] ?? 0 }} - {{ $match['goals']['away'] ?? 0 }}
            </div>

            <div class="flex flex-col items-center flex-1">
                <div class="w-[80px] h-[80px] text-[32px] bg-white/10 border-2 border-white/20 shadow-[0_10px_20px_rgba(0,0,0,0.3)] flex justify-center items-center rounded-full overflow-hidden">
                    <img src="{{ $match['teams']['away']['logo'] }}" alt="{{ $match['teams']['away']['name'] }}" class="w-full h-full object-cover p-2" />
                </div>
                <span class="text-[18px] md:text-[24px] font-extrabold mt-[15px] drop-shadow-md text-center text-white">
                    {{ $match['teams']['away']['name'] }}
                </span>
            </div>
        </div>
    </div>
</div>
