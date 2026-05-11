<x-default-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-index.js'])
    </x-slot>

    <x-slot:title>
        {{ __('ui.polls.index.title') }}
    </x-slot>

    <div id="app-poll-index"></div>
</x-default-layout>
