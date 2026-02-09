<template>
  <PageHeader
    title="Dashboard docente"
    subtitle="Resumen general."
  />

  <div class="rounded-2xl bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Actividad del curso
        </p>
        <h2 class="text-2xl font-semibold">
          Seguimiento docente
        </h2>
      </div>
      <div class="bg-white/20 rounded-xl px-4 py-2">
        <div class="text-xs uppercase tracking-wide opacity-80">
          Tiempo total
        </div>
        <div class="text-lg font-semibold">
          {{ formattedTotal }}
        </div>
      </div>
    </div>
  </div>

  <div
    v-if="errorMessage"
    class="mb-4 text-red-600"
  >
    {{ errorMessage }}
  </div>

  <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-3">
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Tiempo total de actividad
      </h4>
      <div class="text-2xl font-semibold text-gray-800">
        {{ formattedTotal }}
      </div>
    </BaseCard>
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Top cursos
      </h4>
      <ul class="space-y-2 text-sm">
        <li
          v-for="course in topCourses"
          :key="course.curso_id ?? course.curso_name"
          class="flex justify-between"
        >
          <span>{{ course.curso_name || 'General' }}</span>
          <span class="font-semibold">{{ formatDuration(course.seconds) }}</span>
        </li>
        <li
          v-if="topCourses.length === 0"
          class="text-gray-500"
        >
          Sin registros.
        </li>
      </ul>
    </BaseCard>
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Top estudiantes
      </h4>
      <ul class="space-y-2 text-sm">
        <li
          v-for="user in topUsers"
          :key="user.user_id"
          class="flex justify-between"
        >
          <span>{{ user.first_name }} {{ user.last_name }}</span>
          <span class="font-semibold">{{ formatDuration(user.seconds) }}</span>
        </li>
        <li
          v-if="topUsers.length === 0"
          class="text-gray-500"
        >
          Sin registros.
        </li>
      </ul>
    </BaseCard>
  </div>

  <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Rutas con mayor uso
      </h4>
      <ul class="space-y-2 text-sm">
        <li
          v-for="route in topRoutes"
          :key="route.route"
          class="flex justify-between"
        >
          <span>{{ route.route }}</span>
          <span class="font-semibold">{{ formatDuration(route.seconds) }}</span>
        </li>
        <li
          v-if="topRoutes.length === 0"
          class="text-gray-500"
        >
          Sin registros.
        </li>
      </ul>
    </BaseCard>
    <BaseCard>
      <h4 class="mb-2 text-slate-700 font-semibold">
        Actividad por dia
      </h4>
      <ul class="space-y-2 text-sm">
        <li
          v-for="day in byDay"
          :key="day.day"
          class="flex justify-between"
        >
          <span>{{ day.day }}</span>
          <span class="font-semibold">{{ formatDuration(day.seconds) }}</span>
        </li>
        <li
          v-if="byDay.length === 0"
          class="text-gray-500"
        >
          Sin registros.
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

interface SummaryItem {
  seconds: number
}

interface CourseSummary extends SummaryItem {
  curso_id: number | null
  curso_name: string | null
}

interface UserSummary extends SummaryItem {
  user_id: number
  first_name: string
  last_name: string
}

interface RouteSummary extends SummaryItem {
  route: string
}

interface DaySummary extends SummaryItem {
  day: string
}

const totalSeconds = ref(0)
const byCourse = ref<CourseSummary[]>([])
const byUser = ref<UserSummary[]>([])
const byRoute = ref<RouteSummary[]>([])
const byDay = ref<DaySummary[]>([])
const errorMessage = ref('')

const formattedTotal = computed(() => formatDuration(totalSeconds.value))

const sortedBySeconds = <T extends SummaryItem>(items: T[]) =>
  [...items].sort((a, b) => b.seconds - a.seconds)

const topCourses = computed(() => sortedBySeconds(byCourse.value).slice(0, 5))
const topUsers = computed(() => sortedBySeconds(byUser.value).slice(0, 5))
const topRoutes = computed(() => sortedBySeconds(byRoute.value).slice(0, 5))

const loadSummary = async () => {
  errorMessage.value = ''
  try {
    const response = await trackingApi.teacherSummary()
    const data = response.data.data || {}
    totalSeconds.value = data.total_seconds || 0
    byCourse.value = data.by_course || []
    byUser.value = data.by_user || []
    byRoute.value = data.by_route || []
    byDay.value = data.by_day || []
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo cargar el resumen'
  }
}

onMounted(loadSummary)
</script>
