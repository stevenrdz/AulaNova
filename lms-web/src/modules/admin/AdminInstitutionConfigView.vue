<template>
  <PageHeader title="Configuracion institucional" subtitle="Logo y color principal." />

  <BaseCard>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div>
        <label class="inline-block mb-2">Logo institucional</label>
        <input type="file" accept="image/*" @change="onFileChange" />
        <p class="text-sm text-gray-500 mt-2">Se sube a MinIO via presign.</p>
        <div v-if="previewUrl" class="mt-4">
          <img :src="previewUrl" alt="Logo preview" class="h-16" />
        </div>
        <p v-if="uploading" class="text-sm text-gray-600 mt-2">Subiendo...</p>
      </div>
      <div>
        <label class="inline-block mb-2">Color principal</label>
        <input v-model="form.primary_color" type="color" class="h-10 w-24" />
        <p class="text-sm text-gray-500 mt-2">Se guarda como CSS var.</p>
      </div>
    </div>

    <div class="mt-6 flex gap-2">
      <BaseButton @click="saveSettings" :disabled="saving">Guardar</BaseButton>
      <BaseButton variant="secondary" @click="loadSettings">Recargar</BaseButton>
    </div>
    <p v-if="message" class="text-green-700 text-sm mt-3">{{ message }}</p>
    <p v-if="error" class="text-red-600 text-sm mt-3">{{ error }}</p>
  </BaseCard>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { institutionApi } from '@/api/institution'
import { filesApi } from '@/api/files'

const form = reactive({
  logo_url: '',
  primary_color: '#624bff'
})

const previewUrl = ref<string | null>(null)
const uploading = ref(false)
const saving = ref(false)
const message = ref('')
const error = ref('')

const applyColor = (color: string) => {
  document.documentElement.style.setProperty('--primary-color', color)
}

const loadSettings = async () => {
  message.value = ''
  error.value = ''
  try {
    const response = await institutionApi.get()
    form.logo_url = response.data.data.logo_url || ''
    form.primary_color = response.data.data.primary_color || '#624bff'
    applyColor(form.primary_color)
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudieron cargar ajustes.'
  }
}

const onFileChange = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  previewUrl.value = URL.createObjectURL(file)
  uploading.value = true
  error.value = ''

  try {
    const presign = await filesApi.presign({
      filename: file.name,
      mime_type: file.type,
      size: file.size
    })

    const { url, key, bucket } = presign.data.data

    const uploadResponse = await fetch(url, {
      method: 'PUT',
      body: file,
      headers: {
        'Content-Type': file.type
      }
    })
    if (!uploadResponse.ok) {
      throw new Error('Upload failed')
    }

    const complete = await filesApi.complete({
      key,
      bucket,
      original_name: file.name,
      mime_type: file.type,
      size: file.size
    })

    form.logo_url = complete.data.data.key
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudo subir el logo.'
  } finally {
    uploading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = ''
  error.value = ''
  try {
    await institutionApi.update({
      logo_url: form.logo_url || null,
      primary_color: form.primary_color || null
    })
    message.value = 'Configuracion guardada.'
    applyColor(form.primary_color)
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'No se pudo guardar.'
  } finally {
    saving.value = false
  }
}

watch(
  () => form.primary_color,
  (value) => applyColor(value)
)

onMounted(loadSettings)
</script>
