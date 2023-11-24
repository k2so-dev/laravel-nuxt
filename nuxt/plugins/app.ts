import { ofetch } from 'ofetch'
import type { FetchOptions } from 'ofetch';

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const userStore = useUserStore()
  const { logged, token, fetchUser } = useAuth()

  nuxtApp.hook('app:created', () => {
    globalThis.$fetch = ofetch.create(<FetchOptions>{
      retry: false,
      baseURL: config.public.apiBase + config.public.apiPrefix,
      onRequest({ options }) {
        options.headers = (options.headers || {}) as { [key: string]: string }

        if (process.server) {
          useRequestHeaders(['cookie'])

          options.baseURL = config.public.apiLocal + config.public.apiPrefix
          options.headers.Referer = useRequestURL().toString()
        }

        if (logged.value) {
          options.headers.Authorization = `Bearer ${token.value}`
        }
      },
      onRequestError({ error }) {
        useToast().add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: 'red',
          title: 'Something went wrong',
          description: error.message,
        })
      },
      onResponseError: ({ response }) => {
        if (response.status === 401) {
          token.value = null
          userStore.user = {} as User

          useToast().add({
            title: 'Session expired',
            description: 'Please login again',
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'red',
          })

          navigateTo('/auth/login')
        } else if (response.status !== 422) {
          if (process.client) {
            useToast().add({
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'red',
              title: 'Something went wrong',
              description: response._data?.message || response.statusText,
            })
          }
        }
      }
    })
  })

  nuxtApp.hook('app:created', async () => {
    if (logged.value) await fetchUser()

    watch(logged, async (value) => {
      if (value) await fetchUser()
    })
  })
})

