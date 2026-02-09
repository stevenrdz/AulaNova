<template>
  <AppLayout :sidebar="SidebarAdmin">
    <RouterView />
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue'
import SidebarAdmin from '@/components/layout/SidebarAdmin.vue'
</script>
