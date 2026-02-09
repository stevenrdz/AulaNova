<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/50" @click="close"></div>
    <div class="bg-white rounded-md shadow-lg w-full max-w-lg relative z-10">
      <div class="border-b px-5 py-4 flex items-center justify-between">
        <h4>{{ title }}</h4>
        <button class="text-gray-500" type="button" @click="close">×</button>
      </div>
      <div class="p-5">
        <slot />
      </div>
      <div v-if="$slots.footer" class="border-t px-5 py-4 flex justify-end gap-2">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{ modelValue: boolean; title: string }>()
const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)
</script>
