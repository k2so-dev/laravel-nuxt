export default defineNuxtPlugin(async (nuxtApp) => {
  const auth = useAuthStore();

  if (import.meta.client) {
    await auth.fetchCsrf();
  }

  if (auth.logged && !auth.user?.uuid) {
    await auth.fetchUser();
  }
})
