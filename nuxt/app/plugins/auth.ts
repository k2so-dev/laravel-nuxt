export default defineNuxtPlugin(async (nuxtApp) => {
  const auth = useAuthStore();

  if (auth.logged) {
    await auth.fetchUser();
  }
})
