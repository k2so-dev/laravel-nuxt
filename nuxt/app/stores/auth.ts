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
  const nuxtApp = useNuxtApp();
  const user = ref(<User>{});
  const logged = computed(() => !!nuxtApp.$token.value);

  const { refresh: logout } = useHttp<any>('logout', {
    method: 'POST',
    immediate: false,
    onFetchResponse: ({ response }) => {
      if (response.status === 200) {
        reset();
        navigateTo('/');
      }
    }
  });

  const { refresh: fetchUser } = useHttp<any>('user', {
    immediate: false,
    onFetchResponse({ response }) {
      if (response.status === 200) {
        user.value = response._data.user
      }
    }
  });

  function reset(): void {
    nuxtApp.$token.value = ''
    user.value = <User>{}
  }

  function hasRole(name: string): boolean {
    return user.value.roles?.includes(name);
  }

  return { user, logged, logout, fetchUser, reset, hasRole }
})
