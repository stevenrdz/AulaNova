<template>
  <PageHeader title="Dashboard" subtitle="Resumen general del LMS." />

  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
    <BaseCard>
      <div class="flex justify-between items-center">
        <h4>Estudiantes</h4>
        <div class="bg-indigo-600 bg-opacity-10 rounded-md w-10 h-10 flex items-center justify-center text-indigo-600">
          <span>??</span>
        </div>
      </div>
      <div class="mt-4">
        <h2 class="text-xl font-bold">0</h2>
        <span class="text-gray-500">Registrados</span>
      </div>
    </BaseCard>
    <BaseCard>
      <div class="flex justify-between items-center">
        <h4>Docentes</h4>
        <div class="bg-indigo-600 bg-opacity-10 rounded-md w-10 h-10 flex items-center justify-center text-indigo-600">
          <span>?????</span>
        </div>
      </div>
      <div class="mt-4">
        <h2 class="text-xl font-bold">0</h2>
        <span class="text-gray-500">Activos</span>
      </div>
    </BaseCard>
    <BaseCard>
      <div class="flex justify-between items-center">
        <h4>Cursos</h4>
        <div class="bg-indigo-600 bg-opacity-10 rounded-md w-10 h-10 flex items-center justify-center text-indigo-600">
          <span>??</span>
        </div>
      </div>
      <div class="mt-4">
        <h2 class="text-xl font-bold">0</h2>
        <span class="text-gray-500">Creados</span>
      </div>
    </BaseCard>
    <BaseCard>
      <div class="flex justify-between items-center">
        <h4>Actividades</h4>
        <div class="bg-indigo-600 bg-opacity-10 rounded-md w-10 h-10 flex items-center justify-center text-indigo-600">
          <span>??</span>
        </div>
      </div>
      <div class="mt-4">
        <h2 class="text-xl font-bold">0</h2>
        <span class="text-gray-500">Pendientes</span>
      </div>
    </BaseCard>
  </div>
</template>

<script setup lang="ts">
import PageHeader from '@/components/ui/PageHeader.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
</script>
