<script setup>
import { ref, onMounted } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import PollPublicCard from "./components/PollPublicCard.vue";

const { fetchApi } = useFetchApi();

const polls = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        polls.value = await fetchApi({ url: "polls" });
    } catch (err) {
        error.value = "Impossible de charger les sondages.";
        console.error(err);
    } finally {
        loading.value = false;
    }
});
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
