@props(['selectedDate', 'timezone'])

@php
    $baseClass = "px-4 py-2 rounded-lg transition-colors text-sm font-semibold border";
    $activeClass = "bg-accentColor/10 text-accentColor border-accentColor/30 hover:bg-accentColor/20";
    $inactiveClass = "bg-[#1a1d24] text-[#a0aab8] border-[#2d333b] hover:bg-[#2d333b]";
    
    $todayDate = \Carbon\Carbon::now($timezone)->format('Y-m-d');
    $yesterdayDate = \Carbon\Carbon::now($timezone)->subDay()->format('Y-m-d');
    $tomorrowDate = \Carbon\Carbon::now($timezone)->addDay()->format('Y-m-d');
    
    $formattedSelected = $selectedDate->format('Y-m-d');
@endphp

<div class="flex flex-wrap items-center gap-4 mb-6">
    <div class="flex gap-2">
        <a href="{{ route('livescore.index', ['date' => $yesterdayDate]) }}" 
           class="{{ $baseClass }} {{ $formattedSelected === $yesterdayDate ? $activeClass : $inactiveClass }}">
            Kemarin
        </a>
        
        <a href="{{ route('livescore.index', ['date' => $todayDate]) }}" 
           class="{{ $baseClass }} {{ $formattedSelected === $todayDate ? $activeClass : $inactiveClass }}">
            Hari Ini
        </a>
        
        <a href="{{ route('livescore.index', ['date' => $tomorrowDate]) }}" 
           class="{{ $baseClass }} {{ $formattedSelected === $tomorrowDate ? $activeClass : $inactiveClass }}">
            Besok
        </a>
    </div>

    <div class="relative">
        <form action="{{ route('livescore.index') }}" method="GET" id="dateForm">
            <input 
                type="date" 
                name="date"
                value="{{ $formattedSelected }}"
                onchange="document.getElementById('dateForm').submit()"
                class="bg-[#1a1d24] text-[#a0aab8] border border-[#2d333b] px-4 py-2 rounded-lg focus:outline-none focus:border-accentColor"
            />
        </form>
    </div>
</div>
