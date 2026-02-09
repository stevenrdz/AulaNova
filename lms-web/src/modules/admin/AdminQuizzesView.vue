<template>
  <PageHeader
    title="Evaluaciones"
    subtitle="Gestion de quizzes."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <input
          v-model="filters.q"
          class="form-input w-full sm:w-56"
          placeholder="Buscar por titulo..."
        >
        <select
          v-model="filters.curso_virtual_id"
          class="form-select w-full sm:w-56"
        >
          <option value="">
            Curso virtual
          </option>
          <option
            v-for="c in lookups.cursosVirtuales"
            :key="c.id"
            :value="c.id"
          >
            #{{ c.id }} - {{ c.curso?.name || 'Curso' }}
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
        Nuevo quiz
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
              Titulo
            </th>
            <th class="py-2">
              Curso virtual
            </th>
            <th class="py-2">
              Fechas
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
              {{ item.title }}
            </td>
            <td class="py-2">
              {{ item.curso_virtual?.id || '-' }}
            </td>
            <td class="py-2">
              <div>{{ item.start_at || '-' }} / {{ item.end_at || '-' }}</div>
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
        <label class="text-sm text-gray-600">Curso virtual</label>
        <select
          v-model="form.curso_virtual_id"
          class="form-select w-full"
          required
        >
          <option value="">
            Selecciona
          </option>
          <option
            v-for="c in lookups.cursosVirtuales"
            :key="c.id"
            :value="c.id"
          >
            #{{ c.id }} - {{ c.curso?.name || 'Curso' }}
          </option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Define el curso virtual donde vivira el quiz.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Titulo</label>
        <input
          v-model="form.title"
          class="form-input w-full"
          required
        >
        <p class="text-xs text-gray-500 mt-1">
          Usa un titulo corto y claro para estudiantes.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Descripcion</label>
        <textarea
          v-model="form.description"
          class="form-input w-full"
          rows="3"
        />
        <p class="text-xs text-gray-500 mt-1">
          Opcional. Contexto o instrucciones.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-gray-600">Inicio</label>
          <input
            v-model="form.start_at"
            type="datetime-local"
            class="form-input w-full"
          >
          <p class="text-xs text-gray-500 mt-1">
            Opcional. Fecha desde la que se habilita.
          </p>
        </div>
        <div>
          <label class="text-sm text-gray-600">Fin</label>
          <input
            v-model="form.end_at"
            type="datetime-local"
            class="form-input w-full"
          >
          <p class="text-xs text-gray-500 mt-1">
            Opcional. Fecha de cierre.
          </p>
        </div>
      </div>
      <div>
        <label class="text-sm text-gray-600">Tiempo limite (min)</label>
        <input
          v-model.number="form.time_limit_minutes"
          type="number"
          class="form-input w-full"
          min="0"
        >
        <p class="text-xs text-gray-500 mt-1">
          0 = sin limite.
        </p>
      </div>
      <div
        v-if="modalError"
        class="text-xs text-rose-600"
      >
        {{ modalError }}
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showModal = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton
        :disabled="!!validateForm()"
        @click="save"
      >
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
import { assessmentsApi } from '@/api/assessments'
import { virtualApi } from '@/api/virtual'

const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ q: '', curso_virtual_id: '' })
const lookups = reactive({ cursosVirtuales: [] as any[] })

const showModal = ref(false)
const editingId = ref<number | null>(null)
const modalError = ref('')
const form = reactive({
  curso_virtual_id: '',
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  time_limit_minutes: 0
})

const modalTitle = computed(() => (editingId.value ? 'Editar quiz' : 'Nuevo quiz'))

const validateForm = () => {
  if (!form.curso_virtual_id) return 'Selecciona un curso virtual.'
  if (!form.title.trim()) return 'El titulo es obligatorio.'
  if (form.start_at && form.end_at && form.start_at > form.end_at) {
    return 'La fecha fin debe ser posterior a la fecha inicio.'
  }
  if (form.time_limit_minutes !== null && form.time_limit_minutes < 0) {
    return 'El tiempo limite no puede ser negativo.'
  }
  return ''
}

const loadLookups = async () => {
  const { data } = await virtualApi.cursos.list({ page: 1, limit: 200 })
  lookups.cursosVirtuales = data.data
}

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.q) params.q = filters.q
    if (filters.curso_virtual_id) params.curso_virtual_id = filters.curso_virtual_id
    const { data } = await assessmentsApi.quizzes.list(params)
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
  form.curso_virtual_id = ''
  form.title = ''
  form.description = ''
  form.start_at = ''
  form.end_at = ''
  form.time_limit_minutes = 0
  modalError.value = ''
  showModal.value = true
}

const openEdit = (item: any) => {
  editingId.value = item.id
  form.curso_virtual_id = item.curso_virtual?.id?.toString() || ''
  form.title = item.title
  form.description = item.description || ''
  form.start_at = item.start_at ? item.start_at.replace(' ', 'T') : ''
  form.end_at = item.end_at ? item.end_at.replace(' ', 'T') : ''
  form.time_limit_minutes = item.time_limit_minutes || 0
  modalError.value = ''
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  modalError.value = validateForm()
  if (modalError.value) return
  try {
    const payload = {
      curso_virtual_id: Number(form.curso_virtual_id),
      title: form.title,
      description: form.description || null,
      start_at: form.start_at ? form.start_at.replace('T', ' ') : null,
      end_at: form.end_at ? form.end_at.replace('T', ' ') : null,
      time_limit_minutes: form.time_limit_minutes || null
    }
    if (editingId.value) {
      await assessmentsApi.quizzes.update(editingId.value, payload)
    } else {
      await assessmentsApi.quizzes.create(payload)
    }
    showModal.value = false
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo guardar'
  }
}

const remove = async (item: any) => {
  if (!confirm(`Eliminar quiz ${item.title}?`)) return
  try {
    await assessmentsApi.quizzes.remove(item.id)
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

onMounted(async () => {
  await loadLookups()
  await load()
})
</script>
