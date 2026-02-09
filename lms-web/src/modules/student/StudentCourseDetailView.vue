<template>
  <PageHeader
    title="Curso"
    :subtitle="cursoVirtual ? cursoVirtual.curso?.name : 'Detalle del curso.'"
  />

  <div class="rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 text-white p-6 shadow">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <p class="text-sm opacity-80">
          Tu progreso
        </p>
        <h2 class="text-2xl font-semibold">
          {{ cursoVirtual ? cursoVirtual.curso?.name : 'Curso virtual' }}
        </h2>
        <p class="text-sm opacity-80">
          ID virtual: {{ cursoVirtualId }}
        </p>
      </div>
      <div class="bg-white/20 rounded-xl px-4 py-2">
        <div class="text-xs uppercase tracking-wide opacity-80">
          Tiempo de conexion
        </div>
        <div class="text-lg font-semibold">
          {{ formattedTime }}
        </div>
      </div>
    </div>
  </div>

  <BaseCard class="mt-6">
    <div
      v-if="loading"
      class="text-gray-500"
    >
      Cargando...
    </div>
    <div v-else>
      <div class="text-sm text-gray-600">
        Tiempo de conexion: <span class="font-semibold">{{ formattedTime }}</span>
      </div>
    </div>
  </BaseCard>

  <div class="grid gap-6 mt-6">
    <BaseCard>
      <h3 class="text-lg font-semibold text-slate-700">
        Anuncios
      </h3>
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
      <h3 class="text-lg font-semibold text-slate-700">
        Actividades
      </h3>
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
              @click="openSubmission(a)"
            >
              Entregar
            </BaseButton>
            <BaseButton
              variant="secondary"
              @click="openSubmission(a)"
            >
              Mis entregas
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
            Inicia un intento y responde las preguntas.
          </p>
        </div>
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
            <div class="flex flex-col items-end gap-2">
              <span
                class="text-xs px-2 py-1 rounded-full"
                :class="statusBadge(q)"
              >{{ statusLabel(q) }}</span>
              <BaseButton
                variant="secondary"
                :disabled="!isQuizAvailable(q)"
                @click="startOrResume(q)"
              >
                {{ getAttemptLabel(q) }}
              </BaseButton>
              <div
                v-if="getLatestAttempt(q)?.score !== null"
                class="text-xs text-gray-500"
              >
                Puntaje: {{ getLatestAttempt(q)?.score }}%
              </div>
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
  </div>

  <BaseModal
    v-model="showSubmissionModal"
    :title="selectedActivity ? selectedActivity.title : 'Entrega'"
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
      <div class="border rounded-xl p-4 bg-slate-50">
        <h4 class="font-semibold text-slate-800 mb-2">
          Nueva entrega
        </h4>
        <div class="grid gap-4">
          <div>
            <label class="text-sm text-gray-600">Contenido</label>
            <textarea
              v-model="submissionForm.content"
              class="form-input w-full"
              rows="3"
            />
            <p class="text-xs text-gray-500 mt-1">
              Escribe tu respuesta o adjunta un archivo.
            </p>
          </div>
          <div>
            <label
              class="text-sm text-gray-600"
              for="submission-file"
            >Adjunto</label>
            <input
              id="submission-file"
              ref="submissionFileInput"
              type="file"
              class="form-input w-full"
              :disabled="submissionUploading || submissionSaving"
              aria-describedby="submission-file-help"
              @change="onFileSelected"
            >
            <p
              id="submission-file-help"
              class="text-xs text-gray-500 mt-1"
            >
              Opcional. El archivo se sube automaticamente al enviar la entrega.
            </p>
            <div
              v-if="submissionFile.file"
              class="mt-2 flex flex-wrap items-center gap-2 text-xs"
            >
              <span class="rounded-full bg-slate-100 px-2 py-1 text-slate-700">
                {{ submissionFile.file.name }} · {{ formatBytes(submissionFile.file.size) }}
              </span>
              <BaseButton
                variant="secondary"
                :disabled="submissionUploading"
                @click="clearSubmissionFile"
              >
                Quitar adjunto
              </BaseButton>
            </div>
            <div
              v-if="submissionUploading"
              class="mt-2 text-xs text-sky-600"
              role="status"
              aria-live="polite"
            >
              Subiendo adjunto... no cierres esta ventana.
            </div>
            <div
              v-else-if="submissionFile.uploaded"
              class="mt-2 text-xs text-emerald-600"
              role="status"
            >
              Adjunto listo. ID: {{ submissionFile.uploaded.id }}
            </div>
            <div
              v-if="submissionUploadError"
              class="mt-2 text-xs text-rose-600"
              role="alert"
              aria-live="assertive"
            >
              {{ submissionUploadError }}
            </div>
          </div>
        </div>
      </div>

      <div class="border rounded-xl p-4 bg-white">
        <h4 class="font-semibold text-slate-800 mb-2">
          Mis entregas
        </h4>
        <ul class="space-y-2 text-sm">
          <li
            v-for="s in mySubmissions"
            :key="s.id"
            class="border-b pb-3"
          >
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="text-xs text-gray-500">
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
                {{ s.file.original_name }} · {{ formatBytes(s.file.size) }}
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
          </li>
          <li
            v-if="mySubmissions.length === 0"
            class="text-gray-500"
          >
            Aun no has realizado entregas.
          </li>
        </ul>
      </div>
    </div>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showSubmissionModal = false"
      >
        Cerrar
      </BaseButton>
      <BaseButton
        :disabled="submissionSaving || submissionUploading || !selectedActivity"
        :aria-busy="submissionSaving || submissionUploading"
        @click="submitSubmission"
      >
        Enviar entrega
      </BaseButton>
    </template>
  </BaseModal>

  <BaseModal
    v-model="showAttemptModal"
    :title="activeQuiz ? activeQuiz.title : 'Intento'"
  >
    <div
      v-if="attemptLoading"
      class="text-gray-500"
    >
      Cargando preguntas...
    </div>
    <div v-else>
      <div class="flex items-center justify-between mb-3 text-sm text-gray-600">
        <span>Respondidas: {{ answeredCount }} / {{ questions.length }}</span>
        <span v-if="activeAttempt?.started_at">Inicio: {{ activeAttempt.started_at }}</span>
      </div>
      <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2">
        <div
          v-for="question in questions"
          :key="question.id"
          class="border rounded-xl p-4 bg-slate-50"
        >
          <div class="text-xs uppercase text-indigo-600 font-semibold">
            {{ question.type }}
          </div>
          <div class="font-semibold text-slate-800 mt-1">
            {{ question.prompt }}
          </div>
          <div
            v-if="question.type === 'SINGLE'"
            class="mt-3 space-y-2"
          >
            <label
              v-for="opt in question.options || []"
              :key="opt"
              class="flex items-center gap-2 text-sm"
            >
              <input
                type="radio"
                :name="`q-${question.id}`"
                :value="opt"
                :checked="answers[question.id]?.value === opt"
                @change="saveAnswer(question, opt)"
              >
              <span>{{ opt }}</span>
            </label>
          </div>
          <div
            v-else
            class="mt-3"
          >
            <textarea
              class="form-input w-full"
              rows="3"
              :value="answers[question.id]?.value || ''"
              @blur="saveAnswer(question, ($event.target as HTMLTextAreaElement).value)"
            />
          </div>
        </div>
      </div>
    </div>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showAttemptModal = false"
      >
        Cerrar
      </BaseButton>
      <BaseButton
        :disabled="attemptSaving || !activeAttempt"
        @click="finishAttempt"
      >
        Finalizar intento
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { virtualApi } from '@/api/virtual'
import { assessmentsApi } from '@/api/assessments'
import { trackingApi } from '@/api/tracking'
import { filesApi } from '@/api/files'
import { formatDuration } from '@/utils/time'
import { useToastStore } from '@/store/toast'

