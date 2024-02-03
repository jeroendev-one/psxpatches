@extends('layouts.app')

@section('title', $game->title_id)

@section('content')

<div class="flex items-center bg-white border border-gray-200 rounded-lg shadow-md md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-dark-eval-1 dark:hover:bg-gray-700">
    <img class="object-cover rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-l-lg" src="{{ Storage::url($game->icon) }}" alt="{{ $game->name }} Icon">
    <div class="flex flex-col justify-between p-4 leading-normal">
        <h3 class="mb-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $game->name }}</h3>
        <h6 class="mb-2 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Publisher: {{ $game->publisher }}</h6>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Content ID: {{ $game->content_id }}</p>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Region: {{ $game->region }}</p>
    </div>
</div>

@if (!$game->patches->isEmpty())
    <div class="container mt-8 max-w-md"> <!-- Adjusted max-width to control the width -->
        <ul class="grid gap-4 md:grid-cols-1 lg:grid-cols-1">
            @php
                $sortedPatches = $game->patches->sortByDesc('version');
            @endphp
            @foreach ($sortedPatches as $patch)
                <li class="bg-white border border-gray-200 rounded-lg shadow p-4 dark:bg-dark-eval-1 dark:border-gray-700">
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Version: {{ $patch->version }} </h3> <br>
                    <strong class="text-gray-600 dark:text-gray-400">Size: {{ $patch->size }}</strong><br>
                    <!-- Add more details if needed -->
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection