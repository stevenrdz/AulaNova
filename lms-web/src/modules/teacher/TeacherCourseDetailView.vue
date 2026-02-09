<template>
  <PageHeader
    title="Curso"
    :subtitle="cursoVirtual ? cursoVirtual.curso?.name : 'Detalle de curso docente.'"
  />

  <div class="rounded-2xl bg-gradient-to-r from-teal-500 via-sky-500 to-indigo-500 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Aula activa
        </p>
        <h2 class="text-2xl font-semibold">
          {{ cursoVirtual ? cursoVirtual.curso?.name : 'Curso virtual' }}
        </h2>
        <p class="text-sm opacity-80">
          ID virtual: {{ cursoVirtualId }}
        </p>
      </div>
      <div class="flex flex-wrap gap-3">
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Anuncios
          </div>
          <div class="text-lg font-semibold">
            {{ anuncios.length }}
          </div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Actividades
          </div>
          <div class="text-lg font-semibold">
            {{ actividades.length }}
          </div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2">
          <div class="text-xs uppercase tracking-wide opacity-80">
            Evaluaciones
          </div>
          <div class="text-lg font-semibold">
            {{ quizzes.length }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 mt-6">
    <BaseCard>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-700">
          Anuncios
        </h3>
        <BaseButton @click="openAnuncio">
          Nuevo anuncio
        </BaseButton>
      </div>
      <div
        v-if="anunciosLoading"
        class="text-gray-500 mt-3"
      >
        Cargando...
      </div>
      <ul
        v-else
        class="mt-3 space-y-3"
      >
        <li
          v-for="a in anuncios"
          :key="a.id"
          class="border rounded-xl p-4 bg-slate-50/70"
        >
          <div class="font-semibold text-slate-800">
            {{ a.title }}
          </div>
          <div class="text-sm text-gray-600 mt-1">
            {{ a.content }}
          </div>
          <div class="text-xs text-gray-400 mt-2">
            {{ a.created_at }}
          </div>
        </li>
        <li
          v-if="anuncios.length === 0"
          class="text-gray-500"
        >
          Sin anuncios.
        </li>
      </ul>
    </BaseCard>

    <BaseCard>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-700">
          Actividades
        </h3>
        <BaseButton @click="openActividad">
          Nueva actividad
        </BaseButton>
      </div>
      <div
        v-if="actividadesLoading"
        class="text-gray-500 mt-3"
      >
        Cargando...
      </div>
      <ul
        v-else
        class="mt-3 space-y-3"
      >
        <li
          v-for="a in actividades"
          :key="a.id"
          class="border rounded-xl p-4 bg-amber-50/60"
        >
          <div class="flex items-center justify-between">
            <div class="font-semibold text-slate-800">
              {{ a.title }}
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-amber-200 text-amber-900">{{ a.type }}</span>
          </div>
          <div class="text-sm text-gray-600 mt-1">
            {{ a.content || '-' }}
          </div>
          <div class="text-xs text-gray-400 mt-2">
            {{ a.created_at }}
          </div>
          <div class="mt-3 flex flex-wrap gap-2">
            <BaseButton
              variant="secondary"
              @click="openSubmissions(a)"
            >
              Entregas
            </BaseButton>
          </div>
        </li>
        <li
          v-if="actividades.length === 0"
          class="text-gray-500"
        >
          Sin actividades.
        </li>
      </ul>
    </BaseCard>

    <BaseCard>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-slate-700">
            Evaluaciones
          </h3>
          <p class="text-xs text-gray-500">
            Crea quizzes y administra sus preguntas.
          </p>
        </div>
        <BaseButton @click="openQuiz()">
          Nueva evaluacion
        </BaseButton>
      </div>
      <div
        v-if="quizzesLoading"
        class="text-gray-500 mt-3"
      >
        Cargando...
      </div>
      <div
        v-else
        class="mt-4 space-y-3"
      >
        <div
          v-for="q in quizzes"
          :key="q.id"
          class="border rounded-xl p-4 bg-emerald-50/60"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="font-semibold text-slate-800">
                {{ q.title }}
              </div>
              <div class="text-sm text-gray-600">
                {{ q.description || '-' }}
              </div>
              <div class="text-xs text-gray-400 mt-2">
                {{ formatDateTime(q.start_at) }} - {{ formatDateTime(q.end_at) }}
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <BaseButton
                variant="secondary"
                @click="selectQuiz(q.id)"
              >
                Preguntas
              </BaseButton>
              <BaseButton
                variant="secondary"
                @click="openQuiz(q)"
              >
                Editar
              </BaseButton>
              <BaseButton
                variant="secondary"
                @click="deleteQuiz(q)"
              >
                Eliminar
              </BaseButton>
            </div>
          </div>
        </div>
        <div
          v-if="quizzes.length === 0"
          class="text-gray-500"
        >
          Sin quizzes.
        </div>
      </div>
    </BaseCard>

    <BaseCard>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-slate-700">
            Preguntas
          </h3>
          <p class="text-xs text-gray-500">
            Selecciona un quiz para ver y crear preguntas.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <select
            v-model="selectedQuizId"
            class="form-select"
          >
            <option :value="null">
              Selecciona un quiz
            </option>
            <option
              v-for="q in quizzes"
              :key="q.id"
              :value="q.id"
            >
              {{ q.title }}
            </option>
          </select>
          <BaseButton
            :disabled="!selectedQuizId"
            @click="openQuestion()"
          >
            Nueva pregunta
          </BaseButton>
        </div>
      </div>

      <div
        v-if="questionsLoading"
        class="text-gray-500 mt-3"
      >
        Cargando...
      </div>
      <ul
        v-else
        class="mt-4 space-y-3"
      >
        <li
          v-for="question in questions"
          :key="question.id"
          class="border rounded-xl p-4 bg-indigo-50/60"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="text-xs uppercase text-indigo-600 font-semibold">
                {{ question.type }}
              </div>
              <div class="font-semibold text-slate-800">
                {{ question.prompt }}
              </div>
              <div
                v-if="question.options?.length"
                class="text-sm text-gray-600 mt-2"
              >
                Opciones: {{ question.options.join(', ') }}
              </div>
              <div
                v-if="question.correct_option"
                class="text-xs text-gray-500 mt-1"
              >
                Correcta: {{ question.correct_option }}
              </div>
            </div>
            <div class="flex gap-2">
              <BaseButton
                variant="secondary"
                @click="openQuestion(question)"
              >
                Editar
              </BaseButton>
              <BaseButton
                variant="secondary"
                @click="deleteQuestion(question)"
              >
                Eliminar
              </BaseButton>
            </div>
          </div>
        </li>
        <li
          v-if="questions.length === 0"
          class="text-gray-500"
        >
          Sin preguntas.
        </li>
      </ul>
    </BaseCard>
  </div>

  <BaseModal
    v-model="showAnuncio"
    title="Nuevo anuncio"
  >
    <form
      class="grid gap-3"
      @submit.prevent="saveAnuncio"
    >
      <div>
        <label class="text-sm text-gray-600">Titulo</label>
        <input
          v-model="anuncioForm.title"
          class="form-input w-full"
          required
        >
      </div>
      <div>
        <label class="text-sm text-gray-600">Contenido</label>
        <textarea
          v-model="anuncioForm.content"
          class="form-input w-full"
          rows="3"
          required
        />
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showAnuncio = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="saveAnuncio">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    v-model="showActividad"
    title="Nueva actividad"
  >
    <form
      class="grid gap-3"
      @submit.prevent="saveActividad"
    >
      <div>
        <label class="text-sm text-gray-600">Tipo</label>
        <select
          v-model="actividadForm.type"
          class="form-select w-full"
          required
        >
          <option value="TEXT">
            TEXT
          </option>
          <option value="FILE">
            FILE
          </option>
          <option value="VIDEO">
            VIDEO
          </option>
          <option value="TASK">
            TASK
          </option>
        </select>
      </div>
      <div>
        <label class="text-sm text-gray-600">Titulo</label>
        <input
          v-model="actividadForm.title"
          class="form-input w-full"
          required
        >
      </div>
      <div>
        <label class="text-sm text-gray-600">Contenido</label>
        <textarea
          v-model="actividadForm.content"
          class="form-input w-full"
          rows="3"
        />
      </div>
      <div>
        <label class="text-sm text-gray-600">Fecha limite</label>
        <input
          v-model="actividadForm.due_at"
          type="datetime-local"
          class="form-input w-full"
        >
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showActividad = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="saveActividad">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    v-model="showQuizModal"
    :title="quizForm.id ? 'Editar evaluacion' : 'Nueva evaluacion'"
  >
    <form
      class="grid gap-3"
      @submit.prevent="saveQuiz"
    >
      <div>
        <label class="text-sm text-gray-600">Titulo</label>
        <input
          v-model="quizForm.title"
          class="form-input w-full"
          required
        >
      </div>
      <div>
        <label class="text-sm text-gray-600">Descripcion</label>
        <textarea
          v-model="quizForm.description"
          class="form-input w-full"
          rows="3"
        />
      </div>
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        <div>
          <label class="text-sm text-gray-600">Inicio</label>
          <input
            v-model="quizForm.start_at"
            type="datetime-local"
            class="form-input w-full"
          >
        </div>
        <div>
          <label class="text-sm text-gray-600">Fin</label>
          <input
            v-model="quizForm.end_at"
            type="datetime-local"
            class="form-input w-full"
          >
        </div>
      </div>
      <div>
        <label class="text-sm text-gray-600">Tiempo maximo (min)</label>
        <input
          v-model.number="quizForm.time_limit_minutes"
          type="number"
          min="1"
          class="form-input w-full"
        >
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showQuizModal = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="saveQuiz">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    v-model="showQuestionModal"
    :title="questionForm.id ? 'Editar pregunta' : 'Nueva pregunta'"
  >
    <form
      class="grid gap-3"
      @submit.prevent="saveQuestion"
    >
      <div>
        <label class="text-sm text-gray-600">Tipo</label>
        <select
          v-model="questionForm.type"
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
      </div>
      <div>
        <label class="text-sm text-gray-600">Enunciado</label>
        <textarea
          v-model="questionForm.prompt"
          class="form-input w-full"
          rows="3"
          required
        />
      </div>
      <div v-if="questionForm.type === 'SINGLE'">
        <label class="text-sm text-gray-600">Opciones (una por linea)</label>
        <textarea
          v-model="questionForm.optionsText"
          class="form-input w-full"
          rows="4"
        />
      </div>
      <div v-if="questionForm.type === 'SINGLE'">
        <label class="text-sm text-gray-600">Opcion correcta</label>
        <input
          v-model="questionForm.correct_option"
          class="form-input w-full"
        >
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showQuestionModal = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="saveQuestion">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    v-model="showSubmissionsModal"
    :title="selectedActivity ? `Entregas - ${selectedActivity.title}` : 'Entregas'"
  >
    <div
      v-if="submissionsLoading"
      class="text-gray-500"
    >
      Cargando entregas...
    </div>
    <div
      v-else
      class="space-y-4"
    >
      <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border bg-white p-3">
          <div class="text-xs text-gray-500">
            Total entregas
          </div>
          <div class="text-lg font-semibold text-slate-800">
            {{ submissions.length }}
          </div>
        </div>
        <div class="rounded-xl border bg-white p-3">
          <div class="text-xs text-gray-500">
            Calificadas
          </div>
          <div class="text-lg font-semibold text-emerald-700">
            {{ gradedCount }}
          </div>
        </div>
        <div class="rounded-xl border bg-white p-3">
          <div class="text-xs text-gray-500">
            Promedio
          </div>
          <div class="text-lg font-semibold text-slate-800">
            {{ averageGrade }}
          </div>
        </div>
      </div>

      <div class="grid gap-3 rounded-xl border bg-white p-3 lg:grid-cols-3">
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold text-gray-600">Buscar</label>
          <input
            v-model="submissionSearch"
            class="form-input"
            placeholder="Nombre o email"
          >
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold text-gray-600">Estado</label>
          <select
            v-model="submissionStatusFilter"
            class="form-select"
          >
            <option value="">
              Todos
            </option>
            <option value="SUBMITTED">
              Enviada
            </option>
            <option value="GRADED">
              Calificada
            </option>
            <option value="LATE">
              Tardia
            </option>
            <option value="RESUBMITTED">
              Reenviada
            </option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <BaseButton
            variant="secondary"
            @click="toggleSelectAll"
          >
            {{ allFilteredSelected ? 'Deseleccionar todo' : 'Seleccionar todo' }}
          </BaseButton>
          <span class="text-xs text-gray-500">Seleccionados: {{ selectedCount }}</span>
        </div>
      </div>

      <div class="grid gap-3 rounded-xl border bg-white p-3 lg:grid-cols-4">
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold text-gray-600">Estado masivo</label>
          <select
            v-model="bulkForm.status"
            class="form-select"
          >
            <option value="">
              Sin cambio
            </option>
            <option value="SUBMITTED">
              Enviada
            </option>
            <option value="GRADED">
              Calificada
            </option>
            <option value="LATE">
              Tardia
            </option>
            <option value="RESUBMITTED">
              Reenviada
            </option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="text-xs font-semibold text-gray-600">Nota masiva</label>
          <input
            v-model="bulkForm.grade"
            type="number"
            min="0"
            max="100"
            class="form-input"
            placeholder="0-100"
          >
        </div>
        <div class="flex flex-col gap-2 lg:col-span-2">
          <label class="text-xs font-semibold text-gray-600">Feedback masivo</label>
          <input
            v-model="bulkForm.feedback"
            class="form-input"
            placeholder="Comentario general"
          >
        </div>
        <div class="flex items-end gap-2 lg:col-span-4">
          <BaseButton
            variant="secondary"
            :disabled="!selectedCount"
            @click="applyBulkUpdate"
          >
            Aplicar a seleccionados
          </BaseButton>
          <BaseButton
            variant="secondary"
            @click="resetBulkForm"
          >
            Limpiar
          </BaseButton>
          <span class="text-xs text-gray-500">{{ bulkMessage }}</span>
        </div>
      </div>

      <div
        v-for="s in filteredSubmissions"
        :key="s.id"
        class="border rounded-xl p-4 bg-slate-50"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="flex items-start gap-2">
              <input
                type="checkbox"
                class="mt-1"
                :checked="selectedIds.has(s.id)"
                aria-label="Seleccionar entrega"
                @change="toggleSelection(s.id)"
              >
              <div>
                <div class="font-semibold text-slate-800">
                  {{ s.user.first_name }} {{ s.user.last_name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ s.user.email }}
                </div>
              </div>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
              <div class="text-xs text-gray-400">
                Enviado: {{ s.submitted_at }}
              </div>
              <span
                class="text-xs px-2 py-1 rounded-full"
                :class="submissionStatusBadge(s.status)"
              >
                {{ submissionStatusLabel(s.status) }}
              </span>
            </div>
            <div
              v-if="s.content"
              class="text-sm text-gray-600 mt-2"
            >
              {{ s.content }}
            </div>
            <div
              v-if="s.grade !== null || s.feedback"
              class="mt-2 text-xs text-gray-600"
            >
              <span
                v-if="s.grade !== null"
                class="font-semibold"
              >Nota: {{ s.grade }}</span>
              <span
                v-if="s.feedback"
                class="ml-2"
              >Feedback: {{ s.feedback }}</span>
            </div>
            <div
              v-if="s.file"
              class="mt-2 flex flex-wrap items-center gap-2 text-xs"
            >
              <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-700">
                {{ s.file.original_name }} - {{ formatBytes(s.file.size) }}
              </span>
              <BaseButton
                variant="secondary"
                :disabled="submissionDownloadLoading[s.file.id]"
                @click="downloadSubmissionFile(s.file)"
              >
                {{ submissionDownloadLoading[s.file.id] ? 'Descargando...' : 'Descargar' }}
              </BaseButton>
              <BaseButton
                v-if="isPreviewable(s.file)"
                variant="secondary"
                :disabled="submissionPreviewLoading[s.file.id]"
                @click="togglePreview(s.file)"
              >
                {{ submissionPreviewUrl[s.file.id] ? 'Ocultar vista' : 'Ver vista previa' }}
              </BaseButton>
              <BaseButton
                v-if="isPreviewable(s.file) && submissionPreviewUrl[s.file.id]"
                variant="secondary"
                :disabled="submissionPreviewLoading[s.file.id]"
                @click="refreshPreview(s.file)"
              >
                Actualizar
              </BaseButton>
            </div>
            <div
              v-if="s.file && submissionPreviewLoading[s.file.id]"
              class="mt-3 text-xs text-sky-600"
              role="status"
            >
              Cargando vista previa...
            </div>
            <div
              v-if="s.file && submissionPreviewUrl[s.file.id]"
              class="mt-3 border rounded-lg bg-white p-3"
            >
              <img
                v-if="(s.file.mime_type || '').startsWith('image/')"
                :src="submissionPreviewUrl[s.file.id]"
                :alt="s.file.original_name"
                class="max-h-64 w-full object-contain"
              >
              <iframe
                v-else
                :src="submissionPreviewUrl[s.file.id]"
                class="h-64 w-full rounded"
                title="Vista previa"
              />
            </div>
          </div>
          <div class="flex flex-col gap-2 min-w-[200px]">
            <input
              v-model.number="s.grade"
              type="number"
              min="0"
              max="100"
              class="form-input"
              placeholder="Nota (0-100)"
              aria-label="Nota"
            >
            <input
              v-model="s.feedback"
              class="form-input"
              placeholder="Feedback"
              aria-label="Feedback"
            >
            <div
              v-if="submissionError(s)"
              class="text-xs text-rose-600"
            >
              {{ submissionError(s) }}
            </div>
            <BaseButton
              variant="secondary"
              :disabled="!!submissionError(s)"
              @click="gradeSubmission(s)"
            >
              Guardar cambios
            </BaseButton>
            <div class="text-xs text-gray-500">
              Usa Guardar cambios para confirmar la calificacion.
            </div>
          </div>
        </div>
      </div>
      <div
        v-if="filteredSubmissions.length === 0"
        class="text-gray-500"
      >
        No hay entregas con los filtros actuales.
      </div>
    </div>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showSubmissionsModal = false"
      >
        Cerrar
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { virtualApi } from '@/api/virtual'
import { assessmentsApi } from '@/api/assessments'
import { filesApi } from '@/api/files'
import { useToastStore } from '@/store/toast'

const route = useRoute()
const cursoVirtualId = Number(route.params.id)
const toast = useToastStore()

const loading = ref(false)
const cursoVirtual = ref<any | null>(null)

const anuncios = ref<any[]>([])
const actividades = ref<any[]>([])
const quizzes = ref<any[]>([])
const questions = ref<any[]>([])

const anunciosLoading = ref(false)
const actividadesLoading = ref(false)
const quizzesLoading = ref(false)
const questionsLoading = ref(false)

const selectedQuizId = ref<number | null>(null)

const showAnuncio = ref(false)
const showActividad = ref(false)
const showQuizModal = ref(false)
const showQuestionModal = ref(false)
const showSubmissionsModal = ref(false)
const submissionsLoading = ref(false)
const submissions = ref<any[]>([])
const submissionDownloadLoading = ref<Record<number, boolean>>({})
const submissionPreviewLoading = ref<Record<number, boolean>>({})
const submissionPreviewUrl = ref<Record<number, string>>({})
const submissionSearch = ref('')
const submissionStatusFilter = ref('')
const selectedIds = ref<Set<number>>(new Set())
const bulkForm = ref({ status: '', grade: '', feedback: '' })
const bulkMessage = ref('')
const selectedActivity = ref<any | null>(null)

const anuncioForm = ref({ title: '', content: '' })
const actividadForm = ref({ type: 'TEXT', title: '', content: '', due_at: '' })
const quizForm = ref({
  id: null as number | null,
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  time_limit_minutes: null as number | null
})
const questionForm = ref({
  id: null as number | null,
  type: 'SINGLE',
  prompt: '',
  optionsText: '',
  correct_option: ''
})

const toInputDate = (value?: string | null) => {
  if (!value) return ''
  return value.replace(' ', 'T').slice(0, 16)
}

const fromInputDate = (value?: string | null) => {
  if (!value) return null
  return value.replace('T', ' ')
}

const formatDateTime = (value?: string | null) => {
  if (!value) return 'Sin fecha'
  return value
}

const formatBytes = (value: number) => {
  if (!value) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const index = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1)
  const size = value / Math.pow(1024, index)
  return `${size.toFixed(size < 10 && index > 0 ? 1 : 0)} ${units[index]}`
}

