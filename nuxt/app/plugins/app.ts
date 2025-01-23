import type { FetchOptions } from 'ofetch';
import { ofetch } from 'ofetch';

export default defineNuxtPlugin({
  name: 'app',
  enforce: 'default',
  parallel: true,
  async setup(nuxtApp) {
    const config = useRuntimeConfig();
    const auth = useAuthStore();
    const toast = useToast();

    nuxtApp.provide('storage', (path: string): string => {
      if (!path) return '';
      return path.startsWith('http://') || path.startsWith('https://')
        ? path
        : config.public.storageBase + path;
    });

    function buildHeaders(headers: any): Headers {
      const requestHeaders = import.meta.server
        ? {
          'referer': useRequestURL().toString(),
          ...useRequestHeaders(['x-forwarded-for', 'user-agent', 'referer'])
        }
        : {};

      return {
        Accept: 'application/json',
        Authorization: auth.logged ? `Bearer ${auth.token}` : undefined,
        ...headers,
        ...requestHeaders
      };
    }

    function buildBaseURL(baseURL: string): string {
      if (baseURL) return baseURL;

      return import.meta.server
        ? config.apiLocal + config.public.apiPrefix
        : config.public.apiBase + config.public.apiPrefix;
    }

    function buildSecureMethod(options: FetchOptions): void {
      if (!import.meta.server && options.body instanceof FormData &&
        (options.method?.toLowerCase() === 'put')) {
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
        if (!isRequestWithAuth(options.baseURL ?? '', request.toString())) return;

        options.credentials = 'include';
        options.baseURL = buildBaseURL(options.baseURL ?? '');
        options.headers = buildHeaders(options.headers);

        buildSecureMethod(options);
      },
      onRequestError({ error }) {
        if (import.meta.server || error.name === 'AbortError') return;

        toast.add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: "error",
          title: error.message ?? 'Something went wrong'
        });
      },
      onResponseError({ response }) {
        if (response.status === 401) {
          auth.reset();

          if (import.meta.client) {
            toast.add({
              title: 'Please log in to continue',
              icon: 'i-heroicons-exclamation-circle-solid',
              color: 'warning'
            });
          }
        } else if (response.status !== 422 && import.meta.client) {
          toast.add({
            icon: 'i-heroicons-exclamation-circle-solid',
            color: "error",
            title: response._data?.message ?? response.statusText ?? 'Something went wrong'
          });
        }
      }
    });

    if (auth.logged) {
      await auth.fetchUser();
    }
  },
})
