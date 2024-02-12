@extends('layouts.app')

@section('title', $game->title_id)

@section('content')

<!-- Header -->
<div class="h-52 relative flex items-center justify-center rounded overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0" style="background-image: url('{{ Storage::url($game->background) }}'); background-size: cover; background-position: center;"></div>

    <!-- Black Overlay -->
    <div class="absolute inset-0 bg-black opacity-50"></div>

    <!-- Game Icon Square -->
    <div class="absolute top-10 right-40 z-20 flex items-center justify-center">
        <img src="{{ Storage::url($game->icon) }}" class="w-32 h-32 rounded">
    </div>

    <!-- Main Text -->
    <h1 class="relative z-10 text-4xl md:text-5xl lg:text-6xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-white">{{ $game->name }}</h1>
</div>
<!-- End of header -->




<!-- Flexbox container for card and table with added spacing -->
<div class="flex mx-auto max-w-5xl mt-8 space-x-12"> <!-- Adjust max-width and space-x as needed -->

    <!-- Card -->
    <div class="w-1/3">
        <a class="block p-6 bg-white rounded-lg shadow hover:bg-gray-100 dark:bg-dark-eval-1 dark:border-gray-700 dark:hover:bg-gray-700">
            <h6 class="mb-1 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Title ID:</h6>
                <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">{{ $game->title_id }}</p>
            <h6 class="mb-1 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Content ID:</h6>
                <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">{{ $game->content_id }}</p>
            <h6 class="mb-1 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Publisher</h6>
                <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">{{ $game->publisher }}</p>
            <h6 class="mb-1 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Region</h6>
                <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">{{ $game->region }}</p>
            <h6 class="mb-1 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Latest version:</h6>
                <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">{{ $game->current_version }}</p>
        </a>
    </div>

    <!-- Table -->
    @if (!$sortedPatches->isEmpty())    
    <div class="flex-grow relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-dark-eval-1">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:white">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-dark-eval-1 dark:text-white">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Version
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Size
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Added
                    </th>
                    <th scope="col" class="px-6 py-3">
                        <span class="sr-only">Get</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sortedPatches as $patch)
                <tr class="bg-white border-b dark:bg-dark-eval-1 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $patch->version }}
                    </th>
                    <td class="px-6 py-4">
                        {{ $patch->size }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $patch->created_at->format('Y-m-d H:i')}}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="#" class="font-medium text-blue-600 dark:text-red-500 hover:underline">Get</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>  
        @else
        <p class="p-4 text-gray-700 dark:text-white">No patches available!</p>
        @endif
    </div>
</div>
@endsection
