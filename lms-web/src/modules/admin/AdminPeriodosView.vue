<template>
  <PageHeader
    title="Periodos"
    subtitle="Gestion de periodos."
  />
  <BaseCard>
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
      <BaseButton @click="openCreate">
        Nuevo periodo
      </BaseButton>
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
              Nombre
            </th>
            <th class="py-2">
              Inicio
            </th>
            <th class="py-2">
              Fin
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
            v-for="item in items"
            :key="item.id"
            class="border-t"
          >
            <td class="py-2">
              {{ item.id }}
            </td>
            <td class="py-2">
              {{ item.name }}
            </td>
            <td class="py-2">
              {{ item.start_date || '-' }}
            </td>
            <td class="py-2">
              {{ item.end_date || '-' }}
            </td>
            <td class="py-2 flex gap-2">
              <BaseButton
                variant="secondary"
                @click="openEdit(item)"
              >
                Editar
              </BaseButton>
              <BaseButton
                variant="secondary"
                @click="remove(item)"
              >
                Eliminar
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
    :title="modalTitle"
  >
    <form
      class="grid gap-3"
      @submit.prevent="save"
    >
      <div>
        <label class="text-sm text-gray-600">Nombre</label>
        <input
          v-model="form.name"
          class="form-input w-full"
          required
        >
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-gray-600">Inicio</label>
          <input
            v-model="form.start_date"
            type="date"
            class="form-input w-full"
          >
        </div>
        <div>
          <label class="text-sm text-gray-600">Fin</label>
          <input
            v-model="form.end_date"
            type="date"
            class="form-input w-full"
          >
        </div>
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showModal = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="save">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { structureApi } from '@/api/structure'

interface Item {
  id: number
  name: string
  start_date?: string | null
  end_date?: string | null
}

const items = ref<Item[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ q: '' })

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({ name: '', start_date: '', end_date: '' })

const modalTitle = computed(() => (editingId.value ? 'Editar periodo' : 'Nuevo periodo'))

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.q) params.q = filters.q
    const { data } = await structureApi.periodos.list(params)
    items.value = data.data
    meta.total_pages = data.meta.total_pages
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'Error al cargar datos'
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.name = ''
  form.start_date = ''
  form.end_date = ''
  showModal.value = true
}

const openEdit = (item: Item) => {
  editingId.value = item.id
  form.name = item.name
  form.start_date = item.start_date || ''
  form.end_date = item.end_date || ''
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  try {
    const payload = {
      name: form.name,
      start_date: form.start_date || null,
      end_date: form.end_date || null
    }
    if (editingId.value) {
      await structureApi.periodos.update(editingId.value, payload)
    } else {
      await structureApi.periodos.create(payload)
    }
    showModal.value = false
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo guardar'
  }
}

const remove = async (item: Item) => {
  if (!confirm(`Eliminar ${item.name}?`)) return
  try {
    await structureApi.periodos.remove(item.id)
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo eliminar'
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
