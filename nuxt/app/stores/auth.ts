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
  const config = useRuntimeConfig();
  const user = ref(<User>{});
  const token = useCookie('token', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  });
  const logged = computed(() => !!token.value);

  const { refresh: logout } = useFetch<any>('logout', {
    method: 'POST',
    immediate: false,
    onResponse({ response }) {
      if (response.status === 200) {
        reset();
        navigateTo('/');
      }
    }
  });

  const { refresh: fetchUser } = useFetch<any>('user', {
    immediate: false,
    onResponse({ response }) {
      if (response.status === 200) {
        user.value = response._data.user
      }
    }
  });

  function reset(): void {
    token.value = ''
    user.value = <User>{}
  }

  function hasRole(name: string): boolean {
    return user.value.roles?.includes(name);
  }

  return { user, logged, logout, token, fetchUser, reset, hasRole }
})
