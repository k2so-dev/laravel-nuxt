export default defineNuxtRouteMiddleware((to, from) => {
  const nuxtApp = useNuxtApp()
  const auth = useAuthStore()

  if (auth.logged && !auth.user.roles.includes('user')) {
    return nuxtApp.runWithContext(() => {
      useToast().add({
        icon: "i-heroicons-exclamation-circle-solid",
        title: "Access denied.",
        color: "red",
      });

      return navigateTo('/')
    })
  }
})
