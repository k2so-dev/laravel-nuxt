export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useAuthStore();

  if (!auth.logged) {
    return navigateTo('/');
  }
})
