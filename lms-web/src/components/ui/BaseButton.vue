<template>
  <button :type="type" :class="classes" :disabled="disabled">
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ variant?: 'primary' | 'secondary'; type?: 'button' | 'submit'; disabled?: boolean }>()

const classes = computed(() => {
  if (props.variant === 'secondary') {
    return 'btn bg-white text-gray-800 border-gray-600 hover:bg-gray-100 hover:text-gray-800 hover:border-gray-200'
  }
  return 'btn btn-primary'
})
</script>
