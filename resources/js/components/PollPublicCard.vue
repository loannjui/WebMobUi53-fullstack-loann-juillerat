<script setup>
defineProps({
    poll: { type: Object, required: true },
});
</script>

<template>
    <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border border-slate-200 dark:border-slate-700">
        <!-- Auteur et date -->
        <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full bg-teal-600 dark:bg-purple-900 flex items-center justify-center text-white font-semibold shrink-0">
                {{ poll.user.first_name[0].toUpperCase() }}{{ poll.user.last_name[0].toUpperCase() }}
            </div>
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ poll.user.first_name }} {{ poll.user.last_name }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(poll.created_at).toLocaleDateString('fr-CH') }}
                </p>
            </div>
        </div>

        <!-- Titre et question -->
        <h2 v-if="poll.title" class="text-lg font-bold text-slate-900 dark:text-white mb-1">
            {{ poll.title }}
        </h2>
        <p class="text-slate-700 dark:text-slate-300 font-medium mb-4">
            {{ poll.question }}
        </p>

        <!-- Options de réponse -->
        <ul v-if="poll.options?.length" class="mb-4 space-y-2">
            <li
                v-for="option in poll.options"
                :key="option.id"
                class="px-4 py-2 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm"
            >
                {{ option.label }}
            </li>
        </ul>

        <!-- Badges de métadonnées -->
        <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
            <span v-if="poll.allow_multiple_choices" class="px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300">Choix multiple</span>
            <span v-if="poll.results_public" class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Résultats publics</span>
            <span v-if="poll.duration" class="text-slate-500 dark:text-slate-400">Durée : {{ Math.round(poll.duration / 86400) }}j</span>
        </div>
    </article>
</template>
