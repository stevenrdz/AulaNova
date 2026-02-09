<template>
  <PageHeader
    title="Cursos"
    subtitle="Cursos asignados."
  />

  <div class="rounded-2xl bg-gradient-to-r from-sky-500 via-teal-500 to-emerald-500 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Gestor docente
        </p>
        <h2 class="text-2xl font-semibold">
          Tus cursos activos
        </h2>
      </div>
      <div class="bg-white/20 rounded-xl px-4 py-2">
        <div class="text-xs uppercase tracking-wide opacity-80">
          Total listados
        </div>
        <div class="text-lg font-semibold">
          {{ items.length }}
        </div>
      </div>
    </div>
  </div>

  <BaseCard class="mt-6">
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex gap-2 items-center">
        <input
          v-model="filters.q"
          class="form-input"
          placeholder="Buscar..."
        >
        <BaseButton
          variant="secondary"
          @click="load"
        >
          Filtrar
        </BaseButton>
      </div>
    </div>

    <div
      v-if="errorMessage"
      class="mt-3 text-red-600"
    >
      {{ errorMessage }}
    </div>

    <div class="mt-4 overflow-x-auto">
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
              Acciones
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="4"
              class="py-3 text-gray-500"
            >
              Cargando...
            </td>
          </tr>
          <tr
            v-for="item in items"
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
              <RouterLink
                class="text-blue-600"
                :to="`/teacher/cursos/${item.id}`"
              >
                Ver detalle
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
      <span>Pagina {{ meta.page }} de {{ meta.total_pages }}</span>
      <div class="flex gap-2">
        <BaseButton
          variant="secondary"
          :disabled="meta.page <= 1"
          @click="prevPage"
        >
          Anterior
        </BaseButton>
        <BaseButton
          variant="secondary"
          :disabled="meta.page >= meta.total_pages"
          @click="nextPage"
        >
          Siguiente
        </BaseButton>
      </div>
    </div>
  </BaseCard>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import { virtualApi } from '@/api/virtual'

const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ q: '' })

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.q) params.q = filters.q
    const { data } = await virtualApi.cursos.list(params)
    items.value = data.data
    meta.total_pages = data.meta.total_pages
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'Error al cargar datos'
  } finally {
    loading.value = false
  }
}

const prevPage = () => {
  if (meta.page > 1) {
    meta.page -= 1
    load()
  }
}

const nextPage = () => {
  if (meta.page < meta.total_pages) {
    meta.page += 1
    load()
  }
}

onMounted(load)
</script>
