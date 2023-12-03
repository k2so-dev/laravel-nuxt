import { ofetch } from 'ofetch'
import type { FetchOptions } from 'ofetch';

export default defineNuxtPlugin({
  name: 'app',
  enforce: 'default',
  parallel: true,
  async setup(nuxtApp) {
    const config = useRuntimeConfig()
    const auth = useAuthStore()

    nuxtApp.provide('storage', (path: string): string => path ? config.public.storageBase + path : '')

    globalThis.$fetch = ofetch.create(<FetchOptions>{
      retry: false,
      credentials: 'include',
      baseURL: config.public.apiBase + config.public.apiPrefix,
      headers: {
        Accept: 'application/json'
      },

      async onRequest({ request, options }) {
        if (request.toString().includes('/_nuxt/builds/meta/')) {
          options.baseURL = ''
          return
        }

        options.headers = (options.headers || {}) as { [key: string]: string }

        if (process.server) {
          options.headers = {
            referer: useRequestURL().toString(),
            ...useRequestHeaders(['x-forwarded-for', 'user-agent', 'referer']),
          }

          if (options.baseURL === config.public.apiBase + config.public.apiPrefix) {
            options.baseURL = config.apiLocal + config.public.apiPrefix
          }
        }

        if (auth.logged) {
          options.headers['Authorization'] = 'Bearer ' + auth.token
        }

        if (!process.client) return

        const method = options.method?.toLowerCase() ?? 'get'

        if (!['post', 'put', 'delete', 'patch'].includes(method)) return

        if (options.body instanceof FormData && method === 'put') {
          options.method = 'POST';
          options.body.append('_method', 'PUT');
        }
      },

      onRequestError({ error }) {
        if (process.client) {
          useToast().add({
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'red',
            title: error.message ?? 'Something went wrong',
          })
        }
      },

      async onResponseError({ response }) {
        if (process.server) return;

        if (response.status === 401) {
          useToast().add({
            title: 'Please log in to continue',
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'primary',
          })
        } else if (response.status !== 422) {
          useToast().add({
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'red',
            title: response._data?.message ?? response.statusText ?? 'Something went wrong',
          })
        }
      }
    })

    if (auth.logged) {
      await auth.fetchUser();
    }
  },
  // hooks: {
  //   'app:created'(nuxtApp) {
  //     const config = useRuntimeConfig()
  //     nuxtApp.provide('storage', (path: string): string => config.public.storageBase + path)
  //   }
  // },
})
