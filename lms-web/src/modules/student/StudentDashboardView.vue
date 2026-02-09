<template>
  <PageHeader
    title="Dashboard estudiante"
    subtitle="Tu actividad reciente."
  />

  <div class="rounded-2xl bg-gradient-to-r from-cyan-500 via-sky-500 to-blue-600 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Resumen general
        </p>
        <h2 class="text-2xl font-semibold">
          Tiempo total conectado
        </h2>
      </div>
      <div class="bg-white/20 rounded-xl px-4 py-2">
        <div class="text-xs uppercase tracking-wide opacity-80">
          Total
        </div>
        <div class="text-lg font-semibold">
          {{ formattedTotal }}
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Tiempo de conexion total
      </h4>
      <div class="text-2xl font-semibold text-gray-800">
        {{ formattedTotal }}
      </div>
    </BaseCard>
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Tiempo por curso
      </h4>
      <ul class="space-y-2">
        <li
          v-for="course in byCourse"
          :key="course.curso_id ?? 'general'"
          class="flex justify-between"
        >
          <span>{{ course.curso_name || 'General' }}</span>
          <span class="font-semibold">{{ formatDuration(course.seconds) }}</span>
        </li>
        <li
          v-if="byCourse.length === 0"
          class="text-gray-500"
        >
          Sin registros aun.
        </li>
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
