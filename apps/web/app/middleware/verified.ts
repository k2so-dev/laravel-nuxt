import authMiddleware from "./auth";

export default defineNuxtRouteMiddleware(async (to, from) => {
  await authMiddleware(to, from);

  const auth = useAuthStore();

  if (auth.user.must_verify_email) {
    const toast = useToast();

    toast.add({
      icon: "i-lucide-circle-alert",
      title: "Please confirm your email.",
      color: "error",
    });

    return navigateTo('/account/general');
  }
})