const loadCurso = async () => {
  loading.value = true
  try {
    const { data } = await virtualApi.cursos.list({ page: 1, limit: 200 })
    cursoVirtual.value = data.data.find((c: any) => c.id === cursoVirtualId) || null
  } finally {
    loading.value = false
  }
}

const loadAnuncios = async () => {
  anunciosLoading.value = true
  try {
    const { data } = await virtualApi.anuncios.list({ curso_virtual_id: cursoVirtualId, page: 1, limit: 50 })
    anuncios.value = data.data
  } finally {
    anunciosLoading.value = false
  }
}

const loadActividades = async () => {
  actividadesLoading.value = true
  try {
    const { data } = await virtualApi.actividades.list({ curso_virtual_id: cursoVirtualId, page: 1, limit: 50 })
    actividades.value = data.data
  } finally {
    actividadesLoading.value = false
  }
}

const loadQuizzes = async () => {
  quizzesLoading.value = true
  try {
    const { data } = await assessmentsApi.quizzes.list({ curso_virtual_id: cursoVirtualId, page: 1, limit: 100 })
    quizzes.value = data.data
  } finally {
    quizzesLoading.value = false
  }
}

const loadQuestions = async (quizId: number) => {
  questionsLoading.value = true
  try {
    const { data } = await assessmentsApi.questions.list({ quiz_id: quizId, page: 1, limit: 200 })
    questions.value = data.data
  } finally {
    questionsLoading.value = false
  }
}

