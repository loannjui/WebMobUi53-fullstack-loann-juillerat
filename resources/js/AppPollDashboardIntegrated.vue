<script setup>
import { watch } from "vue";
import { ref } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";

import PollTable from "./components/PollTable.vue";
import CreatePollModal from "./components/CreatePollModal.vue";

const props = defineProps({
    loginUrl: { type: String, default: null },
});

const { fetchApiToRef } = useFetchApi();

const {
    data: getResult,
    error: getError,
    fetchNow,
} = fetchApiToRef({ url: "polls/" });
const { data: postResult, error: postError } = fetchApiToRef({
    url: "/foo",
    data: { id: 1 },
});

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
        window.location.href = props.loginUrl;
    } else {
        console.error(err);
    }
}

watch(getError, (err) => handleError(err));
watch(postError, handleError);

usePolling(fetchNow);

const showModal = ref(false);
</script>

<template>
    <h1 class="text-3xl">Mes sondages</h1>

    <PollTable :polls="getResult || []" />

    <section>
        <h2>GET /api/v1/polls</h2>
        <pre v-if="getResult">{{ getResult }}</pre>
        <p v-else>Chargement...</p>
    </section>

    <section>
        <h2>POST /api/v1/foo</h2>
        <pre v-if="postResult">{{ postResult }}</pre>
        <p v-else>Chargement...</p>
    </section>

    <button
        @click="showModal = true"
        href="/polls/dashboard-integrated/new-poll"
        class="px-4 py-2 bg-teal-600 dark:bg-purple-900 text-white rounded-md hover:bg-teal-700 dark:hover:bg-purple-800"
    >
        Créer un nouveau sondage
    </button>

    <CreatePollModal v-model="showModal" />
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
