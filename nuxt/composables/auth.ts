import type { User } from "~/stores/user"

export const useAuth = () => {
  const userStore = useUserStore()
  const token = useCookie('token', { path: '/', maxAge: 60 * 60 * 24 * 30 })

  const logged = computed(() => !!token.value)
  const user = computed(() => userStore.user)

  const { execute: logout } = useFetch('logout', {
    method: 'POST',
    immediate: false,
    onResponse({ response }) {
      token.value = null
      userStore.user = {} as User
  
      useRouter().push('/')
    },
  })

  const { execute: fetchUser } = useFetch('user', {
    immediate: false,
    transform: ({ user }) => {
      user.avatar = `https://api.dicebear.com/7.x/icons/svg?seed=${user.name}`
      userStore.user = user
    }
  })

  return { logged, token, user, logout, fetchUser }
}