const openAnuncio = () => {
  anuncioForm.value = { title: '', content: '' }
  showAnuncio.value = true
}

const saveAnuncio = async () => {
  await virtualApi.anuncios.create({
    curso_virtual_id: cursoVirtualId,
    title: anuncioForm.value.title,
    content: anuncioForm.value.content
  })
  showAnuncio.value = false
  toast.add('Anuncio creado', 'success')
  await loadAnuncios()
}

const openActividad = () => {
  actividadForm.value = { type: 'TEXT', title: '', content: '', due_at: '' }
  showActividad.value = true
}

const saveActividad = async () => {
  await virtualApi.actividades.create({
    curso_virtual_id: cursoVirtualId,
    type: actividadForm.value.type,
    title: actividadForm.value.title,
    content: actividadForm.value.content || null,
    due_at: actividadForm.value.due_at ? actividadForm.value.due_at.replace('T', ' ') : null
  })
  showActividad.value = false
  toast.add('Actividad creada', 'success')
  await loadActividades()
}

const openQuiz = (quiz?: any) => {
  quizForm.value = {
    id: quiz?.id ?? null,
    title: quiz?.title ?? '',
    description: quiz?.description ?? '',
    start_at: toInputDate(quiz?.start_at),
    end_at: toInputDate(quiz?.end_at),
    time_limit_minutes: quiz?.time_limit_minutes ?? null
  }
  showQuizModal.value = true
}

