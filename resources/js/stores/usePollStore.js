import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const polls = ref([]);

export function usePollStore() {
  const { fetchApi } = useFetchApi();

  function setPolls(data) {
    polls.value = data;
  }

  function addPoll(poll) {
    polls.value.unshift(poll);
  }

  function updatePoll(updatedPoll) {
    const index = polls.value.findIndex(p => p.id === updatedPoll.id);
    if (index !== -1) polls.value[index] = updatedPoll;
  }

  async function deletePoll(id) {
    const result = await fetchApi({ url: 'polls/' + id, method: 'DELETE' });
    if (result !== undefined) {
      polls.value = polls.value.filter(p => p.id !== id);
    }
  }

  return { polls, setPolls, addPoll, updatePoll, deletePoll };
}