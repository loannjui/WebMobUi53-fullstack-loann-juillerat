<script setup>
import { ref, watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import PollPublicCard from "./components/PollPublicCard.vue";

const { fetchApiToRef } = useFetchApi();

const polls = ref([]);
const loading = ref(true);
const error = ref(null);

// Enregistre la data, la ref d'erreur, et une fonction pour lancer et relancer la requête
const { data, error: fetchError, fetchNow } = fetchApiToRef({ url: "polls" });

// Dès que data reçoit une valeur... 
watch(data, (val) => { // On envoie la nouvelle valeur de data en callback
    if (val) {
        polls.value = val; // On copie les sondages dans polls
        loading.value = false; // On coupe le chargement
    }
});

// On surveille si des erreurs surviennent et on arrête le chargement
watch(fetchError, (err) => {
    if (err) {
        error.value = "Impossible de charger les sondages.";
        loading.value = false;
    }
});

// Refresh automatique toutes les 5 secondes
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