const saveQuiz = async () => {
  const payload = {
    curso_virtual_id: cursoVirtualId,
    title: quizForm.value.title,
    description: quizForm.value.description || null,
    start_at: fromInputDate(quizForm.value.start_at),
    end_at: fromInputDate(quizForm.value.end_at),
    time_limit_minutes: quizForm.value.time_limit_minutes || null
  }
  if (quizForm.value.id) {
    await assessmentsApi.quizzes.update(quizForm.value.id, payload)
    toast.add('Evaluacion actualizada', 'success')
  } else {
    await assessmentsApi.quizzes.create(payload)
    toast.add('Evaluacion creada', 'success')
  }
  showQuizModal.value = false
  await loadQuizzes()
}

const deleteQuiz = async (quiz: any) => {
  if (!confirm('Deseas eliminar esta evaluacion?')) return
  await assessmentsApi.quizzes.remove(quiz.id)
  toast.add('Evaluacion eliminada', 'success')
  if (selectedQuizId.value === quiz.id) {
    selectedQuizId.value = null
    questions.value = []
  }
  await loadQuizzes()
}

const selectQuiz = (quizId: number) => {
  selectedQuizId.value = quizId
}

const openQuestion = (question?: any) => {
  questionForm.value = {
    id: question?.id ?? null,
    type: question?.type ?? 'SINGLE',
    prompt: question?.prompt ?? '',
    optionsText: question?.options?.join('\n') ?? '',
    correct_option: question?.correct_option ?? ''
  }
  showQuestionModal.value = true
}