const route = useRoute()
const cursoVirtualId = Number(route.params.id)
const toast = useToastStore()

const loading = ref(false)
const cursoVirtual = ref<any | null>(null)
const anuncios = ref<any[]>([])
const actividades = ref<any[]>([])
const quizzes = ref<any[]>([])
const totalSeconds = ref(0)

const anunciosLoading = ref(false)
const actividadesLoading = ref(false)
const quizzesLoading = ref(false)

const showAttemptModal = ref(false)
const attemptLoading = ref(false)
const attemptSaving = ref(false)
const showSubmissionModal = ref(false)
const submissionsLoading = ref(false)
const submissionSaving = ref(false)
const submissionUploading = ref(false)
const submissionUploadError = ref<string | null>(null)
const submissionDownloadLoading = ref<Record<number, boolean>>({})
const submissionPreviewLoading = ref<Record<number, boolean>>({})
const submissionPreviewUrl = ref<Record<number, string>>({})
const selectedActivity = ref<any | null>(null)
const mySubmissions = ref<any[]>([])
const submissionForm = ref({ content: '', file_id: '' })
const submissionFileInput = ref<HTMLInputElement | null>(null)
const submissionFile = ref<{ file: File | null; uploaded: any | null }>({ file: null, uploaded: null })
const activeQuiz = ref<any | null>(null)
const activeAttempt = ref<any | null>(null)
const questions = ref<any[]>([])
const answers = ref<Record<number, { id?: number; value: string }>>({})
const attemptsByQuiz = ref<Record<number, any[]>>({})

