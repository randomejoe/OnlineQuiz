<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'
import { useAttemptStore } from '../../../stores/attempt'
import ConfirmDialog from '../../molecules/ConfirmDialog/ConfirmDialog.vue'

const route = useRoute()
const authStore = useAuthStore()
const attemptStore = useAttemptStore()
const isSidebarOpen = ref(false)
const showLogoutConfirm = ref(false)
const logoutLoading = ref(false)

const navItems = [
  { label: 'Quizzes', to: '/quizzes' },
  { label: 'History', to: '/history' },
]

const displayName = computed(() => authStore.user?.name || 'there')

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function closeSidebar() {
  isSidebarOpen.value = false
}

function logout() {
  closeSidebar()
  if (route.name === 'quiz-take' && attemptStore.hasActiveAttempt) {
    showLogoutConfirm.value = true
    return
  }
  authStore.logout()
}

async function confirmLogout() {
  if (logoutLoading.value) return

  try {
    logoutLoading.value = true
    await attemptStore.abandonAttempt()
  } catch {
    // Ignore API failures here and still clear local active attempt state before logout.
  } finally {
    attemptStore.clearActiveAttempt()
    showLogoutConfirm.value = false
    logoutLoading.value = false
    authStore.logout()
  }
}

function isActive(path) {
  return route.path === path || route.path.startsWith(`${path}/`)
}
</script>

<template>
  <div class="relative min-h-screen md:grid md:grid-cols-[220px_1fr] md:gap-5">
    <div class="mb-3 flex items-center justify-between md:hidden">
      <h1 class="text-base font-bold text-slate-900">Student Menu</h1>
      <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="toggleSidebar">Menu</button>
    </div>

    <div v-if="isSidebarOpen" class="fixed inset-0 z-20 bg-slate-950/40 md:hidden" @click="closeSidebar" />

    <aside
      class="panel fixed inset-y-0 left-0 z-30 flex w-60 flex-col p-4 transition-transform md:static md:w-auto"
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
          :class="isActive(item.to) ? 'bg-blue-600 text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'"
          @click="closeSidebar"
        >
          {{ item.label }}
        </RouterLink>
      </nav>
    </aside>

    <main class="min-w-0">
      <slot />
    </main>

    <ConfirmDialog
      v-model="showLogoutConfirm"
      title="Leave quiz and logout?"
      message="Your current quiz attempt will be discarded and not saved."
      confirm-label="Leave and logout"
      cancel-label="Stay"
      :loading="logoutLoading"
      @confirm="confirmLogout"
    />
  </div>
</template>
