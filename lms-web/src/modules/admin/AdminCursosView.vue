<template>
  <PageHeader
    title="Cursos"
    subtitle="Gestion de cursos."
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
          v-model="filters.periodo_id"
          class="form-select"
        >
          <option value="">
            Periodo
          </option>
          <option
            v-for="p in lookups.periodos"
            :key="p.id"
            :value="p.id"
          >
            {{ p.name }}
          </option>
        </select>
        <select
          v-model="filters.teacher_id"
          class="form-select"
        >
          <option value="">
            Docente
          </option>
          <option
            v-for="t in lookups.docentes"
            :key="t.id"
            :value="t.id"
          >
            {{ t.first_name }} {{ t.last_name }}
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
        Nuevo curso
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
              Periodo
            </th>
            <th class="py-2">
              Docente
            </th>
            <th class="py-2">
              Capacidad
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
              {{ item.name }}
            </td>
            <td class="py-2">
              {{ item.periodo?.name || '-' }}
            </td>
            <td class="py-2">
              {{ item.teacher ? item.teacher.first_name + ' ' + item.teacher.last_name : '-' }}
            </td>
            <td class="py-2">
              {{ item.capacity ?? '-' }}
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
          <label class="text-sm text-gray-600">Capacidad</label>
          <input
            v-model.number="form.capacity"
            type="number"
            class="form-input w-full"
            min="0"
          >
        </div>
        <div>
          <label class="text-sm text-gray-600">Periodo</label>
          <select
            v-model="form.periodo_id"
            class="form-select w-full"
          >
            <option value="">
              Selecciona
            </option>
            <option
              v-for="p in lookups.periodos"
              :key="p.id"
              :value="p.id"
            >
              {{ p.name }}
            </option>
          </select>
        </div>
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
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-gray-600">Docente</label>
          <select
            v-model="form.teacher_id"
            class="form-select w-full"
          >
            <option value="">
              Selecciona
            </option>
            <option
              v-for="t in lookups.docentes"
              :key="t.id"
              :value="t.id"
            >
              {{ t.first_name }} {{ t.last_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="text-sm text-gray-600">Sede Jornada</label>
          <select
            v-model="form.sede_jornada_id"
            class="form-select w-full"
          >
            <option value="">
              Selecciona
            </option>
            <option
              v-for="s in lookups.sedes"
              :key="s.id"
              :value="s.id"
            >
              {{ s.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-gray-600">Carrera</label>
          <select
            v-model="form.carrera_id"
            class="form-select w-full"
          >
            <option value="">
              Selecciona
            </option>
            <option
              v-for="c in lookups.carreras"
              :key="c.id"
              :value="c.id"
            >
              {{ c.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="text-sm text-gray-600">Asignatura</label>
          <select
            v-model="form.asignatura_id"
            class="form-select w-full"
          >
            <option value="">
              Selecciona
            </option>
            <option
              v-for="a in lookups.asignaturas"
              :key="a.id"
              :value="a.id"
            >
              {{ a.name }}
            </option>
          </select>
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
import { usersApi } from '@/api/users'

interface Item {
  id: number
  name: string
  capacity?: number | null
  start_date?: string | null
  end_date?: string | null
  periodo?: { id: number; name: string }
  teacher?: { id: number; first_name: string; last_name: string }
  sede_jornada?: { id: number; name: string }
  carrera?: { id: number; name: string }
  asignatura?: { id: number; name: string }
}

const items = ref<Item[]>([])
const loading = ref(false)
const errorMessage = ref('')
const meta = reactive({ page: 1, limit: 20, total_pages: 1 })
const filters = reactive({ q: '', periodo_id: '', teacher_id: '' })

const lookups = reactive({
  periodos: [] as any[],
  docentes: [] as any[],
  sedes: [] as any[],
  carreras: [] as any[],
  asignaturas: [] as any[]
})

const showModal = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({
  name: '',
  capacity: 0,
  start_date: '',
  end_date: '',
  periodo_id: '',
  teacher_id: '',
  sede_jornada_id: '',
  carrera_id: '',
  asignatura_id: ''
})

const modalTitle = computed(() => (editingId.value ? 'Editar curso' : 'Nuevo curso'))

const loadLookups = async () => {
  const [periodos, sedes, carreras, asignaturas, docentes] = await Promise.all([
    structureApi.periodos.list({ page: 1, limit: 200 }),
    structureApi.sedeJornadas.list({ page: 1, limit: 200 }),
    structureApi.carreras.list({ page: 1, limit: 200 }),
    structureApi.asignaturas.list({ page: 1, limit: 200 }),
    usersApi.list('ROLE_TEACHER')
  ])
  lookups.periodos = periodos.data.data
  lookups.sedes = sedes.data.data
  lookups.carreras = carreras.data.data
  lookups.asignaturas = asignaturas.data.data
  lookups.docentes = docentes.data.data
}

const load = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: Record<string, any> = { page: meta.page, limit: meta.limit }
    if (filters.q) params.q = filters.q
    if (filters.periodo_id) params.periodo_id = filters.periodo_id
    if (filters.teacher_id) params.teacher_id = filters.teacher_id
    const { data } = await structureApi.cursos.list(params)
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
  form.capacity = 0
  form.start_date = ''
  form.end_date = ''
  form.periodo_id = ''
  form.teacher_id = ''
  form.sede_jornada_id = ''
  form.carrera_id = ''
  form.asignatura_id = ''
  showModal.value = true
}

const openEdit = (item: Item) => {
  editingId.value = item.id
  form.name = item.name
  form.capacity = item.capacity || 0
  form.start_date = item.start_date || ''
  form.end_date = item.end_date || ''
  form.periodo_id = item.periodo?.id?.toString() || ''
  form.teacher_id = item.teacher?.id?.toString() || ''
  form.sede_jornada_id = item.sede_jornada?.id?.toString() || ''
  form.carrera_id = item.carrera?.id?.toString() || ''
  form.asignatura_id = item.asignatura?.id?.toString() || ''
  showModal.value = true
}

const save = async () => {
  errorMessage.value = ''
  try {
    const payload: Record<string, any> = {
      name: form.name,
      capacity: form.capacity || null,
      start_date: form.start_date || null,
      end_date: form.end_date || null,
      periodo_id: form.periodo_id ? Number(form.periodo_id) : null,
      teacher_id: form.teacher_id ? Number(form.teacher_id) : null,
      sede_jornada_id: form.sede_jornada_id ? Number(form.sede_jornada_id) : null,
      carrera_id: form.carrera_id ? Number(form.carrera_id) : null,
      asignatura_id: form.asignatura_id ? Number(form.asignatura_id) : null
    }
    if (editingId.value) {
      await structureApi.cursos.update(editingId.value, payload)
    } else {
      await structureApi.cursos.create(payload)
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
    await structureApi.cursos.remove(item.id)
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
