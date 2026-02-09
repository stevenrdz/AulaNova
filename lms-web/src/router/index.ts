import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

import LoginView from '@/auth/LoginView.vue'
import ForgotPasswordView from '@/auth/ForgotPasswordView.vue'
import ResetPasswordView from '@/auth/ResetPasswordView.vue'

import AdminLayout from '@/modules/admin/AdminLayout.vue'
import AdminDashboardView from '@/modules/admin/AdminDashboardView.vue'
import AdminStudentsView from '@/modules/admin/AdminStudentsView.vue'
import AdminTeachersView from '@/modules/admin/AdminTeachersView.vue'
import AdminStaffView from '@/modules/admin/AdminStaffView.vue'
import AdminImportView from '@/modules/admin/AdminImportView.vue'
import AdminSedeJornadaView from '@/modules/admin/AdminSedeJornadaView.vue'
import AdminNivelesView from '@/modules/admin/AdminNivelesView.vue'
import AdminPeriodosView from '@/modules/admin/AdminPeriodosView.vue'
import AdminAsignaturasView from '@/modules/admin/AdminAsignaturasView.vue'
import AdminCarrerasView from '@/modules/admin/AdminCarrerasView.vue'
import AdminCursosView from '@/modules/admin/AdminCursosView.vue'
import AdminCursosVirtualesView from '@/modules/admin/AdminCursosVirtualesView.vue'
import AdminCursoDetailView from '@/modules/admin/AdminCursoDetailView.vue'
import AdminInstitutionConfigView from '@/modules/admin/AdminInstitutionConfigView.vue'
import AdminQuizzesView from '@/modules/admin/AdminQuizzesView.vue'
import AdminQuestionsView from '@/modules/admin/AdminQuestionsView.vue'
import AdminAttemptsView from '@/modules/admin/AdminAttemptsView.vue'
import AdminAnswersView from '@/modules/admin/AdminAnswersView.vue'

import TeacherLayout from '@/modules/teacher/TeacherLayout.vue'
import TeacherDashboardView from '@/modules/teacher/TeacherDashboardView.vue'
import TeacherCoursesView from '@/modules/teacher/TeacherCoursesView.vue'
import TeacherCourseDetailView from '@/modules/teacher/TeacherCourseDetailView.vue'

import StudentLayout from '@/modules/student/StudentLayout.vue'
import StudentDashboardView from '@/modules/student/StudentDashboardView.vue'
import StudentCoursesView from '@/modules/student/StudentCoursesView.vue'
import StudentCourseDetailView from '@/modules/student/StudentCourseDetailView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', component: LoginView },
    { path: '/forgot-password', component: ForgotPasswordView },
    { path: '/reset-password', component: ResetPasswordView },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] },
      children: [
        { path: '', redirect: 'dashboard' },
        { path: 'dashboard', component: AdminDashboardView },
        { path: 'institucional/estudiantes', component: AdminStudentsView },
        { path: 'institucional/docentes', component: AdminTeachersView },
        { path: 'institucional/administrativos', component: AdminStaffView },
        { path: 'institucional/importacion', component: AdminImportView },
        { path: 'institucional/configuracion', component: AdminInstitutionConfigView },
        { path: 'estructuracion/sede-jornada', component: AdminSedeJornadaView },
        { path: 'estructuracion/niveles', component: AdminNivelesView },
        { path: 'estructuracion/periodos', component: AdminPeriodosView },
        { path: 'estructuracion/asignaturas', component: AdminAsignaturasView },
        { path: 'estructuracion/carreras', component: AdminCarrerasView },
        { path: 'estructuracion/cursos', component: AdminCursosView },
        { path: 'academico/cursos-virtuales', component: AdminCursosVirtualesView },
        { path: 'academico/cursos/:id', component: AdminCursoDetailView },
        { path: 'academico/quizzes', component: AdminQuizzesView },
        { path: 'academico/preguntas', component: AdminQuestionsView },
        { path: 'academico/intentos', component: AdminAttemptsView },
        { path: 'academico/respuestas', component: AdminAnswersView }
      ]
    },
    {
      path: '/teacher',
      component: TeacherLayout,
      meta: { requiresAuth: true, roles: ['ROLE_TEACHER'] },
      children: [
        { path: '', redirect: 'dashboard' },
        { path: 'dashboard', component: TeacherDashboardView },
        { path: 'cursos', component: TeacherCoursesView },
        { path: 'cursos/:id', component: TeacherCourseDetailView }
      ]
    },
    {
      path: '/student',
      component: StudentLayout,
      meta: { requiresAuth: true, roles: ['ROLE_STUDENT'] },
      children: [
        { path: '', redirect: 'dashboard' },
        { path: 'dashboard', component: StudentDashboardView },
        { path: 'cursos', component: StudentCoursesView },
        { path: 'cursos/:id', component: StudentCourseDetailView }
      ]
    }
  ]
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.accessToken && auth.refreshToken) {
    try {
      await auth.refresh()
    } catch {
      auth.clearSession()
    }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return '/login'
  }

  if (!to.meta.requiresAuth && auth.isAuthenticated && to.path === '/login') {
    if (auth.hasRole('ROLE_ADMIN')) return '/admin/dashboard'
    if (auth.hasRole('ROLE_TEACHER')) return '/teacher/dashboard'
    if (auth.hasRole('ROLE_STUDENT')) return '/student/dashboard'
  }

  const roles = (to.meta.roles as string[] | undefined) ?? []
  if (roles.length && !roles.some((role) => auth.user?.roles?.includes(role as any))) {
    return '/login'
  }

  return true
})

export default router
