export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useAuthStore()

  if (auth.logged && !auth.hasRole('user')) {
    const toast = useToast()

    toast.add({
      icon: "i-heroicons-exclamation-circle-solid",
      title: "Access denied.",
      color: "error",
    });

    return false;
  }
})
