<script setup>
import { ref, watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import PollPublicCard from "./components/PollPublicCard.vue";

const { fetchApiToRef } = useFetchApi();

const polls = ref([]);
const loading = ref(true);
const error = ref(null);

const { data, error: fetchError, fetchNow } = fetchApiToRef({ url: "polls" });

watch(data, (val) => {
    if (val) {
        polls.value = val;
        loading.value = false;
    }
});

watch(fetchError, (err) => {
    if (err) {
        error.value = "Impossible de charger les sondages.";
        loading.value = false;
    }
});

usePolling(fetchNow);
</script>

<template>
    <h1 class="text-2xl font-bold dark:text-white mb-4">Tous les sondages</h1>

    <p v-if="loading" class="text-slate-500 dark:text-slate-400">Chargement...</p>

    <p v-else-if="error" class="text-red-500">{{ error }}</p>

    <p v-else-if="polls.length === 0" class="text-slate-500 dark:text-slate-400">
        Aucun sondage publié pour l'instant.
    </p>

    <div v-else class="space-y-6">
        <PollPublicCard v-for="poll in polls" :key="poll.id" :poll="poll" />
    </div>
</template>
