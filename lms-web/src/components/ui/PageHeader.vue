<template>
  <div class="flex flex-col mb-4 border-b border-gray-300 pb-4">
    <h1 class="block font-semibold leading-6 text-xl mb-1">
      {{ title }}
    </h1>
    <p
      v-if="subtitle"
      class="text-lg"
    >
      {{ subtitle }}
    </p>
    <div
      v-if="$slots.actions"
      class="mt-3 flex gap-2"
    >
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string; subtitle?: string }>()
</script>
