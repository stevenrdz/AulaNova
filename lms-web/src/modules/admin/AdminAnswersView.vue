<template>
  <PageHeader
    title="Respuestas"
    subtitle="Gestion de respuestas."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <select
          v-model="filters.attempt_id"
          class="form-select w-full sm:w-56"
        >
          <option value="">
            Intento
          </option>
          <option
            v-for="a in lookups.attempts"
            :key="a.id"
            :value="a.id"
          >
            #{{ a.id }} - {{ a.quiz?.title || 'Quiz' }}
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
        Nueva respuesta
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
              Intento
            </th>
            <th class="py-2">
              Pregunta
            </th>
            <th class="py-2">
              Correcta
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
              {{ item.attempt?.id || '-' }}
            </td>
            <td class="py-2">
              {{ item.question?.prompt || item.question?.id || '-' }}
            </td>
            <td class="py-2">
              {{ item.is_correct ? 'Si' : 'No' }}
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
        <label class="text-sm text-gray-600">Intento</label>
        <select
          v-model="form.attempt_id"
          class="form-select w-full"
          required
        >
          <option value="">
            Selecciona
          </option>
          <option
            v-for="a in lookups.attempts"
            :key="a.id"
            :value="a.id"
          >
            #{{ a.id }} - {{ a.quiz?.title || 'Quiz' }}
          </option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Selecciona el intento que recibira la respuesta.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Pregunta</label>
        <select
          v-model="form.question_id"
          class="form-select w-full"
          required
        >
          <option value="">
            Selecciona
          </option>
          <option
            v-for="q in lookups.questions"
            :key="q.id"
            :value="q.id"
          >
            #{{ q.id }} - {{ q.prompt }}
          </option>
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Asegura que la pregunta pertenece al quiz del intento.
        </p>
      </div>
      <div>
        <label class="text-sm text-gray-600">Respuesta</label>
        <textarea
          v-model="form.answer_text"
          class="form-input w-full"
          rows="2"
        />
        <p class="text-xs text-gray-500 mt-1">
          Opcional para SINGLE si solo marcas correcta.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <input
          id="is_correct"
          v-model="form.is_correct"
          type="checkbox"
        >
        <label for="is_correct">Correcta</label>
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
const filters = reactive({ attempt_id: '' })
const lookups = reactive({ attempts: [] as any[], questions: [] as any[] })

const showModal = ref(false)
const editingId = ref<number | null>(null)
const modalError = ref('')
const form = reactive({ attempt_id: '', question_id: '', answer_text: '', is_correct: false })

const modalTitle = computed(() => (editingId.value ? 'Editar respuesta' : 'Nueva respuesta'))

const validateForm = () => {
  if (!form.attempt_id) return 'Selecciona un intento.'
  if (!form.question_id) return 'Selecciona una pregunta.'
  return ''
}

const loadLookups = async () => {
  const [attempts, questions] = await Promise.all([
    assessmentsApi.attempts.list({ page: 1, limit: 200 }),
    assessmentsApi.questions.list({ page: 1, limit: 200 })
  ])
  lookups.attempts = attempts.data.data
  lookups.questions = questions.data.data
}

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.attempt_id) params.attempt_id = filters.attempt_id
    const { data } = await assessmentsApi.answers.list(params)
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
  form.attempt_id = ''
  form.question_id = ''
  form.answer_text = ''
  form.is_correct = false
  modalError.value = ''
  showModal.value = true
}

const openEdit = (item: any) => {
  editingId.value = item.id
  form.attempt_id = item.attempt?.id?.toString() || ''
  form.question_id = item.question?.id?.toString() || ''
  form.answer_text = item.answer_text || ''
  form.is_correct = !!item.is_correct
  modalError.value = ''
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  modalError.value = validateForm()
  if (modalError.value) return
  try {
    const payload = {
      attempt_id: Number(form.attempt_id),
      question_id: Number(form.question_id),
      answer_text: form.answer_text || null,
      is_correct: form.is_correct
    }
    if (editingId.value) {
      await assessmentsApi.answers.update(editingId.value, payload)
    } else {
      await assessmentsApi.answers.create(payload)
    }
    showModal.value = false
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo guardar'
  }
}

const remove = async (item: any) => {
  if (!confirm('Eliminar respuesta?')) return
  try {
    await assessmentsApi.answers.remove(item.id)
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
