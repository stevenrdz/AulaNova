<template>
  <div class="flex flex-col items-center justify-center g-0 h-screen px-4 bg-gray-100">
    <div class="justify-center items-center w-full bg-white rounded-md shadow lg:flex md:mt-0 max-w-md xl:p-0">
      <div class="p-6 w-full sm:p-8 lg:p-8">
        <div class="mb-4">
          <img src="/logo-primary.svg" class="mb-1" alt="LMS" />
          <p class="mb-6">Ingresa el OTP y tu nueva contraseña.</p>
        </div>
        <form @submit.prevent="onSubmit">
          <div class="mb-3">
            <label for="email" class="inline-block mb-2">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="Email"
              required
            />
          </div>
          <div class="mb-3">
            <label for="otp" class="inline-block mb-2">OTP</label>
            <input
              id="otp"
              v-model="form.otp"
              type="text"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="123456"
              required
            />
          </div>
          <div class="mb-5">
            <label for="password" class="inline-block mb-2">Nueva contraseña</label>
            <input
              id="password"
              v-model="form.new_password"
              type="password"
              class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
              placeholder="********"
              required
            />
          </div>
          <p v-if="message" class="text-green-700 text-sm mb-3">{{ message }}</p>
          <p v-if="error" class="text-red-600 text-sm mb-3">{{ error }}</p>
          <div class="mb-3 grid">
            <button
              type="submit"
              class="btn bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-800 hover:border-indigo-800"
              :disabled="loading"
            >
              {{ loading ? 'Actualizando...' : 'Actualizar' }}
            </button>
          </div>
          <RouterLink to="/login" class="text-indigo-600 text-sm">Volver a login</RouterLink>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { authApi } from '@/api/auth'

const form = reactive({
  email: '',
  otp: '',
  new_password: ''
})

const loading = ref(false)
const message = ref('')
const error = ref('')

const onSubmit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''
  try {
    await authApi.resetPassword({
      email: form.email,
      otp: form.otp,
      new_password: form.new_password
    })
    message.value = 'Password actualizado. Ya puedes ingresar.'
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudo actualizar.'
  } finally {
    loading.value = false
  }
}
</script>