const saveQuestion = async () => {
  if (!selectedQuizId.value) return
  const options =
    questionForm.value.type === 'SINGLE'
      ? questionForm.value.optionsText
          .split('\n')
          .map((o) => o.trim())
          .filter((o) => o.length > 0)
      : null
  const payload = {
    quiz_id: selectedQuizId.value,
    type: questionForm.value.type,
    prompt: questionForm.value.prompt,
    options,
    correct_option: questionForm.value.type === 'SINGLE' ? questionForm.value.correct_option : null
  }
  if (questionForm.value.id) {
    await assessmentsApi.questions.update(questionForm.value.id, payload)
    toast.add('Pregunta actualizada', 'success')
  } else {
    await assessmentsApi.questions.create(payload)
    toast.add('Pregunta creada', 'success')
  }
  showQuestionModal.value = false
  await loadQuestions(selectedQuizId.value)
}

const deleteQuestion = async (question: any) => {
  if (!confirm('Deseas eliminar esta pregunta?')) return
  await assessmentsApi.questions.remove(question.id)
  toast.add('Pregunta eliminada', 'success')
  if (selectedQuizId.value) {
    await loadQuestions(selectedQuizId.value)
  }
}

const submissionStatusLabel = (status?: string | null) => {
  const value = (status || '').toUpperCase()
  if (value === 'GRADED') return 'Calificada'
  if (value === 'LATE') return 'Tardia'
  if (value === 'RESUBMITTED') return 'Reenviada'
  return 'Enviada'
}

