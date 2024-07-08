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

  const { refresh: logout } = useFetch<any>('logout', {
    method: 'POST',
    immediate: false,
    onResponse({ response }) {
      if (response.status === 200) {
        token.value = ''
        user.value = <User>{}

        nuxtApp.runWithContext(() => {
          return navigateTo('/');
        })
      }
    }
  })

  const { refresh: fetchUser } = useFetch<any>('user', {
    immediate: false,
    onResponse({ response }) {
      if (response.status === 200) {
        user.value = response._data.user
      }
    }
  })

  return { user, logged, logout, fetchUser, token }
})
