<script setup>
import { computed } from "vue";

const props = defineProps({
    poll: { type: Object, required: true },
});

const totalVotes = computed(() =>
    (props.poll.options ?? []).reduce((sum, o) => sum + (o.votes_count ?? 0), 0)
);

function pct(option) {
    if (!totalVotes.value) return 0;
    return Math.round(((option.votes_count ?? 0) / totalVotes.value) * 100);
}
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

        <!-- Résultats par option -->
        <ul v-if="poll.options?.length" class="mb-4 space-y-2">
            <li v-for="option in poll.options" :key="option.id">
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="text-slate-800 dark:text-slate-200">{{ option.label }}</span>
                    <span class="text-slate-500 dark:text-slate-400">{{ option.votes_count ?? 0 }} vote{{ (option.votes_count ?? 0) !== 1 ? 's' : '' }} ({{ pct(option) }}%)</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                    <div
                        class="h-full rounded-full bg-teal-500 dark:bg-teal-600 transition-all"
                        :style="{ width: pct(option) + '%' }"
                    />
                </div>
            </li>
        </ul>

        <!-- Bouton  Voir le sondage -->
        <div class="mb-3">
            <a
                :href="`/polls/${poll.secret_token}`"
                class="inline-block px-4 py-1.5 rounded-md text-sm font-medium bg-teal-600 hover:bg-teal-500 text-white transition"
            >
                Voir le sondage
            </a>
            <span class="ml-3 text-xs text-slate-500 dark:text-slate-400">{{ totalVotes }} vote{{ totalVotes !== 1 ? 's' : '' }} au total</span>
        </div>

        <!-- Badges de métadonnées -->
        <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
            <span v-if="poll.allow_multiple_choices" class="px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300">Choix multiple</span>
            <span v-if="poll.results_public" class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Résultats publics</span>
            <span v-if="poll.allow_vote_change" class="px-2 py-0.5 rounded bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300">Vote modifiable</span>
            <span v-if="poll.duration" class="text-slate-500 dark:text-slate-400">Durée : {{ Math.round(poll.duration / 86400) }}j</span>
        </div>
    </article>
</template>
