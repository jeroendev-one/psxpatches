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