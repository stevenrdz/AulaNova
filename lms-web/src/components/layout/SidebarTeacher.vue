<template>
  <nav class="navbar-vertical navbar">
    <div
      id="myScrollableElement"
      class="h-screen overflow-y-auto"
    >
      <RouterLink
        class="navbar-brand"
        to="/teacher/dashboard"
      >
        <img
          :src="logoUrl"
          alt="LMS"
        >
      </RouterLink>

      <ul
        id="sideNavbar"
        class="navbar-nav flex-col"
      >
        <li class="nav-item">
          <RouterLink
            class="nav-link"
            active-class="active"
            to="/teacher/dashboard"
          >
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Dashboard
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink
            class="nav-link"
            active-class="active"
            to="/teacher/cursos"
          >
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Cursos
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { institutionApi } from '@/api/institution'
import { filesApi } from '@/api/files'

const logoUrl = ref('/logo.svg')

const loadLogo = async () => {
  try {
    const response = await institutionApi.get()
    const value = response.data.data?.logo_url
    if (!value) return
    if (typeof value === 'string' && value.startsWith('http')) {
      logoUrl.value = value
      return
    }
    const numericId = Number(value)
    if (!Number.isNaN(numericId) && String(numericId) === String(value).trim()) {
      const download = await filesApi.download(numericId, { disposition: 'inline' })
      const url = download.data.data?.url
      if (url) {
        logoUrl.value = url
      }
    }
  } catch {
    // keep default logo
  }
}

onMounted(loadLogo)
</script>
