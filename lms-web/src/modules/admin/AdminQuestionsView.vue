<template>
  <PageHeader
    title="Preguntas"
    subtitle="Gestion de preguntas."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <select
          v-model="filters.quiz_id"
          class="form-select w-full sm:w-56"
        >
          <option value="">
            Quiz
          </option>
          <option
            v-for="q in lookups.quizzes"
            :key="q.id"
            :value="q.id"
          >
            #{{ q.id }} - {{ q.title }}
          </option>
        </select>
        <select
          v-model="filters.type"
          class="form-select w-full sm:w-40"
        >
          <option value="">
            Tipo
          </option>
          <option value="SINGLE">
            SINGLE
          </option>
          <option value="TEXT">
            TEXT
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
        Nueva pregunta
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
              Quiz
            </th>
            <th class="py-2">
              Tipo
            </th>
            <th class="py-2">
              Enunciado
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
              {{ item.quiz?.id || '-' }}
            </td>
            <td class="py-2">
              {{ item.type }}
            </td>
            <td class="py-2">
              {{ item.prompt }}
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
        <label class="text-sm text-gray-600">Quiz</label>
        <select
          v-model="form.quiz_id"
          class="form-select w-full"
          required
        >
          <option value="">
            Selecciona
          </option>
          <option
            v-for="q in lookups.quizzes"
            :key="q.id"
            :value="q.id"
          >
            #{{ q.id }} - {{ q.title }}
          </option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Asocia la pregunta a un quiz.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Tipo</label>
        <select
          v-model="form.type"
          class="form-select w-full"
          required
        >
          <option value="SINGLE">
            SINGLE
          </option>
          <option value="TEXT">
            TEXT
          </option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
          SINGLE = opcion unica, TEXT = respuesta abierta.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Enunciado</label>
        <textarea
          v-model="form.prompt"
          class="form-input w-full"
          rows="3"
          required
        />
        <p class="text-xs text-gray-500 mt-1">
          Obligatorio. Evita ambiguedades.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Opciones (separadas por coma)</label>
        <input
          v-model="form.options"
          class="form-input w-full"
        >
        <p class="text-xs text-gray-500 mt-1">
          Solo para SINGLE. Ej: A, B, C.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Opcion correcta</label>
        <input
          v-model="form.correct_option"
          class="form-input w-full"
        >
        <p class="text-xs text-gray-500 mt-1">
          Debe coincidir con una opcion.
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

const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ quiz_id: '', type: '' })
const lookups = reactive({ quizzes: [] as any[] })

const showModal = ref(false)
const editingId = ref<number | null>(null)
const modalError = ref('')
const form = reactive({ quiz_id: '', type: 'SINGLE', prompt: '', options: '', correct_option: '' })

const modalTitle = computed(() => (editingId.value ? 'Editar pregunta' : 'Nueva pregunta'))

const parseOptions = () =>
  form.options
    ? form.options
        .split(/[\n,]+/)
        .map((v) => v.trim())
        .filter(Boolean)
    : []

const validateForm = () => {
  if (!form.quiz_id) return 'Selecciona un quiz.'
  if (!form.prompt.trim()) return 'El enunciado es obligatorio.'
  if (form.type === 'SINGLE') {
    const options = parseOptions()
    if (options.length < 2) return 'Agrega al menos 2 opciones.'
    if (!form.correct_option.trim()) return 'Define la opcion correcta.'
    if (!options.includes(form.correct_option.trim())) return 'La opcion correcta debe existir en las opciones.'
  }
  return ''
}

const loadLookups = async () => {
  const { data } = await assessmentsApi.quizzes.list({ page: 1, limit: 200 })
  lookups.quizzes = data.data
}

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.quiz_id) params.quiz_id = filters.quiz_id
    if (filters.type) params.type = filters.type
    const { data } = await assessmentsApi.questions.list(params)
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
  form.quiz_id = ''
  form.type = 'SINGLE'
  form.prompt = ''
  form.options = ''
  form.correct_option = ''
  modalError.value = ''
  showModal.value = true
}

const openEdit = (item: any) => {
  editingId.value = item.id
  form.quiz_id = item.quiz?.id?.toString() || ''
  form.type = item.type
  form.prompt = item.prompt
  form.options = Array.isArray(item.options) ? item.options.join(', ') : ''
  form.correct_option = item.correct_option || ''
  modalError.value = ''
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  modalError.value = validateForm()
  if (modalError.value) return
  try {
    const options = form.type === 'SINGLE' ? parseOptions() : null
    const payload: Record<string, any> = {
      quiz_id: Number(form.quiz_id),
      type: form.type,
      prompt: form.prompt,
      options,
      correct_option: form.type === 'SINGLE' ? form.correct_option.trim() || null : null
    }
    if (editingId.value) {
      await assessmentsApi.questions.update(editingId.value, payload)
    } else {
      await assessmentsApi.questions.create(payload)
    }
    showModal.value = false
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo guardar'
  }
}

const remove = async (item: any) => {
  if (!confirm('Eliminar pregunta?')) return
  try {
    await assessmentsApi.questions.remove(item.id)
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
