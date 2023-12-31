import { defineStore } from 'pinia'

export type User = {
  ulid: string;
  name: string;
  email: string;
  avatar: string;
  must_verify_email: boolean;
  has_password: boolean;
  roles: string[];
  providers: string[];
}

export const useAuthStore = defineStore('auth', () => {
  const config = useRuntimeConfig()
  const nuxtApp = useNuxtApp()

  const user = ref(<User>{});
  const token = useCookie('token', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  })
  const logged = computed(() => !!token.value)

  async function logout() {
    await useFetch('logout', {
      method: 'POST',
    })

    token.value = ''
    user.value = <User>{}

    return nuxtApp.runWithContext(() => {
      return navigateTo('/')
    })
  }

  async function fetchUser() {
    const { data, status } = await useFetch<any>('user')

    if (status.value === 'success') {
      user.value = data.value.user
    }
  }

  return { user, logged, logout, fetchUser, token }
})
