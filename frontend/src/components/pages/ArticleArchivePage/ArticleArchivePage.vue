<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"
        ></div>
        <p class="text-gray-600">Loading articles...</p>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="min-h-screen flex items-center justify-center"
    >
      <div class="text-center max-w-md">
        <div class="text-red-600 text-5xl mb-4">⚠️</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          Error Loading Articles
        </h2>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <button
          @click="fetchArticles"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Article Archive Template -->
    <ArticleArchive
      v-else
      :articles="articles"
      @article-click="handleArticleClick"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import ArticleArchive from "../../templates/ArticleArchive/ArticleArchive.vue";
import { get } from "../../../utils/api.js";

const articles = ref([]);
const loading = ref(true);
const error = ref(null);

/**
 * Fetch articles from the API
 */
const fetchArticles = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await get("/articles");

    if (!response.ok) {
      throw new Error(
        `Failed to fetch articles: ${response.status} ${response.statusText}`,
      );
    }

    const data = await response.json();
    articles.value = data;
  } catch (err) {
    console.error("Error fetching articles:", err);
    error.value =
      err.message || "Failed to load articles. Please try again later.";
    articles.value = [];
  } finally {
    loading.value = false;
  }
};

/**
 * Handle article click event
 * @param {number} articleId - The ID of the clicked article
 */
const handleArticleClick = (articleId) => {
  // Navigate to article detail page
  // This can be implemented with Vue Router or your preferred routing solution
  console.log("Article clicked:", articleId);
  // Example: router.push(`/articles/${articleId}`);
};

// Fetch articles when component is mounted
onMounted(() => {
  fetchArticles();
});
</script>
