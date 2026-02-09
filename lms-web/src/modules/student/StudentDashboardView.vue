<template>
  <PageHeader title="Dashboard estudiante" subtitle="Tu actividad reciente." />

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <BaseCard>
      <h4 class="mb-2">Tiempo de conexion total</h4>
      <div class="text-2xl font-semibold text-gray-800">{{ formattedTotal }}</div>
    </BaseCard>
    <BaseCard>
      <h4 class="mb-2">Tiempo por curso</h4>
      <ul class="space-y-2">
        <li v-for="course in byCourse" :key="course.curso_id ?? 'general'" class="flex justify-between">
          <span>{{ course.curso_name || 'General' }}</span>
          <span class="font-semibold">{{ formatDuration(course.seconds) }}</span>
        </li>
        <li v-if="byCourse.length === 0" class="text-gray-500">Sin registros aun.</li>
      </ul>
    </BaseCard>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import { trackingApi } from '@/api/tracking'
import { formatDuration } from '@/utils/time'

interface CourseTime {
  curso_id: number | null
  curso_name: string | null
  seconds: number
}

const totalSeconds = ref(0)
const byCourse = ref<CourseTime[]>([])

const formattedTotal = computed(() => formatDuration(totalSeconds.value))

const loadSummary = async () => {
  try {
    const response = await trackingApi.summary()
    totalSeconds.value = response.data.data.total_seconds || 0
    byCourse.value = response.data.data.by_course || []
  } catch {
    // ignore
  }
}

onMounted(loadSummary)
</script>