const submissionStatusBadge = (status?: string | null) => {
  const value = (status || '').toUpperCase()
  if (value === 'GRADED') return 'bg-emerald-200 text-emerald-900'
  if (value === 'LATE') return 'bg-amber-200 text-amber-900'
  if (value === 'RESUBMITTED') return 'bg-sky-200 text-sky-900'
  return 'bg-slate-200 text-slate-900'
}

const isPreviewable = (file: any) => {
  const mime = (file?.mime_type || '').toLowerCase()
  return mime.startsWith('image/') || mime === 'application/pdf'
}

const loadPreviewUrl = async (file: any) => {
  if (!file?.id) return ''
  if (submissionPreviewUrl.value[file.id]) return submissionPreviewUrl.value[file.id]
  submissionPreviewLoading.value[file.id] = true
  try {
    const response = await filesApi.download(file.id, {
      disposition: 'inline',
      filename: file.original_name
    })
    const url = response.data.data?.url
    if (!url) {
      throw new Error('No se pudo obtener la URL del archivo.')
    }
    submissionPreviewUrl.value[file.id] = url
    return url
  } catch (err: any) {
    toast.add(err?.response?.data?.message || err?.message || 'No se pudo obtener el preview', 'error')
    return ''
  } finally {
    submissionPreviewLoading.value[file.id] = false
  }
}