const formattedTime = computed(() => formatDuration(totalSeconds.value))
const answeredCount = computed(() => Object.values(answers.value).filter((a) => a?.value?.length).length)

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
    const { data } = await assessmentsApi.quizzes.list({ curso_virtual_id: cursoVirtualId, page: 1, limit: 50 })
    quizzes.value = data.data
  } finally {
    quizzesLoading.value = false
  }
}

const loadTracking = async () => {
  try {
    const response = await trackingApi.summary()
    const byCourse = response.data.data?.by_course || []
    const courseName = cursoVirtual.value?.curso?.name
    if (courseName) {
      const match = byCourse.find((item: any) => item.curso_name === courseName)
      totalSeconds.value = match?.seconds || 0
    }
  } catch {
    totalSeconds.value = 0
  }
}

const loadAttemptsForQuiz = async (quizId: number) => {
  const { data } = await assessmentsApi.attempts.list({ quiz_id: quizId, page: 1, limit: 50 })
  attemptsByQuiz.value[quizId] = data.data
}

const loadAttemptsAll = async () => {
  const tasks = quizzes.value.map((q: any) => loadAttemptsForQuiz(q.id))
  await Promise.all(tasks)
}

const getLatestAttempt = (quiz: any) => {
  const list = attemptsByQuiz.value[quiz.id] || []
  return list[0] || null
}

const parseDate = (value?: string | null) => {
  if (!value) return null
  return new Date(value.replace(' ', 'T'))
}

const isQuizAvailable = (quiz: any) => {
  const now = new Date()
  const startAt = parseDate(quiz.start_at)
  const endAt = parseDate(quiz.end_at)
  if (startAt && now < startAt) return false
  if (endAt && now > endAt) return false
  return true
}

const statusLabel = (quiz: any) => {
  const attempt = getLatestAttempt(quiz)
  if (attempt?.finished_at) return 'Finalizado'
  if (attempt && !attempt.finished_at) return 'En curso'
  if (!isQuizAvailable(quiz)) return quiz.start_at ? 'Proximamente' : 'Cerrado'
  return 'Disponible'
}

const statusBadge = (quiz: any) => {
  const label = statusLabel(quiz)
  if (label === 'Finalizado') return 'bg-emerald-200 text-emerald-900'
  if (label === 'En curso') return 'bg-sky-200 text-sky-900'
  if (label === 'Proximamente') return 'bg-amber-200 text-amber-900'
  if (label === 'Cerrado') return 'bg-rose-200 text-rose-900'
  return 'bg-teal-200 text-teal-900'
}

const getAttemptLabel = (quiz: any) => {
  const attempt = getLatestAttempt(quiz)
  if (attempt && !attempt.finished_at) return 'Continuar'
  return 'Iniciar intento'
}

const startOrResume = async (quiz: any) => {
  const attempt = getLatestAttempt(quiz)
  if (attempt && !attempt.finished_at) {
    await openAttempt(quiz, attempt)
    return
  }
  try {
    const response = await assessmentsApi.attempts.create({ quiz_id: quiz.id })
    await openAttempt(quiz, response.data.data)
    await loadAttemptsForQuiz(quiz.id)
  } catch (err: any) {
    toast.add(err?.response?.data?.message || 'No se pudo iniciar el intento', 'error')
  }
}

const openAttempt = async (quiz: any, attempt: any) => {
  activeQuiz.value = quiz
  activeAttempt.value = attempt
  showAttemptModal.value = true
  attemptLoading.value = true
  try {
    const [questionsResponse, answersResponse] = await Promise.all([
      assessmentsApi.questions.list({ quiz_id: quiz.id, page: 1, limit: 200 }),
      assessmentsApi.answers.list({ attempt_id: attempt.id, page: 1, limit: 200 })
    ])
    questions.value = questionsResponse.data.data
    answers.value = {}
    answersResponse.data.data.forEach((answer: any) => {
      answers.value[answer.question.id] = { id: answer.id, value: answer.answer_text || '' }
    })
  } finally {
    attemptLoading.value = false
  }
}

const saveAnswer = async (question: any, value: string) => {
  if (!activeAttempt.value) return
  const existing = answers.value[question.id]
  try {
    if (existing?.id) {
      await assessmentsApi.answers.update(existing.id, { answer_text: value })
      answers.value[question.id] = { id: existing.id, value }
    } else {
      const response = await assessmentsApi.answers.create({
        attempt_id: activeAttempt.value.id,
        question_id: question.id,
        answer_text: value
      })
      answers.value[question.id] = { id: response.data.data.id, value }
    }
  } catch (err: any) {
    toast.add(err?.response?.data?.message || 'No se pudo guardar la respuesta', 'error')
  }
}

