<template>
  <AppLayout :sidebar="SidebarStudent">
    <RouterView />
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue'
import SidebarStudent from '@/components/layout/SidebarStudent.vue'
import { useHeartbeat } from '@/utils/heartbeat'

useHeartbeat()
</script>
