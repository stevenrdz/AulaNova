<template>
  <PageHeader
    title="Intentos"
    subtitle="Gestion de intentos."
  />
  <BaseCard>
    <div class="flex flex-wrap gap-3 items-center justify-between">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <select
          v-model="filters.quiz_id"
          class="form-select w-full sm:w-64"
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
        <BaseButton
          variant="secondary"
          @click="load"
        >
          Filtrar
        </BaseButton>
      </div>
      <BaseButton @click="openCreate">
        Nuevo intento
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
              Usuario
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
              {{ item.quiz?.title || item.quiz?.id || '-' }}
            </td>
            <td class="py-2">
              {{ item.user ? item.user.first_name + ' ' + item.user.last_name : '-' }}
            </td>
            <td class="py-2">
              {{ item.finished_at ? 'Finalizado' : 'En curso' }}
            </td>
            <td class="py-2 flex gap-2">
              <BaseButton
                variant="secondary"
                @click="finish(item)"
              >
                Finalizar
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
    title="Nuevo intento"
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
          Crea un intento manual para pruebas o recuperaciones.
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
        Crear
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
import { assessmentsApi } from '@/api/assessments'

const items = ref<any[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ quiz_id: '' })
const lookups = reactive({ quizzes: [] as any[] })

const showModal = ref(false)
const modalError = ref('')
const form = reactive({ quiz_id: '' })

const validateForm = () => {
  if (!form.quiz_id) return 'Selecciona un quiz.'
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
    const { data } = await assessmentsApi.attempts.list(params)
    items.value = data.data
    meta.total_pages = data.meta.total_pages
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'Error al cargar datos'
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  form.quiz_id = ''
  modalError.value = ''
  showModal.value = true
}

const save = async () => {
  modalError.value = validateForm()
  if (modalError.value) return
  try {
    await assessmentsApi.attempts.create({ quiz_id: Number(form.quiz_id) })
    showModal.value = false
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo crear'
  }
}

const finish = async (item: any) => {
  try {
    await assessmentsApi.attempts.finish(item.id)
    await load()
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message || 'No se pudo finalizar'
  }
}

const remove = async (item: any) => {
  if (!confirm('Eliminar intento?')) return
  try {
    await assessmentsApi.attempts.remove(item.id)
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
