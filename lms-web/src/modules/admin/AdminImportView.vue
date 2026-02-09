<template>
  <PageHeader
    title="Importacion"
    subtitle="Carga masiva de datos."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex gap-2 items-center">
        <select
          v-model="filters.type"
          class="form-select"
        >
          <option value="">
            Tipo
          </option>
          <option value="users">
            Usuarios
          </option>
        </select>
        <select
          v-model="filters.status"
          class="form-select"
        >
          <option value="">
            Estado
          </option>
          <option value="pending">
            Pendiente
          </option>
          <option value="processing">
            Procesando
          </option>
          <option value="completed">
            Completado
          </option>
          <option value="failed">
            Fallido
          </option>
        </select>
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
              Tipo
            </th>
            <th class="py-2">
              Estado
            </th>
            <th class="py-2">
              Totales
            </th>
            <th class="py-2">
              Creado
            </th>
            <th class="py-2">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="6"
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
              {{ item.type }}
            </td>
            <td class="py-2">
              {{ item.status }}
            </td>
            <td class="py-2">
              {{ item.success_count ?? 0 }}/{{ item.total_rows ?? 0 }}
            </td>
            <td class="py-2">
              {{ item.created_at }}
            </td>
            <td class="py-2">
              <BaseButton
                variant="secondary"
                @click="openDetail(item)"
              >
                Ver
              </BaseButton>
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

  <BaseModal
    v-model="showModal"
    title="Detalle de importacion"
  >
    <div
      v-if="detail"
      class="grid gap-3 text-sm"
    >
      <div><strong>ID:</strong> {{ detail.data.id }}</div>
      <div><strong>Estado:</strong> {{ detail.data.status }}</div>
      <div><strong>Totales:</strong> {{ detail.data.success_count ?? 0 }}/{{ detail.data.total_rows ?? 0 }}</div>
      <div><strong>Errores:</strong> {{ detail.data.error_count ?? 0 }}</div>
      <div
        v-if="detail.errors?.length"
        class="border-t pt-3"
      >
        <div class="font-semibold mb-2">
          Errores
        </div>
        <ul class="list-disc pl-5">
          <li
            v-for="err in detail.errors"
            :key="err.id"
          >
            Fila {{ err.row_number }} - {{ err.message }}
          </li>
        </ul>
      </div>
    </div>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showModal = false"
      >
        Cerrar
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { importsApi } from '@/api/imports'

const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ type: '', status: '' })

const showModal = ref(false)
const detail = ref<any | null>(null)

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.type) params.type = filters.type
    if (filters.status) params.status = filters.status
    const { data } = await importsApi.listBatches(params)
    items.value = data.data
    meta.total_pages = data.meta.total_pages
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'Error al cargar datos'
  } finally {
    loading.value = false
  }
}

const openDetail = async (item: any) => {
  try {
    const { data } = await importsApi.getBatch(item.id)
    detail.value = data
    showModal.value = true
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo cargar el detalle'
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
