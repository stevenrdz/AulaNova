<template>
  <PageHeader
    title="Cursos"
    subtitle="Tus cursos activos."
  />

  <div class="rounded-2xl bg-gradient-to-r from-sky-500 via-emerald-500 to-lime-500 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Explora tu ruta
        </p>
        <h2 class="text-2xl font-semibold">
          Cursos disponibles
        </h2>
      </div>
      <div class="flex flex-wrap gap-3">
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Total
          </div>
          <div class="text-lg font-semibold">
            {{ totalCourses }}
          </div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Con actividad
          </div>
          <div class="text-lg font-semibold">
            {{ activeCount }}
          </div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Sin actividad
          </div>
          <div class="text-lg font-semibold">
            {{ inactiveCount }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <BaseCard class="mt-6">
    <div class="flex gap-2 mb-4 flex-wrap">
      <BaseButton
        variant="secondary"
        @click="filter = 'all'"
      >
        Todos
      </BaseButton>
      <BaseButton
        variant="secondary"
        @click="filter = 'active'"
      >
        Con actividad
      </BaseButton>
      <BaseButton
        variant="secondary"
        @click="filter = 'inactive'"
      >
        Sin actividad
      </BaseButton>
    </div>

    <div
      v-if="errorMessage"
      class="mb-3 text-red-600"
    >
      {{ errorMessage }}
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2">
              ID
            </th>
            <th class="py-2">
              Curso
            </th>
            <th class="py-2">
              Descripcion
            </th>
            <th class="py-2">
              Tiempo
            </th>
            <th class="py-2">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="5"
              class="py-3 text-gray-500"
            >
              Cargando...
            </td>
          </tr>
          <tr
            v-for="item in filteredItems"
            :key="item.id"
            class="border-t"
          >
            <td class="py-2">
              {{ item.id }}
            </td>
            <td class="py-2">
              <span class="font-semibold text-slate-800">{{ item.curso?.name || '-' }}</span>
            </td>
            <td class="py-2">
              {{ item.description || '-' }}
            </td>
            <td class="py-2">
              {{ formatDuration(getCourseSeconds(item.curso?.name)) }}
            </td>
            <td class="py-2">
              <RouterLink
                class="text-blue-600"
                :to="`/student/cursos/${item.id}`"
              >
                Ver detalle
              </RouterLink>
            </td>
          </tr>
          <tr v-if="!loading && filteredItems.length === 0">
            <td
              colspan="5"
              class="py-3 text-gray-500"
            >
              No hay cursos disponibles.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </BaseCard>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import { virtualApi } from '@/api/virtual'
import { trackingApi } from '@/api/tracking'
import { formatDuration } from '@/utils/time'

const filter = ref<'all' | 'active' | 'inactive'>('all')
const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const trackingByCourse = ref<Record<string, number>>({})

const totalCourses = computed(() => items.value.length)
const activeCount = computed(() => items.value.filter((item) => getCourseSeconds(item.curso?.name) > 0).length)
const inactiveCount = computed(() => items.value.filter((item) => getCourseSeconds(item.curso?.name) === 0).length)

const getCourseSeconds = (courseName?: string | null) => {
  if (!courseName) return 0
  return trackingByCourse.value[courseName] || 0
}

const filteredItems = computed(() => {
  const data = items.value
  if (filter.value === 'active') {
    return data.filter((item) => getCourseSeconds(item.curso?.name) > 0)
  }
  if (filter.value === 'inactive') {
    return data.filter((item) => getCourseSeconds(item.curso?.name) === 0)
  }
  return data
})

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const [coursesResponse, trackingResponse] = await Promise.all([
      virtualApi.cursos.list({ page: 1, limit: 200 }),
      trackingApi.summary()
    ])
    items.value = coursesResponse.data.data || []
    const byCourse = trackingResponse.data.data?.by_course || []
    trackingByCourse.value = byCourse.reduce((acc: Record<string, number>, item: any) => {
      if (item.curso_name) acc[item.curso_name] = item.seconds || 0
      return acc
    }, {})
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo cargar los cursos'
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>
