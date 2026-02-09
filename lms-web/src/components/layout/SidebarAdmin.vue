<template>
  <nav class="navbar-vertical navbar">
    <div id="myScrollableElement" class="h-screen overflow-y-auto">
      <RouterLink class="navbar-brand" to="/admin/dashboard">
        <img src="/logo.svg" alt="LMS" />
      </RouterLink>

      <ul class="navbar-nav flex-col" id="sideNavbar">
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/dashboard">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Dashboard
          </RouterLink>
        </li>

        <li class="nav-item">
          <div class="navbar-heading">Institucional</div>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/institucional/estudiantes">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Estudiantes
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/institucional/docentes">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Docentes
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/institucional/administrativos">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Administrativos
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/institucional/importacion">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Importacion
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/institucional/configuracion">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Configuracion
          </RouterLink>
        </li>

        <li class="nav-item">
          <div class="navbar-heading">Estructuracion</div>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/sede-jornada">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Sede Jornada
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/niveles">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Niveles
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/periodos">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Periodos
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/asignaturas">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Asignaturas
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/carreras">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Carreras
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/estructuracion/cursos">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Cursos
          </RouterLink>
        </li>

        <li class="nav-item">
          <div class="navbar-heading">Academico</div>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/admin/academico/cursos-virtuales">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Cursos Virtuales
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts"></script>
