@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Stats -->    
<div class="w-full bg-white rounded-lg shadow dark:bg-dark-eval-1 ">
    <div>
        <div class="p-4 bg-white rounded-lg md:p-8 dark:bg-dark-eval-1" id="stats" >
            <dl class="grid max-w-screen-xl grid-cols-2 gap-8 p-4 mx-auto text-gray-900 sm:grid-cols-3 xl:grid-cols-3 dark:text-white sm:p-8">
                <div class="flex flex-col items-center justify-center">
                    <dt class="mb-2 text-3xl font-extrabold">{{ $total_games }}</dt>
                    <dd class="text-gray-500 dark:text-gray-400">Games</dd>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <dt class="mb-2 text-3xl font-extrabold">{{ $total_patches }}</dt>
                    <dd class="text-gray-500 dark:text-gray-400">Patches</dd>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <dt class="mb-2 text-3xl font-extrabold">{{ $day_patches }}</dt>
                    <dd class="text-gray-500 dark:text-gray-400">New in last 24h</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
<!-- End stats -->

<!-- Search -->
<div>
<form>   
    <label for="liveSearch" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
    <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
        </div>
        <input type="text" id="liveSearchInput" class="block w-full p-4 ps-10 text-sm dark:text-white text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-red-500 focus:border-red-500 dark:bg-dark-eval-1 dark:border-gray-600 dark:placeholder-white dark:ring-red-500 dark:border-red-500" placeholder="Search" required>
        <button type="submit" class="text-white absolute end-2.5 bottom-2.5 bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-500 dark:hover:bg-red-700 dark:ring-red-800">Search</button>
        <div id="liveSearchDropdown" class="hidden absolute mt-1 bg-white rounded-md shadow-lg dark:bg-dark-eval-1">
            <!-- Dropdown items -->
        </div>
    </div>
</form>
</div>
<!-- End search -->


<!-- Cards -->
<div class="flex flex-wrap justify-center items-stretch mt-8 space-x-4">
    @foreach($games as $game)
        <div class="w-72 h-104 bg-white border-white rounded-lg shadow dark:bg-dark-eval-0 dark:border-gray-700 mb-8 mt-4">
            <a href="{{ route('details', ['title_id' => $game->title_id]) }}" class="h-3/4 block">
                @if($game->icon)
                    <img class="h-full w-full rounded-t-lg object-cover" src="{{ Storage::url($game->icon) }}" alt="" />
                @else
                    <!-- Placeholder or fallback image -->
                    <div class="h-full w-full bg-gray-200"></div>
                @endif
            </a>
            <div class="p-5 flex flex-col h-1/3">
                <a href="#" class="h-full flex flex-col justify-center">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white overflow-hidden whitespace-nowrap">{{ $game->name }}</h5>
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400 overflow-hidden whitespace-nowrap">{{ $game->title_id }}</p>
                </a>
            </div>
        </div>
    @endforeach
    {{ $games->links() }}
</div>
<!-- End of cards -->
<script>
const liveSearchInput = document.getElementById('liveSearchInput');
const liveSearchDropdown = document.getElementById('liveSearchDropdown');
let searchTimer;

liveSearchInput.addEventListener('input', function() {
    const query = liveSearchInput.value.trim();

    // Clear previous search timer
    clearTimeout(searchTimer);

    // Set a new timer to wait before triggering the search
    searchTimer = setTimeout(() => {
        if (query.length > 0) {
            // Make an AJAX request to your liveSearch route
            fetch(`/search?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    // Clear previous results
                    liveSearchDropdown.innerHTML = '';

                    if (data.length > 0) {
                        // Populate the dropdown with links to details pages
                        data.forEach(game => {
                            const link = document.createElement('a');
                            link.href = `/details/${game.title_id}`;
                            link.textContent = `${game.name} - ${game.title_id}`;
                            link.classList.add('block', 'py-2', 'px-4', 'text-sm', 'text-gray-900', 'hover:bg-gray-100', 'dark:text-white', 'dark:hover:bg-dark-eval-2');
                            liveSearchDropdown.appendChild(link);
                        });

                        // Show the dropdown
                        liveSearchDropdown.classList.remove('hidden');
                    } else {
                        // Hide the dropdown if there are no results
                        liveSearchDropdown.classList.add('hidden');
                    }
                });
        } else {
            // Hide the dropdown if the input is empty
            liveSearchDropdown.classList.add('hidden');
        }
    }, 500); // Adjust the delay time as needed
});
</script>

@endsection
