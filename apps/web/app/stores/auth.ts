import { defineStore } from 'pinia'

export type User = {
  uuid: string;
  name: string;
  email: string;
  avatar: string;
  avatar_url: string | null;
  must_verify_email: boolean;
  has_password: boolean;
  roles: string[];
  providers: string[];
}

export const useAuthStore = defineStore('auth', () => {
  const config = useRuntimeConfig();

  const loggedCookie = useCookie('logged', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  });

  const user = ref(<User>{});

  async function logout(): Promise<void> {
    await $http('logout', { method: 'POST' });
    reset();
    await navigateTo('/');
  }

  async function fetchUser(): Promise<void> {
    try {
      const res = await $http<{ user: User }>('user');
      user.value = res.user;
    } catch {
      // ...
    }
  }

  function fetchCsrf(): Promise<unknown> {
    return $http('/sanctum/csrf-cookie', {
      baseURL: config.public.apiBase,
      credentials: 'include',
      headers: { Accept: 'application/json' }
    });
  }

  async function login(): Promise<void> {
    loggedCookie.value = '1';
    await fetchUser();
  }

  function reset(): void {
    loggedCookie.value = null;
    user.value = <User>{}
  }

  function hasRole(name: string): boolean {
    return (user.value.roles ?? []).includes(name);
  }

  return {
    user,
    logged: loggedCookie,
    login,
    logout,
    fetchUser,
    fetchCsrf,
    reset,
    hasRole,
  }
})
