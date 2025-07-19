export default defineNuxtPlugin(async (nuxtApp) => {
  const auth = useAuthStore();
  const config = useRuntimeConfig();

  if (import.meta.client) {
    $fetch('/sanctum/csrf-cookie', {
      baseURL: config.public.apiBase,
      credentials: 'include',
    });
  }

  await auth.fetchUser();
})
