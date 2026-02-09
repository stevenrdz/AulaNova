<template>
  <div
    class="fixed top-4 right-4 z-[60] flex flex-col gap-2"
    role="status"
    aria-live="polite"
  >
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="px-4 py-3 rounded-md shadow text-sm text-white"
      :class="toastClass(toast.type)"
    >
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { useToastStore, ToastType } from '@/store/toast'

const { toasts } = useToastStore()

const toastClass = (type: ToastType) => {
  if (type === 'success') return 'bg-emerald-600'
  if (type === 'error') return 'bg-rose-600'
  return 'bg-slate-700'
}
</script>
