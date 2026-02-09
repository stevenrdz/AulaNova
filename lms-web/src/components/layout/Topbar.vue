<template>
  <div class="header">
    <nav class="bg-white px-6 py-[10px] flex items-center justify-between shadow-sm">
      <button id="nav-toggle" class="text-gray-800" type="button" @click="$emit('toggle')">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="w-6 h-6"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </button>
      <div class="ml-3 hidden lg:block">
        <form class="flex items-center">
          <input
            type="search"
            class="border border-gray-300 text-gray-900 rounded focus:ring-indigo-600 focus:border-indigo-600 block w-full p-2 px-3"
            placeholder="Search"
          />
        </form>
      </div>
      <ul class="flex ml-auto items-center gap-4">
        <li class="text-gray-600 hidden md:block">{{ displayName }}</li>
        <li>
          <button
            class="btn bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-800 hover:border-indigo-800"
            type="button"
            @click="logout"
          >
            Sign out
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const authStore = useAuthStore()
const router = useRouter()

const displayName = computed(() => {
  const user = authStore.user
  if (!user) return ''
  return `${user.first_name} ${user.last_name}`
})

const logout = async () => {
  await authStore.logout()
  await router.push('/login')
}
</script>
