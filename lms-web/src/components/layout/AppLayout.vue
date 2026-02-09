<template>
  <div id="app-layout" class="overflow-x-hidden flex" :class="{ toggled: isToggled }">
    <component :is="sidebar" />
    <div
      id="app-layout-content"
      class="min-h-screen w-full min-w-[100vw] md:min-w-0 ml-[15.625rem] [transition:margin_0.25s_ease-out]"
    >
      <Topbar @toggle="toggle" />
      <div class="p-6">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Topbar from './Topbar.vue'

defineProps<{ sidebar: any }>()

const isToggled = ref(false)

const toggle = () => {
  isToggled.value = !isToggled.value
}
</script>
