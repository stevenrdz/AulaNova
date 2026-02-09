import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'info'

export interface Toast {
  id: number
  message: string
  type: ToastType
}

const toasts = ref<Toast[]>([])
let nextId = 1

export const useToastStore = () => {
  const add = (message: string, type: ToastType = 'info', timeout = 4000) => {
    const id = nextId++
    toasts.value.push({ id, message, type })
    if (timeout > 0) {
      setTimeout(() => remove(id), timeout)
    }
    return id
  }

  const remove = (id: number) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  return { toasts, add, remove }
}
