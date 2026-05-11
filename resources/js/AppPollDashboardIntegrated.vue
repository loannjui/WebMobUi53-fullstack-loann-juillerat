<script setup>
import { watch } from "vue";
import { ref } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import { usePollStore } from "@/stores/usePollStore";

import PollTable from "./components/PollTable.vue";
import CreatePollModal from "./components/CreatePollModal.vue";

const props = defineProps({
    loginUrl: { type: String, default: null },
    username: { type: String, default: null },
    polls: { type: Array, default: () => [] },
});

// Initialise le store avec les polls du backend
const { setPolls } = usePollStore();
setPolls(props.polls);

// Ton fetch existant pour le polling/refresh
const { fetchApiToRef } = useFetchApi();
const { data: getResult, error: getError, fetchNow } = fetchApiToRef({ url: "my-polls" });

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
        window.location.href = props.loginUrl;
    } else {
        console.error(err);
    }
}

watch(getError, handleError);

// Met à jour le store quand le fetch rafraîchit les données
watch(getResult, (val) => {
    if (val) setPolls(Array.isArray(val) ? val : val.data ?? []);
});

usePolling(fetchNow);

const showModal = ref(false);
</script>

<template>
    <h1 class="text-3xl">Mes sondages</h1>

    <PollTable />

    <button
        @click="showModal = true"
        class="px-4 py-2 bg-teal-600 dark:bg-purple-900 text-white rounded-md hover:bg-teal-700 dark:hover:bg-purple-800"
    >
        Créer un nouveau sondage
    </button>

    <CreatePollModal v-model="showModal" />
</template>