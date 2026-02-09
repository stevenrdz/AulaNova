<template>
  <PageHeader
    title="Estudiantes"
    subtitle="Gestion de estudiantes y matriculas."
  >
    <template #actions>
      <BaseButton @click="openCreate">
        Crear estudiante
      </BaseButton>
    </template>
  </PageHeader>

  <div class="card shadow">
    <div class="card-body">
      <div class="relative overflow-x-auto">
        <table class="text-left w-full whitespace-nowrap">
          <thead class="bg-gray-200 text-gray-700">
            <tr class="border-gray-300 border-b">
              <th class="px-6 py-3">
                Nombre
              </th>
              <th class="px-6 py-3">
                Email
              </th>
              <th class="px-6 py-3">
                Estado
              </th>
              <th class="px-6 py-3">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="student in students"
              :key="student.id"
              class="border-gray-300 border-b"
            >
              <td class="py-3 px-6">
                {{ student.first_name }} {{ student.last_name }}
              </td>
              <td class="py-3 px-6">
                {{ student.email }}
              </td>
              <td class="py-3 px-6">
                <span
                  class="px-2 py-1 text-sm rounded-full"
                  :class="student.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                >
                  {{ student.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="py-3 px-6 flex gap-2">
                <BaseButton
                  variant="secondary"
                  @click="openEdit(student)"
                >
                  Editar
                </BaseButton>
                <BaseButton
                  variant="secondary"
                  @click="removeStudent(student)"
                >
                  Eliminar
                </BaseButton>
              </td>
            </tr>
            <tr v-if="!loading && students.length === 0">
              <td
                colspan="4"
                class="py-6 px-6 text-center text-gray-500"
              >
                Sin estudiantes.
              </td>
            </tr>
            <tr v-if="loading">
              <td
                colspan="4"
                class="py-6 px-6 text-center text-gray-500"
              >
                Cargando...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <BaseModal
    v-model="showModal"
    :title="modalTitle"
  >
    <form
      class="space-y-4"
      @submit.prevent="saveStudent"
    >
      <div>
        <label class="inline-block mb-2">Nombre</label>
        <input
          v-model="form.first_name"
          class="border border-gray-300 rounded p-2 w-full"
          required
        >
      </div>
      <div>
        <label class="inline-block mb-2">Apellido</label>
        <input
          v-model="form.last_name"
          class="border border-gray-300 rounded p-2 w-full"
          required
        >
      </div>
      <div>
        <label class="inline-block mb-2">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="border border-gray-300 rounded p-2 w-full"
          required
        >
      </div>
      <div>
        <label class="inline-block mb-2">Password</label>
        <input
          v-model="form.password"
          type="password"
          class="border border-gray-300 rounded p-2 w-full"
          :required="!editing"
        >
      </div>
    </form>
    <template #footer>
      <BaseButton
        variant="secondary"
        @click="showModal = false"
      >
        Cancelar
      </BaseButton>
      <BaseButton @click="saveStudent">
        Guardar
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import { usersApi } from '@/api/users'

interface Student {
  id: number
  email: string
  first_name: string
  last_name: string
  roles: string[]
  is_active: boolean
}

const students = ref<Student[]>([])
const loading = ref(false)
const showModal = ref(false)
const editing = ref<Student | null>(null)

const form = reactive({
  email: '',
  password: '',
  first_name: '',
  last_name: ''
})

const modalTitle = computed(() => (editing.value ? 'Editar estudiante' : 'Crear estudiante'))

const resetForm = () => {
  form.email = ''
  form.password = ''
  form.first_name = ''
  form.last_name = ''
}

const loadStudents = async () => {
  loading.value = true
  try {
    const response = await usersApi.list('ROLE_STUDENT')
    students.value = response.data.data
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editing.value = null
  resetForm()
  showModal.value = true
}

const openEdit = (student: Student) => {
  editing.value = student
  form.email = student.email
  form.first_name = student.first_name
  form.last_name = student.last_name
  form.password = ''
  showModal.value = true
}

const saveStudent = async () => {
  const payload: any = {
    email: form.email,
    first_name: form.first_name,
    last_name: form.last_name,
    role: 'ROLE_STUDENT'
  }
  if (form.password) payload.password = form.password

  if (editing.value) {
    await usersApi.update(editing.value.id, payload)
  } else {
    await usersApi.create({ ...payload, password: form.password })
  }
  showModal.value = false
  await loadStudents()
}

const removeStudent = async (student: Student) => {
  if (!confirm(`Eliminar a ${student.first_name} ${student.last_name}?`)) return
  await usersApi.remove(student.id)
  await loadStudents()
}

onMounted(loadStudents)
</script>