const finishAttempt = async () => {
  if (!activeAttempt.value) return
  attemptSaving.value = true
  try {
    const response = await assessmentsApi.attempts.finish(activeAttempt.value.id)
    activeAttempt.value = response.data.data
    toast.add('Intento finalizado', 'success')
    showAttemptModal.value = false
    if (activeQuiz.value) {
      await loadAttemptsForQuiz(activeQuiz.value.id)
    }
  } catch (err: any) {
    toast.add(err?.response?.data?.message || 'No se pudo finalizar el intento', 'error')
  } finally {
    attemptSaving.value = false
  }
}

const formatDateTime = (value?: string | null) => {
  if (!value) return 'Sin fecha'
  return value
}

const loadMySubmissions = async (actividadId: number) => {
  submissionsLoading.value = true
  try {
    const { data } = await virtualApi.submissions.my(actividadId, { page: 1, limit: 50 })
    mySubmissions.value = data.data
  } finally {
    submissionsLoading.value = false
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

const formatBytes = (value: number) => {
  if (!value) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const index = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1)
  const size = value / Math.pow(1024, index)
  return `${size.toFixed(size < 10 && index > 0 ? 1 : 0)} ${units[index]}`
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

const onFileSelected = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files && target.files.length > 0 ? target.files[0] : null
  submissionFile.value = { file, uploaded: null }
  submissionUploadError.value = null
  submissionForm.value.file_id = ''
}

const clearSubmissionFile = () => {
  submissionFile.value = { file: null, uploaded: null }
  submissionUploadError.value = null
  submissionForm.value.file_id = ''
  if (submissionFileInput.value) {
    submissionFileInput.value.value = ''
  }
}

const uploadSubmissionFile = async () => {
  if (!submissionFile.value.file) return null
  if (submissionFile.value.uploaded) return submissionFile.value.uploaded
  submissionUploading.value = true
  submissionUploadError.value = null
  try {
    const file = submissionFile.value.file
    const mimeType = file.type || 'application/octet-stream'
    const presignResponse = await filesApi.presign({
      filename: file.name,
      mime_type: mimeType,
      size: file.size
    })
    const { url, key, bucket } = presignResponse.data.data
    const putResponse = await fetch(url, {
      method: 'PUT',
      headers: { 'Content-Type': mimeType },
      body: file
    })
    if (!putResponse.ok) {
      throw new Error('No se pudo subir el archivo al storage.')
    }
    const completeResponse = await filesApi.complete({
      key,
      bucket,
      original_name: file.name,
      mime_type: mimeType,
      size: file.size
    })
    submissionFile.value.uploaded = completeResponse.data.data
    submissionForm.value.file_id = String(completeResponse.data.data.id)
    return completeResponse.data.data
  } catch (err: any) {
    const message = err?.response?.data?.message || err?.message || 'No se pudo subir el archivo.'
    submissionUploadError.value = message
    toast.add(message, 'error')
    return null
  } finally {
    submissionUploading.value = false
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

const openSubmission = async (actividad: any) => {
  selectedActivity.value = actividad
  submissionForm.value = { content: '', file_id: '' }
  clearSubmissionFile()
  showSubmissionModal.value = true
  await loadMySubmissions(actividad.id)
}

const submitSubmission = async () => {
  if (!selectedActivity.value) return
  if (!submissionForm.value.content?.trim() && !submissionFile.value.file) {
    toast.add('Debe enviar contenido o archivo', 'error')
    return
  }
  submissionSaving.value = true
  try {
    const payload: Record<string, any> = {
      content: submissionForm.value.content || null
    }
    if (submissionFile.value.file) {
      const uploaded = await uploadSubmissionFile()
      if (!uploaded) {
        return
      }
      payload.file_id = Number(uploaded.id)
    } else if (submissionForm.value.file_id) {
      payload.file_id = Number(submissionForm.value.file_id)
    }
    await virtualApi.submissions.create(selectedActivity.value.id, payload)
    toast.add('Entrega enviada', 'success')
    submissionForm.value = { content: '', file_id: '' }
    clearSubmissionFile()
    await loadMySubmissions(selectedActivity.value.id)
  } catch (err: any) {
    toast.add(err?.response?.data?.message || 'No se pudo enviar la entrega', 'error')
  } finally {
    submissionSaving.value = false
  }
}

onMounted(async () => {
  await loadCurso()
  await Promise.all([loadAnuncios(), loadActividades(), loadQuizzes()])
  await loadTracking()
  await loadAttemptsAll()
})
</script>
