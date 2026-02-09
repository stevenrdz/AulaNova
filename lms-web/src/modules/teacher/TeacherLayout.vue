<template>
  <AppLayout :sidebar="SidebarTeacher">
    <RouterView />
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue'
import SidebarTeacher from '@/components/layout/SidebarTeacher.vue'
</script>
