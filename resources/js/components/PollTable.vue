<script setup>
import { ref } from "vue";
import { usePollStore } from "@/stores/usePollStore";
import EditPollModal from "./EditPollModal.vue";

const { polls, deletePoll } = usePollStore();

const showEditModal = ref(false);
const selectedPoll = ref(null);

async function delPoll(id) {
    console.log("delete Poll ID:", id);
    await deletePoll(id);
}

function openEdit(poll) {
    selectedPoll.value = poll;
    showEditModal.value = true;
}
</script>

<template>
    <p v-if="polls.length === 0" class="text-gray-500 dark:text-gray-400 mt-4">
        Aucun sondage.
    </p>

    <div v-else class="mt-4 mb-4 space-y-4">
        <article
            v-for="poll in polls"
            :key="poll.id"
            class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6 border border-slate-200 dark:border-slate-700"
        >
            <!-- En-tête : titre, question et badge brouillon -->
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <h2 v-if="poll.title" class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ poll.title }}
                    </h2>
                    <p class="text-slate-700 dark:text-slate-300 font-medium">
                        {{ poll.question }}
                    </p>
                </div>
                <span
                    class="shrink-0 px-2 py-0.5 rounded text-xs font-medium"
                    :class="poll.is_draft
                        ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300'
                        : 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300'"
                >
                    {{ poll.is_draft ? 'Brouillon' : 'Publié' }}
                </span>
            </div>

            <!-- Liste des options de réponse -->
            <ul v-if="poll.options?.length" class="mb-4 space-y-1">
                <li
                    v-for="option in poll.options"
                    :key="option.id"
                    class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm"
                >
                    {{ option.label }}
                </li>
            </ul>
            <p v-else class="text-sm text-slate-400 dark:text-slate-500 mb-4 italic">
                Aucune option définie.
            </p>

            <!-- Métadonnées et actions -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                <div class="flex gap-2 flex-wrap text-xs text-slate-500 dark:text-slate-400">
                    <span v-if="poll.allow_multiple_choices" class="px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300">Choix multiple</span>
                    <span v-if="poll.results_public" class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Résultats publics</span>
                    <span v-if="poll.duration">Durée : {{ Math.round(poll.duration / 86400) }}j</span>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="openEdit(poll)"
                        :disabled="!poll.is_draft"
                        :title="!poll.is_draft ? 'Un sondage publié ne peut plus être modifié' : ''"
                        class="px-3 py-1.5 text-sm rounded-md transition"
                        :class="poll.is_draft
                            ? 'bg-teal-600 dark:bg-teal-800 text-white hover:bg-teal-700 dark:hover:bg-teal-700'
                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 cursor-not-allowed'"
                    >Éditer</button>
                    <button
                        @click="delPoll(poll.id)"
                        class="px-3 py-1.5 text-sm bg-red-600 dark:bg-red-900 text-white rounded-md hover:bg-red-700 dark:hover:bg-red-800 transition"
                    >Supprimer</button>
                </div>
            </div>
        </article>
    </div>

    <EditPollModal v-model="showEditModal" :poll="selectedPoll" />
</template>
