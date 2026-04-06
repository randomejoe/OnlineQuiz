<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { del, get, readJsonResponse } from '../../utils/api'
import { formatDateOnly } from '../../utils/format'
import { useAuthStore } from '../../stores/auth'
import AdminLayout from '../templates/AdminLayout/AdminLayout.vue'
import ConfirmDialog from '../molecules/ConfirmDialog/ConfirmDialog.vue'

const router = useRouter()
const authStore = useAuthStore()
const users = ref([])
const loading = ref(false)
const deleting = ref(false)
const error = ref('')

const page = ref(1)
const perPage = 10
const total = ref(0)
const totalPages = ref(1)

const showDeleteDialog = ref(false)
const userToDelete = ref(null)
const deleteMessage = ref('Delete this user? This action cannot be undone.')
const selfDeleteMessage = 'You cannot delete your own account'
const currentUserId = computed(() => authStore.user?.id ?? null)

function isCurrentUser(user) {
  return currentUserId.value !== null && Number(user.id) === Number(currentUserId.value)
}

async function loadUsers() {
  try {
    loading.value = true
    error.value = ''

    const response = await get(`/admin/users?page=${page.value}&per_page=${perPage}`)
    const payload = await readJsonResponse(response, 'Failed to fetch users')

    users.value = Array.isArray(payload.data) ? payload.data : []
    total.value = Number(payload.meta?.total || users.value.length)
    totalPages.value = Math.max(1, Math.ceil(total.value / perPage))

    if (page.value > totalPages.value) {
      page.value = totalPages.value
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load users'
  } finally {
    loading.value = false
  }
}

function openDeleteDialog(user) {
  if (isCurrentUser(user)) {
    error.value = selfDeleteMessage
    return
  }

  userToDelete.value = user
  deleteMessage.value = `Delete ${user.name}? This action cannot be undone.`
  showDeleteDialog.value = true
}

function goToUserAttempts(userId) {
  router.push(`/admin/users/${userId}/attempts`)
}

async function confirmDelete() {
  if (!userToDelete.value) return

  try {
    deleting.value = true
    error.value = ''

    const response = await del(`/admin/users/${userToDelete.value.id}`)
    await readJsonResponse(response, 'Failed to delete user')

    showDeleteDialog.value = false
    userToDelete.value = null

    if (users.value.length === 1 && page.value > 1) {
      page.value -= 1
    }

    await loadUsers()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to delete user'
  } finally {
    deleting.value = false
  }
}

watch(page, loadUsers)
onMounted(loadUsers)
</script>

<template>
  <AdminLayout>
    <section class="space-y-5">
      <div>
        <h1 class="title-xl">Users</h1>
        <p class="subtitle">Review user accounts and manage access.</p>
      </div>

      <p v-if="error" class="status-error">{{ error }}</p>

      <div v-if="loading" class="status-info">Loading users...</div>

      <div v-else class="panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 font-medium">Name</th>
              <th class="px-4 py-3 font-medium">Email</th>
              <th class="px-4 py-3 font-medium">Role</th>
              <th class="px-4 py-3 font-medium">Join date</th>
              <th class="px-4 py-3 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="users.length === 0">
              <td colspan="5" class="px-4 py-5 text-center text-slate-500">No users found.</td>
            </tr>
            <tr v-for="user in users" :key="user.id" class="border-t border-slate-200">
              <td class="px-4 py-3 text-slate-900">{{ user.name }}</td>
              <td class="px-4 py-3 text-slate-600">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  :class="user.role === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="px-4 py-3 text-slate-600">{{ formatDateOnly(user.created_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="goToUserAttempts(user.id)">Attempts</button>
                  <button
                    type="button"
                    class="btn border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:hover:bg-slate-100"
                    :disabled="isCurrentUser(user)"
                    :title="isCurrentUser(user) ? selfDeleteMessage : 'Delete user'"
                    @click="openDeleteDialog(user)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-end gap-2">
        <button type="button" class="btn-secondary" :disabled="page <= 1" @click="page -= 1">Previous</button>
        <span class="text-sm text-slate-600">Page {{ page }} / {{ totalPages }}</span>
        <button type="button" class="btn-secondary" :disabled="page >= totalPages" @click="page += 1">Next</button>
      </div>
    </section>

    <ConfirmDialog
      v-model="showDeleteDialog"
      title="Delete user"
      :message="deleteMessage"
      confirm-label="Delete"
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>
