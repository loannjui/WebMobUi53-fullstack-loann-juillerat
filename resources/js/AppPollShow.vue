<script setup>
import { ref, computed, onMounted } from "vue";
import { useFetchApi } from "./composables/useFetchApi";

const { fetchApi } = useFetchApi();
const { fetchApi: fetchApiBase } = useFetchApi('/api');

const token = document.getElementById('app-poll-show').dataset.token;

const poll = ref(null);
const currentUser = ref(null);
const loading = ref(true);
const error = ref(null);

// Voting state
const selectedIds = ref([]);
const voted = ref(false);
const voting = ref(false);
const voteError = ref(null);

const totalVotes = computed(() =>
    (poll.value?.options ?? []).reduce((sum, o) => sum + (o.votes_count ?? 0), 0)
);

const canVote = computed(() =>
    currentUser.value && (!voted.value || poll.value?.allow_vote_change)
);

function pct(option) {
    if (!totalVotes.value) return 0;
    return Math.round(((option.votes_count ?? 0) / totalVotes.value) * 100);
}

function toggleOption(optionId) {
    if (!canVote.value) return;

    if (poll.value.allow_multiple_choices) {
        const idx = selectedIds.value.indexOf(optionId);
        if (idx === -1) selectedIds.value.push(optionId);
        else selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value = [optionId];
    }
}

async function submitVote() {
    if (!selectedIds.value.length || voting.value) return;

    voting.value = true;
    voteError.value = null;

    try {
        await fetchApi({
            url: `polls/${poll.value.id}/vote`,
            data: { option_ids: selectedIds.value },
        });
        voted.value = true;
        // Refresh poll to get updated vote counts
        const refreshed = await fetchApi({ url: `polls/${token}` });
        poll.value = refreshed;
    } catch (err) {
        voteError.value = err?.data?.message ?? "Erreur lors du vote.";
    } finally {
        voting.value = false;
    }
}

onMounted(async () => {
    try {
        currentUser.value = await fetchApiBase({ url: 'user' });
    } catch {
        // Not logged in
    }

    try {
        poll.value = await fetchApi({ url: `polls/${token}` });
    } catch {
        error.value = "Sondage introuvable.";
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <p v-if="loading" class="text-slate-500 dark:text-slate-400">Chargement...</p>

        <p v-else-if="error" class="text-red-500">{{ error }}</p>

        <div v-else-if="poll">
            <!-- En-tête -->
            <div class="flex items-center gap-3 mb-4">
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

            <h1 v-if="poll.title" class="text-2xl font-bold text-slate-900 dark:text-white mb-1">
                {{ poll.title }}
            </h1>
            <p class="text-lg text-slate-700 dark:text-slate-300 font-medium mb-6">
                {{ poll.question }}
            </p>

            <!-- Confirmation de vote -->
            <p v-if="voted && !poll.allow_vote_change" class="mb-4 text-sm font-medium text-teal-600 dark:text-teal-400">
                Vote enregistré.
            </p>
            <p v-if="voted && poll.allow_vote_change" class="mb-2 text-sm text-slate-400 dark:text-slate-500">
                Vous avez déjà voté — vous pouvez modifier votre choix.
            </p>

            <!-- Options -->
            <ul v-if="poll.options?.length" class="mb-6 space-y-3">
                <li
                    v-for="option in poll.options"
                    :key="option.id"
                    @click="toggleOption(option.id)"
                    :class="[
                        'rounded-lg border p-4 transition',
                        canVote ? 'cursor-pointer' : 'cursor-default',
                        canVote && selectedIds.includes(option.id)
                            ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/30'
                            : canVote
                                ? 'border-slate-200 dark:border-slate-600 hover:border-teal-400 dark:hover:border-teal-500 bg-white dark:bg-slate-800'
                                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
                    ]"
                >
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2 text-slate-900 dark:text-white text-sm font-medium">
                            <span v-if="canVote">
                                {{ poll.allow_multiple_choices
                                    ? (selectedIds.includes(option.id) ? '☑' : '☐')
                                    : (selectedIds.includes(option.id) ? '◉' : '○') }}
                            </span>
                            {{ option.label }}
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ option.votes_count ?? 0 }} vote{{ (option.votes_count ?? 0) !== 1 ? 's' : '' }} ({{ pct(option) }}%)
                        </span>
                    </div>
                    <div class="h-1.5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        <div
                            class="h-full rounded-full bg-teal-500 dark:bg-teal-600 transition-all"
                            :style="{ width: pct(option) + '%' }"
                        />
                    </div>
                </li>
            </ul>

            <!-- Actions -->
            <div>
                <!-- Non connecté -->
                <p v-if="!currentUser" class="text-sm text-slate-400 dark:text-slate-500 italic">
                    <a href="/auth/login" class="underline text-teal-600 hover:text-teal-500">Connectez-vous</a> pour voter.
                </p>

                <!-- Voté sans possibilité de modifier -->
                <template v-else-if="voted && !poll.allow_vote_change">
                    <!-- résultats déjà affichés ci-dessus -->
                </template>

                <!-- Peut voter ou modifier -->
                <div v-else>
                    <p v-if="voteError" class="text-sm text-red-500 mb-2">{{ voteError }}</p>
                    <button
                        @click="submitVote"
                        :disabled="!selectedIds.length || voting"
                        class="px-5 py-2 rounded-md text-sm font-medium transition"
                        :class="selectedIds.length && !voting
                            ? 'bg-teal-600 hover:bg-teal-500 text-white'
                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                    >
                        {{ voting ? 'Envoi...' : voted ? 'Modifier mon vote' : 'Voter' }}
                    </button>
                </div>
            </div>

            <!-- Total -->
            <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">{{ totalVotes }} vote{{ totalVotes !== 1 ? 's' : '' }} au total</p>

            <!-- Lien retour -->
            <a href="/polls" class="inline-block mt-6 text-sm text-teal-600 hover:underline">← Retour aux sondages</a>
        </div>
    </div>
</template>
