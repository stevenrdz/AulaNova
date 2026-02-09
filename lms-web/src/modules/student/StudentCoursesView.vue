<template>
  <PageHeader title="Cursos" subtitle="Tus cursos activos." />
  <BaseCard>
    <div class="flex gap-2 mb-4">
      <BaseButton variant="secondary" @click="filter = 'all'">Todos</BaseButton>
      <BaseButton variant="secondary" @click="filter = 'in_progress'">En progreso</BaseButton>
      <BaseButton variant="secondary" @click="filter = 'completed'">Completados</BaseButton>
    </div>
    <p class="text-gray-600">Placeholder de cursos del estudiante.</p>
  </BaseCard>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const filter = ref('all')
</script>