const togglePreview = async (file: any) => {
  if (!file?.id) return
  if (submissionPreviewUrl.value[file.id]) {
    delete submissionPreviewUrl.value[file.id]
    return
  }
  await loadPreviewUrl(file)
}

const refreshPreview = async (file: any) => {
  if (!file?.id) return
  delete submissionPreviewUrl.value[file.id]
  await loadPreviewUrl(file)
}

const filteredSubmissions = computed(() => {
  const search = submissionSearch.value.trim().toLowerCase()
  const status = submissionStatusFilter.value.trim().toUpperCase()
  return submissions.value.filter((s: any) => {
    const matchStatus = status ? (s.status || '').toUpperCase() === status : true
    if (!search) return matchStatus
    const fullName = `${s.user.first_name} ${s.user.last_name}`.toLowerCase()
    const email = (s.user.email || '').toLowerCase()
    return matchStatus && (fullName.includes(search) || email.includes(search))
  })
})

const gradedCount = computed(() =>
  submissions.value.filter((s: any) => (s.status || '').toUpperCase() === 'GRADED').length
)

const averageGrade = computed(() => {
  const values = submissions.value.map((s: any) => s.grade).filter((g: any) => g !== null && g !== undefined)
  if (values.length === 0) return 'N/A'
  const total = values.reduce((sum: number, g: number) => sum + Number(g), 0)
  return (total / values.length).toFixed(1)
})

const selectedCount = computed(() => selectedIds.value.size)

const allFilteredSelected = computed(() => {
  if (filteredSubmissions.value.length === 0) return false
  return filteredSubmissions.value.every((s: any) => selectedIds.value.has(s.id))
})

