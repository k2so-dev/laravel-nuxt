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

  const tokenCookie = useCookie('token', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  });

  const loggedCookie = useCookie('logged', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  });

  const user = ref(<User>{});

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

  function fetchCsrf(): void {
    $http('/sanctum/csrf-cookie', {
      baseURL: config.public.apiBase,
      credentials: 'include',
      headers: { Accept: 'application/json' }
    });
  }

  async function login(token?: string | null): Promise<void> {
    if (config.public.authGuard === 'api') {
      tokenCookie.value = token || null;
    }

    loggedCookie.value = '1';
    await fetchUser();
  }

  function reset(): void {
    if (config.public.authGuard === 'api') {
      tokenCookie.value = null;
    }

    loggedCookie.value = null;
    user.value = <User>{}
  }

  function hasRole(name: string): boolean {
    return (user.value.roles ?? []).includes(name);
  }

  return {
    user,
    token: tokenCookie,
    logged: loggedCookie,
    login,
    logout,
    fetchUser,
    fetchCsrf,
    reset,
    hasRole,
  }
})
