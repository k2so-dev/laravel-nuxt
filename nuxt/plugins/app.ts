import { ofetch } from 'ofetch'
import type { FetchOptions } from 'ofetch';

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const userStore = useUserStore()
  const { logged, token, fetchUser } = useAuth()

  nuxtApp.hook('app:created', () => {
    globalThis.$fetch = ofetch.create(<FetchOptions>{
      retry: 0,
      baseURL: config.public.apiBase + config.public.apiPrefix,
      headers: {
        Accept: 'application/json',
      },
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
      onResponseError: ({ response }) => {
        if (response.status === 401) {
          token.value = null
          userStore.user = {} as User
          navigateTo('/auth/login')
          useToast().add({
            title: 'Session expired',
            description: 'Please login again',
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'red',
          })
        } else if (response.status === 422) {
          let messages = [];

          for (const field in response._data.errors) {
            messages.push({
              path: field,
              message: response._data.errors[field].join(", "),
            });
          }

          throw createError({
            statusCode: response.status,
            message: response._data?.message || response.statusText,
            data: messages,
            fatal: false,
          })
        } else {
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

