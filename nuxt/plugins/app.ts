import { ofetch } from 'ofetch'

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
          process.server ? {
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

      return process.server ?
        config.apiLocal + config.public.apiPrefix
        : config.public.apiBase + config.public.apiPrefix;
    }

    function buildSecureMethod(options: FetchOptionsWithHooks): void {
      if (process.server) return;

      const method = options.method?.toLowerCase() ?? 'get'

      if (options.body instanceof FormData && method === 'put') {
        options.method = 'POST';
        options.body.append('_method', 'PUT');
      }
    }

    function isRequestWithAuth(path: string): boolean {
      return !path.startsWith('/_nuxt')
        && !path.startsWith('http://')
        && !path.startsWith('https://');
    }

    globalThis.$fetch = ofetch.create(<FetchOptionsWithHooks>{
      retry: false,

      async onRequest(context: FetchContextWithHooks) {
        if (typeof context.options.hooks?.onRequest === 'function') {
          await context.options.hooks.onRequest(context);
        }

        if (!isRequestWithAuth(context.request.toString())) return

        context.options.credentials = 'include';

        context.options.baseURL = buildBaseURL(context.options.baseURL ?? '');
        context.options.headers = buildHeaders(context.options.headers);

        buildSecureMethod(context.options);
      },

      async onRequestError(context: FetchContextWithHooksRequestError) {
        if (typeof context.options.hooks?.onRequestError === 'function') {
          await context.options.hooks.onRequestError(context);
        }

        if (process.server) return;

        useToast().add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: 'red',
          title: context.error.message ?? 'Something went wrong',
        })
      },

      async onResponse(context: FetchContextWithHooksResponse) {
        if (typeof context.options.hooks?.onResponse === 'function') {
          await context.options.hooks.onResponse(context);
        }
      },

      async onResponseError(context: FetchContextWithHooksResponse) {
        if (typeof context.options.hooks?.onResponseError === 'function') {
          await context.options.hooks.onResponseError(context);
        }

        if (context.response.status === 401) {
          if (auth.logged) {
            auth.token = ''
            auth.user = <User>{}
          }

          if (process.client) {
            useToast().add({
              title: 'Please log in to continue',
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'primary',
            })
          }
        } else if (context.response.status !== 422) {
          if (process.client) {
            useToast().add({
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'red',
              title: context.response._data?.message ?? context.response.statusText ?? 'Something went wrong',
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
