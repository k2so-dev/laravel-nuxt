export default defineNuxtPlugin(async (nuxtApp) => {
  const auth = useAuthStore();

  if (import.meta.client) {
    auth.fetchCsrf();
  }

  if (auth.logged) {
    await auth.fetchUser();
  }
})
