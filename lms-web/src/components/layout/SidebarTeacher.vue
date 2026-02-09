<template>
  <nav class="navbar-vertical navbar">
    <div id="myScrollableElement" class="h-screen overflow-y-auto">
      <RouterLink class="navbar-brand" to="/teacher/dashboard">
        <img src="/logo.svg" alt="LMS" />
      </RouterLink>

      <ul class="navbar-nav flex-col" id="sideNavbar">
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/teacher/dashboard">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Dashboard
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink class="nav-link" active-class="active" to="/teacher/cursos">
            <span class="nav-icon w-4 h-4 mr-2">•</span>
            Cursos
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts"></script>
