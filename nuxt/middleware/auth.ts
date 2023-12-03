export default defineNuxtRouteMiddleware((to, from) => {
  const nuxtApp = useNuxtApp()
  const auth = useAuthStore()

  if (!auth.logged) {
    return nuxtApp.runWithContext(() => navigateTo('/'))
  }
})
