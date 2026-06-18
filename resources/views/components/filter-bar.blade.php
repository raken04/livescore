@props(['leagues', 'activeLeague', 'selectedDate'])

<nav class="flex gap-[15px] overflow-x-auto pb-[15px] mb-[30px] hide-scrollbar">
    
    @php
        $btnBaseClass = "px-[20px] py-[10px] rounded-full text-[14px] font-bold cursor-pointer transition-all duration-300 border border-[#2d333b]";
        $btnInactiveClass = "bg-[#1a1d24] text-[#a0aab8] hover:bg-[#2d333b] hover:text-[#e1e6f0]";
        $btnActiveClass = "bg-[#e1e6f0] text-[#0f1115] border-[#e1e6f0]";
    @endphp

    <a href="{{ route('livescore.index', ['date' => $selectedDate->format('Y-m-d'), 'league' => 'All']) }}" 
       class="{{ $btnBaseClass }} whitespace-nowrap {{ $activeLeague === 'All' ? $btnActiveClass : $btnInactiveClass }}">
        Semua
    </a>
    
    @foreach ($leagues as $league)
        <a href="{{ route('livescore.index', ['date' => $selectedDate->format('Y-m-d'), 'league' => $league['id']]) }}" 
           class="{{ $btnBaseClass }} whitespace-nowrap {{ $activeLeague == $league['id'] ? $btnActiveClass : $btnInactiveClass }}">
            {{ $league['name'] }}
        </a>
    @endforeach
    
</nav>
