<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const lastAdminPath = ref(sessionStorage.getItem('admin:last-path') || '/admin')

const navItems = [
  { label: 'Dashboard', to: '/admin' },
  { label: 'Quizzes', to: '/admin/quizzes' },
  { label: 'Users', to: '/admin/users' },
]

const displayName = computed(() => authStore.user?.name || 'there')
const canGoBack = computed(() => route.path !== '/admin')

watch(
  () => route.path,
  (path) => {
    if (path.startsWith('/admin') && path !== '/admin') {
      sessionStorage.setItem('admin:last-path', path)
      lastAdminPath.value = path
    }
  },
  { immediate: true },
)

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function closeSidebar() {
  isSidebarOpen.value = false
}

function logout() {
  closeSidebar()
  authStore.logout()
}

function goBack() {
  const fallbackPath = '/admin'
  const targetPath = lastAdminPath.value && lastAdminPath.value !== route.path ? lastAdminPath.value : fallbackPath

  if (window.history.length > 1) {
    router.back()
    return
  }

  router.push(targetPath)
}

function isActive(itemPath) {
  if (itemPath === '/admin') {
    return route.path === '/admin'
  }

  return route.path.startsWith(itemPath)
}
</script>

<template>
  <div class="relative min-h-screen md:grid md:grid-cols-[240px_1fr] md:gap-5">
    <div class="mb-3 flex items-center justify-between md:hidden">
      <div class="flex items-center gap-2">
        <button
          v-if="canGoBack"
          type="button"
          class="btn-secondary h-9 w-9 p-0"
          aria-label="Go back"
          title="Go back"
          @click="goBack"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </button>
        <h1 class="text-base font-bold text-slate-900">Admin Panel</h1>
      </div>
      <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="toggleSidebar">Menu</button>
    </div>

    <div v-if="isSidebarOpen" class="fixed inset-0 z-20 bg-slate-950/40 md:hidden" @click="closeSidebar" />

    <aside
      class="panel fixed inset-y-0 left-0 z-30 flex w-64 flex-col p-4 transition-transform md:static md:w-auto"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <div class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4">
        <p class="text-base font-bold text-slate-900">Hi {{ displayName }}</p>
        <button
          type="button"
          class="btn-secondary h-10 w-10 p-0"
          aria-label="Logout"
          title="Logout"
          @click="logout"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2v10" />
            <path d="M6.2 5.9a8 8 0 1 0 11.6 0" />
          </svg>
        </button>
      </div>

      <nav class="flex-1 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="block rounded-lg px-3 py-2 text-sm font-semibold transition"
          :class="
            isActive(item.to)
              ? 'bg-blue-600 text-white'
              : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
          "
          @click="closeSidebar"
        >
          {{ item.label }}
        </RouterLink>
      </nav>
    </aside>

    <main class="min-w-0">
      <div class="panel p-4 sm:p-6">
        <div class="mb-4 hidden items-center gap-2 md:flex">
          <button
            v-if="canGoBack"
            type="button"
            class="btn-secondary h-9 w-9 p-0"
            aria-label="Go back"
            title="Go back"
            @click="goBack"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" />
            </svg>
          </button>
        </div>
        <slot />
      </div>
    </main>
  </div>
</template>
