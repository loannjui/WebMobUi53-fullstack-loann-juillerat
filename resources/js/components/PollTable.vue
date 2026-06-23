<script setup>
import { ref } from "vue";

function remaining(poll) {
    if (!poll.ends_at) return null;
    const diff = new Date(poll.ends_at) - Date.now(); // Donne l'écart en milisecondes
    if (diff <= 0) return null;
    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000); // Retire les jours entiers puis calcule en heure.
    const minutes = Math.floor((diff % 3600000) / 60000); // Retire les heures entières pour calculer les minutes
    // On crée un tableau avec les différentes valeurs.
    const parts = [];
    if (days > 0) parts.push(`${days}j`);
    if (hours > 0) parts.push(`${hours}h`);
    parts.push(`${minutes}min`);
    // Puis on le change en string
    return parts.join(" ");
}
import { usePollStore } from "@/stores/usePollStore";
import EditPollModal from "./EditPollModal.vue";

const { polls, deletePoll } = usePollStore();

const showEditModal = ref(false);
const selectedPoll = ref(null);
const copiedPollId = ref(null);

async function delPoll(id) {
    console.log("delete Poll ID:", id);
    await deletePoll(id);
}
// Déclenche l'afficahge de la modale côté parent.
function openEdit(poll) {
    selectedPoll.value = poll;
    showEditModal.value = true;
}

async function copyShareLink(poll) {
    const url = `${window.location.origin}/polls/${poll.secret_token}`;
    await navigator.clipboard.writeText(url); // Copie l'url dans le presse-papier
    // feedback visuel pour le copié
    copiedPollId.value = poll.id;
    setTimeout(() => {
        copiedPollId.value = null;
    }, 2000);
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
                    <h2
                        v-if="poll.title"
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        {{ poll.title }}
                    </h2>
                    <p class="text-slate-700 dark:text-slate-300 font-medium">
                        {{ poll.question }}
                    </p>
                </div>
                <span
                    class="shrink-0 px-2 py-0.5 rounded text-xs font-medium"
                    :class="
                        poll.is_draft
                            ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300'
                            : 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300'
                    "
                >
                    {{ poll.is_draft ? "Brouillon" : "Publié" }}
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
            <p
                v-else
                class="text-sm text-slate-400 dark:text-slate-500 mb-4 italic"
            >
                Aucune option définie.
            </p>

            <!-- Boutons d'action -->
            <div class="mb-3 flex gap-2">
                <a
                    v-if="!poll.is_draft"
                    :href="`/polls/${poll.secret_token}`"
                    class="px-3 py-1.5 text-sm rounded-md bg-teal-600 hover:bg-teal-500 text-white transition"
                    >Voir le sondage</a
                >
                <button
                    v-if="!poll.is_draft"
                    @click="copyShareLink(poll)"
                    class="px-3 py-1.5 text-sm rounded-md transition"
                    :class="
                        copiedPollId === poll.id
                            ? 'bg-green-600 dark:bg-green-700 text-white'
                            : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                    "
                >
                    {{
                        copiedPollId === poll.id
                            ? "Lien copié !"
                            : "Copier le lien"
                    }}
                </button>
                <button
                    @click="openEdit(poll)"
                    :disabled="!poll.is_draft"
                    :title="
                        !poll.is_draft
                            ? 'Un sondage publié ne peut plus être modifié'
                            : ''
                    "
                    class="px-3 py-1.5 text-sm rounded-md transition"
                    :class="
                        poll.is_draft
                            ? 'bg-teal-600 dark:bg-teal-800 text-white hover:bg-teal-700 dark:hover:bg-teal-700'
                            : 'bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 cursor-not-allowed'
                    "
                >
                    Éditer
                </button>
                <button
                    @click="delPoll(poll.id)"
                    class="px-3 py-1.5 text-sm bg-red-600 dark:bg-red-900 text-white rounded-md hover:bg-red-700 dark:hover:bg-red-800 transition"
                >
                    Supprimer
                </button>
            </div>

            <!-- Métadonnées -->
            <div
                class="flex gap-2 flex-wrap pt-3 border-t border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400"
            >
                <span
                    v-if="poll.allow_multiple_choices"
                    class="px-2 py-0.5 rounded bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300"
                    >Choix multiple</span
                >
                <span
                    v-if="poll.results_public"
                    class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                    >Résultats publics</span
                >
                <span v-if="poll.ends_at">
                    {{
                        remaining(poll)
                            ? remaining(poll) + " restant"
                            : "Terminé"
                    }}
                </span>
            </div>
        </article>
    </div>

    <EditPollModal v-model="showEditModal" :poll="selectedPoll" />
</template>
