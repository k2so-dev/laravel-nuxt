export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useAuthStore();

  if (auth.logged && auth.user.must_verify_email) {
    const toast = useToast();

    toast.add({
      icon: "i-heroicons-exclamation-circle-solid",
      title: "Please confirm your email.",
      color: "error",
    });

    return navigateTo('/account/general');
  }
})
