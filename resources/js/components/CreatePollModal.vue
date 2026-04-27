<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="close"
    >
      <div
        class="w-full max-w-lg mx-4 rounded-lg bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700"
      >
        <!-- Titre -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
            Créer un sondage
          </h2>
        </div>

        <!-- Formulaire -->
        <div class="px-6 py-4">
          <input
            v-model="title"
            type="text"
            placeholder="Titre du sondage"
            class="w-full px-3 py-2 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-600"
          />
        </div>

        <!-- Envois -->
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
          <button
            @click="close"
            class="px-3 py-1 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
          >
            Annuler
          </button>

          <button
            class="px-3 py-1 rounded-md bg-teal-600 hover:bg-teal-700 text-white transition"
            @click="submit"
          >
            Créer
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

const title = ref('')

function close() {
  emit('update:modelValue', false)
}

function submit() {
  console.log('Créer sondage:', title.value)
  title.value = ''
  close()
}
</script>