const toggleSelection = (id: number) => {
  const next = new Set(selectedIds.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  selectedIds.value = next
}

const toggleSelectAll = () => {
  if (allFilteredSelected.value) {
    const next = new Set(selectedIds.value)
    filteredSubmissions.value.forEach((s: any) => next.delete(s.id))
    selectedIds.value = next
    return
  }
  const next = new Set(selectedIds.value)
  filteredSubmissions.value.forEach((s: any) => next.add(s.id))
  selectedIds.value = next
}

const resetBulkForm = () => {
  bulkForm.value = { status: '', grade: '', feedback: '' }
  bulkMessage.value = ''
}

const loadSubmissions = async (actividadId: number) => {
  submissionsLoading.value = true
  try {
    const { data } = await virtualApi.submissions.list(actividadId, { page: 1, limit: 50 })
    submissions.value = data.data
    selectedIds.value = new Set()
  } finally {
    submissionsLoading.value = false
  }
}

const openSubmissions = async (actividad: any) => {
  selectedActivity.value = actividad
  showSubmissionsModal.value = true
  submissionSearch.value = ''
  submissionStatusFilter.value = ''
  resetBulkForm()
  await loadSubmissions(actividad.id)
}

const gradeSubmission = async (submission: any) => {
  try {
    if (submissionError(submission)) {
      toast.add(submissionError(submission), 'error')
      return
    }
    await virtualApi.submissions.grade(submission.id, {
      grade: submission.grade ?? null,
      feedback: submission.feedback ?? null,
      status: submission.grade !== null ? 'GRADED' : 'SUBMITTED'
    })
    toast.add('Entrega actualizada', 'success')
  } catch (err: any) {
    toast.add(err?.response?.data?.message || 'No se pudo actualizar la entrega', 'error')
  }
}

const submissionError = (submission: any) => {
  if (submission.grade === null || submission.grade === undefined || submission.grade === '') return ''
  const value = Number(submission.grade)
  if (Number.isNaN(value)) return 'La nota debe ser numerica.'
  if (value < 0 || value > 100) return 'La nota debe estar entre 0 y 100.'
  if (!submission.feedback || !submission.feedback.trim()) return 'El feedback es obligatorio al calificar.'
  return ''
}

const applyBulkUpdate = async () => {
  bulkMessage.value = ''
  if (!selectedIds.value.size) {
    toast.add('Selecciona al menos una entrega', 'error')
    return
  }
  const hasStatus = bulkForm.value.status.trim() !== ''
  const hasGrade = bulkForm.value.grade !== '' && bulkForm.value.grade !== null
  const hasFeedback = bulkForm.value.feedback.trim() !== ''
  if (!hasStatus && !hasGrade && !hasFeedback) {
    toast.add('Define un cambio masivo antes de aplicar', 'error')
    return
  }
  if (hasGrade && !hasFeedback) {
    toast.add('El feedback es obligatorio al asignar nota masiva', 'error')
    return
  }

  const gradeValue = hasGrade ? Number(bulkForm.value.grade) : null
  if (hasGrade && (Number.isNaN(gradeValue) || gradeValue < 0 || gradeValue > 100)) {
    toast.add('La nota masiva debe estar entre 0 y 100', 'error')
    return
  }

  const selected = submissions.value.filter((s: any) => selectedIds.value.has(s.id))
  let successCount = 0
  let errorCount = 0
  await Promise.all(
    selected.map(async (s: any) => {
      try {
        await virtualApi.submissions.grade(s.id, {
          grade: hasGrade ? gradeValue : s.grade ?? null,
          feedback: hasFeedback ? bulkForm.value.feedback : s.feedback ?? null,
          status: hasStatus
            ? bulkForm.value.status
            : hasGrade
              ? 'GRADED'
              : s.status ?? 'SUBMITTED'
        })
        successCount += 1
      } catch {
        errorCount += 1
      }
    })
  )
  bulkMessage.value = `Actualizadas: ${successCount}${errorCount ? ` - Fallidas: ${errorCount}` : ''}`
  if (selectedActivity.value) {
    await loadSubmissions(selectedActivity.value.id)
  }
}

const downloadSubmissionFile = async (file: any) => {
  if (!file?.id) return
  submissionDownloadLoading.value[file.id] = true
  try {
    const response = await filesApi.download(file.id, {
      disposition: 'attachment',
      filename: file.original_name
    })
    const url = response.data.data?.url
    if (!url) {
      throw new Error('No se pudo obtener la URL del archivo.')
    }
    window.open(url, '_blank', 'noopener')
  } catch (err: any) {
    toast.add(err?.response?.data?.message || err?.message || 'No se pudo descargar el archivo', 'error')
  } finally {
    submissionDownloadLoading.value[file.id] = false
  }
}

watch(selectedQuizId, (value) => {
  if (value) {
    loadQuestions(value)
  } else {
    questions.value = []
  }
})

onMounted(async () => {
  await loadCurso()
  await Promise.all([loadAnuncios(), loadActividades(), loadQuizzes()])
})
</script>
