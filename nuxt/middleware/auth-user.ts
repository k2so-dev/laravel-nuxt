export default defineNuxtRouteMiddleware((to, from) => {
  const { logged } = useAuth()
  if (!logged.value) return navigateTo('/')
})
