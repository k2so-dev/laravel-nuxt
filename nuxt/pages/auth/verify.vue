<script lang="ts" setup>
definePageMeta({
  validate: (route) => !!route.query.verify_url,
});

const route = useRoute();
const auth = useAuthStore();

const { pending, error } = useLazyFetch<any>(route.query.verify_url as string, {
  async onResponse({ response }) {
    if (response._data?.ok) {
      await auth.fetchUser();
    }
  },
});
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <div class="space-y-4">
      <h1
        class="text-3xl font-black leading-tight tracking-tight flex items-center gap-2"
      >
        Email Verification
        <UIcon v-if="pending" name="i-heroicons-arrow-path-solid" class="animate-spin" />
        <span v-else-if="error" class="text-red-500">Error</span>
        <span v-else class="text-emerald-500">Done</span>
      </h1>
      <div v-if="error && error.data?.message">{{ error.data?.message }}</div>

      <div class="text-sm">
        <NuxtLink class="text-sm" to="/">Back to Home</NuxtLink>
      </div>
    </div>
  </UCard>
</template>
