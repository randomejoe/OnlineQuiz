import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import LoginPage from '../components/pages/LoginPage.vue'
import RegisterPage from '../components/pages/RegisterPage.vue'
import QuizListPage from '../components/pages/QuizListPage.vue'
import QuizDetailPage from '../components/pages/QuizDetailPage.vue'
import QuizTakePage from '../components/pages/QuizTakePage.vue'
import ResultPage from '../components/pages/ResultPage.vue'
import HistoryPage from '../components/pages/HistoryPage.vue'
import AdminDashboardPage from '../components/pages/AdminDashboardPage.vue'
import AdminQuizListPage from '../components/pages/AdminQuizListPage.vue'
import AdminQuizCreatePage from '../components/pages/AdminQuizCreatePage.vue'
import AdminQuizEditPage from '../components/pages/AdminQuizEditPage.vue'
import AdminQuizResultsPage from '../components/pages/AdminQuizResultsPage.vue'
import AdminUserAttemptsPage from '../components/pages/AdminUserAttemptsPage.vue'
import AdminUsersPage from '../components/pages/AdminUsersPage.vue'
import NotFoundPage from '../components/pages/NotFoundPage.vue'

const routes = [
  { path: '/', name: 'root', component: LoginPage },
  { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: RegisterPage, meta: { guestOnly: true } },
  { path: '/quizzes', name: 'quizzes', component: QuizListPage, meta: { requiresAuth: true } },
  { path: '/quizzes/:id', name: 'quiz-detail', component: QuizDetailPage, meta: { requiresAuth: true } },
  {
    path: '/quizzes/:id/take',
    name: 'quiz-take',
    component: QuizTakePage,
    meta: { requiresAuth: true },
  },
  {
    path: '/results/:attemptId',
    name: 'result',
    component: ResultPage,
    meta: { requiresAuth: true },
  },
  { path: '/history', name: 'history', component: HistoryPage, meta: { requiresAuth: true } },
  {
    path: '/admin',
    name: 'admin-dashboard',
    component: AdminDashboardPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/quizzes',
    name: 'admin-quizzes',
    component: AdminQuizListPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/quizzes/create',
    name: 'admin-quiz-create',
    component: AdminQuizCreatePage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/quizzes/:id/edit',
    name: 'admin-quiz-edit',
    component: AdminQuizEditPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/quizzes/:id/results',
    name: 'admin-quiz-results',
    component: AdminQuizResultsPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: AdminUsersPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/users/:id/attempts',
    name: 'admin-user-attempts',
    component: AdminUserAttemptsPage,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()
  await authStore.initialize()

  if (to.name === 'root') {
    if (!authStore.isAuthenticated) {
      return '/login'
    }

    return authStore.isAdmin ? '/admin' : '/quizzes'
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return authStore.isAdmin ? '/admin' : '/quizzes'
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return '/login'
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return '/quizzes'
  }

  return true
})

export default router
