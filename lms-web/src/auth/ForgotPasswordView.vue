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
            Te enviaremos un codigo OTP para resetear tu password.
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
              v-model="email"
              type="email"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="Email"
              required
            >
          </div>
          <p
            v-if="message"
            class="text-green-700 text-sm mb-3"
          >
            {{ message }}
          </p>
          <p
            v-if="error"
            class="text-red-600 text-sm mb-3"
          >
            {{ error }}
          </p>
          <div class="mb-3 grid">
            <button
              type="submit"
              class="btn bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-800 hover:border-indigo-800"
              :disabled="loading"
            >
              {{ loading ? 'Enviando...' : 'Enviar OTP' }}
            </button>
          </div>
          <RouterLink
            to="/login"
            class="text-indigo-600 text-sm"
          >
            Volver a login
          </RouterLink>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { authApi } from '@/api/auth'

const email = ref('')
const loading = ref(false)
const message = ref('')
const error = ref('')

const onSubmit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''
  try {
    await authApi.forgotPassword(email.value)
    message.value = 'Si el correo existe, enviaremos el OTP.'
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudo enviar el OTP.'
  } finally {
    loading.value = false
  }
}
</script>
