<template>
  <PageHeader
    title="Carreras"
    subtitle="Gestion de carreras."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex gap-2 items-center">
        <input
          v-model="filters.q"
          class="form-input"
          placeholder="Buscar..."
        >
        <select
          v-model="filters.is_active"
          class="form-select"
        >
          <option value="">
            Estado
          </option>
          <option value="true">
            Activo
          </option>
          <option value="false">
            Inactivo
          </option>
        </select>
        <BaseButton
          variant="secondary"
          @click="load"
        >
          Filtrar
        </BaseButton>
      </div>
      <BaseButton @click="openCreate">
        Nueva carrera
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
              Estado
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
              {{ item.name }}
            </td>
            <td class="py-2">
              <span :class="item.is_active ? 'text-green-600' : 'text-gray-500'">
                {{ item.is_active ? 'Activo' : 'Inactivo' }}
              </span>
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
      <div class="flex items-center gap-2">
        <input
          id="is_active"
          v-model="form.is_active"
          type="checkbox"
        >
        <label for="is_active">Activo</label>
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
  is_active: boolean
}

const items = ref<Item[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ q: '', is_active: '' })

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({ name: '', is_active: true })

const modalTitle = computed(() => (editingId.value ? 'Editar carrera' : 'Nueva carrera'))

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.q) params.q = filters.q
    if (filters.is_active !== '') params.is_active = filters.is_active
    const { data } = await structureApi.carreras.list(params)
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
  form.is_active = true
  showModal.value = true
}

const openEdit = (item: Item) => {
  editingId.value = item.id
  form.name = item.name
  form.is_active = item.is_active
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  try {
    const payload = { name: form.name, is_active: form.is_active }
    if (editingId.value) {
      await structureApi.carreras.update(editingId.value, payload)
    } else {
      await structureApi.carreras.create(payload)
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
    await structureApi.carreras.remove(item.id)
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
