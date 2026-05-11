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
        duration.value = p.duration ? Math.round(p.duration / 86400) : 0;
    },
    { immediate: true }
);

function close() {
    emit("update:modelValue", false);
}

async function submit() {
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
                    <input
                        v-model="title"
                        type="text"
                        placeholder="Titre du sondage"
                        class="mb-4 w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
                    <input
                        v-model="question"
                        type="text"
                        placeholder="Votre question"
                        class="mb-4 w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
                    <div class="mb-4">
                        <div class="text-slate-900 dark:text-white">
                            <input v-model="is_draft" type="checkbox" />
                            <label for="is_draft">Brouillon</label>
                        </div>
                        <div class="text-slate-900 dark:text-white">
                            <input v-model="multiple_choice" type="checkbox" />
                            <label for="multiple_choice">Choix multiple</label>
                        </div>
                        <div class="text-slate-900 dark:text-white">
                            <input
                                v-model="allow_vote_change"
                                type="checkbox"
                            />
                            <label for="allow_vote_change"
                                >Changement de votes</label
                            >
                        </div>
                        <div class="text-slate-900 dark:text-white">
                            <input v-model="results_public" type="checkbox" />
                            <label for="results_public"
                                >Résultats publics</label
                            >
                        </div>
                    </div>
                    <input
                        v-model="duration"
                        type="number"
                        placeholder="En jour"
                        min="0"
                        max="30"
                        class="w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
                    />
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
