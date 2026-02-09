<template>
  <div class="flex flex-col items-center justify-center g-0 h-screen px-4 bg-gray-100">
    <div class="justify-center items-center w-full bg-white rounded-md shadow lg:flex md:mt-0 max-w-md xl:p-0">
      <div class="p-6 w-full sm:p-8 lg:p-8">
        <div class="mb-4">
          <img
            src="/logo-primary.svg"
            class="mb-1"
            alt="LMS"
          >
          <p class="mb-6">
            Por favor ingresa tus credenciales.
          </p>
        </div>
        <form @submit.prevent="onSubmit">
          <div class="mb-3">
            <label
              for="email"
              class="inline-block mb-2"
            >Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="Email"
              required
            >
          </div>
          <div class="mb-5">
            <label
              for="password"
              class="inline-block mb-2"
            >Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="********"
              required
            >
          </div>
          <p
            v-if="error"
            class="text-red-600 text-sm mb-3"
          >
            {{ error }}
          </p>
          <div class="grid">
            <button
              type="submit"
              class="btn bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-800 hover:border-indigo-800"
              :disabled="loading"
            >
              {{ loading ? 'Ingresando...' : 'Ingresar' }}
            </button>
          </div>
          <div class="flex justify-between mt-4 text-sm">
            <RouterLink
              to="/forgot-password"
              class="text-indigo-600"
            >
              Olvidaste tu password?
            </RouterLink>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const onSubmit = async () => {
  error.value = ''
  loading.value = true
  try {
    await authStore.login(form.email, form.password)
    if (authStore.hasRole('ROLE_ADMIN')) {
      await router.push('/admin/dashboard')
      return
    }
    if (authStore.hasRole('ROLE_TEACHER')) {
      await router.push('/teacher/dashboard')
      return
    }
    await router.push('/student/dashboard')
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudo iniciar sesion.'
  } finally {
    loading.value = false
  }
}
</script>
