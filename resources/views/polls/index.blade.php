<x-default-layout>
    <x-slot:title>
        {{ __('ui.polls.index.title') }}
    </x-slot>

    <x-slot:description>
        {{ __('ui.polls.index.description', ['app_name' => config('app.name')]) }}
    </x-slot>

    <h1 class="text-2xl font-bold dark:text-white">
        {{ __('ui.polls.index.title') }}
    </h1>

    <p class="mt-4 dark:text-gray-300">
        {{ __('ui.polls.index.description', ['app_name' => config('app.name')]) }}
    </p>

    <div class="mt-8 space-y-6">
        @forelse ($polls as $poll)
            <x-poll-card :poll="$poll" />
        @empty
            <p class="text-gray-500 dark:text-gray-400">Aucun sondage publié pour l'instant.</p>
        @endforelse
    </div>
</x-default-layout>
