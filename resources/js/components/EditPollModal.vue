<script setup>
import { ref, watch } from "vue";
import { useFetchApi } from "../composables/useFetchApi";
import { usePollStore } from "@/stores/usePollStore";

const props = defineProps({
    modelValue: Boolean,
    poll: { type: Object, default: null },
});
const emit = defineEmits(["update:modelValue"]);

const { fetchApi } = useFetchApi();
const { updatePoll } = usePollStore();

const title = ref("");
const question = ref("");
const is_draft = ref(false);
const multiple_choice = ref(false);
const allow_vote_change = ref(false);
const results_public = ref(false);
const duration = ref(0);
// Liste des options de réponse du sondage en cours d'édition
const options = ref(["", ""]);

// Quand le sondage change (ou à l'ouverture), on remplit tous les champs avec ses données
watch(
    () => props.poll,
    (p) => {
        if (!p) return;
        title.value = p.title ?? "";
        question.value = p.question ?? "";
        is_draft.value = p.is_draft ?? false;
        multiple_choice.value = p.allow_multiple_choices ?? false;
        allow_vote_change.value = p.allow_vote_change ?? false;
        results_public.value = p.results_public ?? false;
        // La durée est stockée en secondes en BDD, on la convertit en jours pour l'affichage
        duration.value = p.duration ? Math.round(p.duration / 86400) : 0;
        // Si le sondage a déjà des options, on les charge ; sinon on repart de 2 champs vides
        options.value = p.options?.length
            ? p.options.map((o) => o.label)
            : ["", ""];
    },
    { immediate: true }
);

// Ajoute un champ vide à la fin de la liste d'options
function addOption() {
    options.value.push("");
}

// Supprime l'option à l'index donné
function removeOption(index) {
    options.value.splice(index, 1);
}

function close() {
    emit("update:modelValue", false);
}

async function submit() {
    // Sécurité : on ne soumet pas si le sondage est déjà publié
    if (!props.poll?.is_draft) return;

    try {
        const result = await fetchApi({
            url: "polls/" + props.poll.id,
            method: "PUT",
            data: {
                title: title.value,
                question: question.value,
                is_draft: is_draft.value,
                allow_multiple_choices: multiple_choice.value,
                allow_vote_change: allow_vote_change.value,
                results_public: results_public.value,
                duration: duration.value,
                // On envoie uniquement les options non vides à l'API
                options: options.value.filter((o) => o.trim() !== ""),
            },
        });
        updatePoll(result);
        close();
    } catch (err) {
        console.error(err);
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="modelValue && poll"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @click.self="close"
        >
            <div
                class="w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700"
            >
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-700"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Modifier le sondage
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="title">Titre du sondage</label>
                    <input
                        v-model="title"
                        type="text"
                        placeholder="Titre du sondage"
                        class="mb-4 w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="question">Votre question</label>
                    <input
                        v-model="question"
                        type="text"
                        placeholder="Votre question"
                        class="mb-4 w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <input v-model="is_draft" class="mr-2" type="checkbox" />
                            <label class="text-sm text-gray-700 dark:text-gray-300" for="is_draft">Publier</label>
                        </div>
                        <div class="flex items-center mb-2">
                            <input class ="mr-2" v-model="multiple_choice" type="checkbox" />
                            <label class="text-sm text-gray-700 dark:text-gray-300" for="multiple_choice">Choix multiple</label>
                        </div>
                        <div class="flex items-center mb-2">
                            <input class="mr-2"
                                v-model="allow_vote_change"
                                type="checkbox"
                            />
                            <label class="text-sm text-gray-700 dark:text-gray-300" for="allow_vote_change"
                                >Changement de votes</label
                            >
                        </div>
                        <div class="flex items-center mb-2">
                            <input class="mr-2" v-model="results_public" type="checkbox" />
                            <label class="text-sm text-gray-700 dark:text-gray-300" for="results_public"
                                >Résultats publics</label
                            >
                        </div>
                    </div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="duration">Durée du sondage (en jour)</label>
                    <input
                        v-model="duration"
                        type="number"
                        placeholder="En jour"
                        min="0"
                        max="30"
                        class="mb-4 w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options de réponse</label>
                        <div
                            v-for="(option, index) in options"
                            :key="index"
                            class="flex gap-2 mb-2"
                        >
                            <input
                                v-model="options[index]"
                                type="text"
                                :placeholder="`Option ${index + 1}`"
                                class="flex-1 px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                            />
                            <button
                                v-if="options.length > 2"
                                @click="removeOption(index)"
                                type="button"
                                class="px-2 py-1 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                            >✕</button>
                        </div>
                        <button
                            @click="addOption"
                            type="button"
                            class="text-sm text-teal-600 hover:underline"
                        >+ Ajouter une option</button>
                    </div>
                </div>
                <div
                    class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2"
                >
                    <button
                        @click="close"
                        class="px-3 py-1 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                    >
                        Annuler
                    </button>

                    <button
                        class="px-3 py-1 rounded-md bg-teal-600 hover:bg-teal-500 text-white transition"
                        @click="submit"
                    >
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
