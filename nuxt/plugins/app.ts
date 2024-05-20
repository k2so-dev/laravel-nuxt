import type { FetchOptions } from 'ofetch';
import { ofetch } from 'ofetch';

export default defineNuxtPlugin({
  name: 'app',
  enforce: 'default',
  parallel: true,
  async setup(nuxtApp) {
    const config = useRuntimeConfig()
    const auth = useAuthStore()

    nuxtApp.provide('storage', (path: string): string => {
      if (!path) return ''

      return path.startsWith('http://') || path.startsWith('https://') ?
        path
        : config.public.storageBase + path
    })

    function buildHeaders(headers = <HeadersInit>{}): HeadersInit {
      return {
        ...headers,
        ...{
          'Accept': 'application/json',
        },
        ...(
          import.meta.server ? {
            'referer': useRequestURL().toString(),
            ...useRequestHeaders(['x-forwarded-for', 'user-agent', 'referer']),
          } : {}
        ),
        ...(
          auth.logged ? {
            'Authorization': `Bearer ${auth.token}`
          } : {}
        )
      };
    }

    function buildBaseURL(baseURL: string): string {
      if (baseURL) return baseURL;

      return import.meta.server ?
        config.apiLocal + config.public.apiPrefix
        : config.public.apiBase + config.public.apiPrefix;
    }

    function buildSecureMethod(options: FetchOptions): void {
      if (import.meta.server) return;

      const method = options.method?.toLowerCase() ?? 'get'

      if (options.body instanceof FormData && method === 'put') {
        options.method = 'POST';
        options.body.append('_method', 'PUT');
      }
    }

    function isRequestWithAuth(baseURL: string, path: string): boolean {
      return !baseURL
        && !path.startsWith('/_nuxt')
        && !path.startsWith('http://')
        && !path.startsWith('https://');
    }

    globalThis.$fetch = ofetch.create(<FetchOptions>{
      retry: false,

      onRequest({ request, options }) {
        if (!isRequestWithAuth(options.baseURL ?? '', request.toString())) return

        options.credentials = 'include';

        options.baseURL = buildBaseURL(options.baseURL ?? '');
        options.headers = buildHeaders(options.headers);

        buildSecureMethod(options);
      },

      onRequestError({ error }) {
        if (import.meta.server) return;

        if (error.name === 'AbortError') return;

        useToast().add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: 'red',
          title: error.message ?? 'Something went wrong',
        })
      },

      onResponseError({ response }) {
        if (response.status === 401) {
          if (auth.logged) {
            auth.token = ''
            auth.user = <User>{}
          }

          if (import.meta.client) {
            useToast().add({
              title: 'Please log in to continue',
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'primary',
            })
          }
        } else if (response.status !== 422) {
          if (import.meta.client) {
            useToast().add({
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'red',
              title: response._data?.message ?? response.statusText ?? 'Something went wrong',
            })
          }
        }
      }
    })

    if (auth.logged) {
      await auth.fetchUser();
    }
  },
})
