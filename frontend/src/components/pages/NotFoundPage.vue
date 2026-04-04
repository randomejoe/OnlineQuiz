<template>
  <section class="page-shell pt-6">
    <article class="panel mx-auto max-w-xl px-6 py-12 text-center">
      <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-blue-600">404</p>
      <h1 class="title-xl">Page not found</h1>
      <p class="subtitle mx-auto mt-2 max-w-sm">
        The page you requested does not exist or may have been moved.
      </p>

      <div class="mt-7 flex flex-wrap justify-center gap-2">
        <RouterLink to="/" class="btn-primary">Go to home route</RouterLink>
        <RouterLink to="/login" class="btn-secondary">Go to login</RouterLink>
      </div>
    </article>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
