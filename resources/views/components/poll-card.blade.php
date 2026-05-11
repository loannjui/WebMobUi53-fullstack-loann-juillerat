<article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
    <header class="mb-4">
        <div class="flex items-center gap-3 mb-3">
            <a href="{{ url('@' . $poll->user->username) }}">
                <div class="h-10 w-10 rounded-full bg-teal-600 dark:bg-purple-900 flex items-center justify-center text-white font-semibold hover:bg-teal-700 dark:hover:bg-purple-800">
                    {{ strtoupper(substr($poll->user->first_name, 0, 1) . substr($poll->user->last_name, 0, 1)) }}
                </div>
            </a>
            <div>
                <a href="{{ url('@' . $poll->user->username) }}" class="hover:underline">
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $poll->user->first_name }} {{ $poll->user->last_name }}
                    </p>
                </a>
                <p class="text-sm text-gray-500 dark:text-gray-400" title="{{ $poll->created_at->isoFormat('LLLL') }}">
                    {{ $poll->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        @if ($poll->title)
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                {{ $poll->title }}
            </h2>
        @endif

        <p class="text-gray-700 dark:text-gray-300 font-medium">
            {{ $poll->question }}
        </p>
    </header>

    @if ($poll->options->isNotEmpty())
        <ul class="mb-4 space-y-2">
            @foreach ($poll->options as $option)
                <li class="px-4 py-2 rounded-md bg-slate-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200 text-sm">
                    {{ $option->label }}
                </li>
            @endforeach
        </ul>
    @endif

    <footer class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
        @if ($poll->allow_multiple_choices)
            <span class="px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300">Choix multiple</span>
        @endif
        @if ($poll->results_public)
            <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Résultats publics</span>
        @endif
        @if ($poll->duration)
            <span>Durée : {{ round($poll->duration / 86400) }}j</span>
        @endif
    </footer>
</article>
