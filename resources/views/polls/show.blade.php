<x-default-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-show.js'])
    </x-slot>

    <x-slot:title>Sondage</x-slot>

    <div id="app-poll-show" data-token="{{ $token }}"></div>
</x-default-layout>
