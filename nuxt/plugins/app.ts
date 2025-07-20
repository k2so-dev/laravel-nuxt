import type { NitroFetchRequest } from 'nitropack/types';
import type { HttpFetchOptions, HttpFetchContext } from '~';

export default defineNuxtPlugin((nuxtApp) => {
  const toast = useToast();
  const config = useRuntimeConfig();
  const requestUrl = useRequestURL();
  const requestHeaders = useRequestHeaders(['cookie', 'x-forwarded-for', 'user-agent']);
  const xsrf = useCookie('XSRF-TOKEN');
  const auth = useAuthStore();

  function buildHeaders(headers: any): Headers {
    let authHeaders = {};

    if (config.public.authGuard === 'web') {
      authHeaders['X-XSRF-TOKEN'] = xsrf.value;
    } else if (config.public.authGuard === 'api' && auth.token) {
      authHeaders['Authorization'] = `Bearer ${auth.token}`;
    }

    return {
      Accept: 'application/json',
      ...headers,
      ...authHeaders,
      ...(
        import.meta.server
          ? {
            referer: requestUrl.toString(),
            ...requestHeaders
          }
          : {}
      )
    };
  }

  function buildBaseURL(baseURL: string): string {
    if (baseURL) return baseURL;

    return import.meta.server
      ? config.apiLocal + config.public.apiPrefix
      : config.public.apiBase + config.public.apiPrefix;
  }

  function buildSecureMethod(options: HttpFetchOptions): void {
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

  async function callHooks(context, hooks) {
    if (Array.isArray(hooks)) {
      for (const hook of hooks) {
        await hook(context);
      }
    } else if (hooks) {
      await hooks(context);
    }
  }

  const http = $fetch.create<unknown, NitroFetchRequest>(<HttpFetchOptions>{
    baseURL: '',
    retry: false,
    async onRequest(context: HttpFetchContext) {
      await callHooks(context, context.options.onFetch);

      if (!isRequestWithAuth(context.options.baseURL ?? '', context.request.toString())) return;

      context.options.credentials = 'include';
      context.options.baseURL = buildBaseURL(context.options.baseURL ?? '');
      context.options.headers = buildHeaders(context.options.headers);

      buildSecureMethod(context.options);
    },
    async onRequestError(context: HttpFetchContext) {
      await callHooks(context, context.options.onFetchError);

      if (import.meta.server || context.error.name === 'AbortError') return;

      toast.add({
        icon: 'i-heroicons-exclamation-circle-solid',
        color: "error",
        title: context.error.message ?? 'Something went wrong'
      });
    },
    async onResponse(context: HttpFetchContext) {
      await callHooks(context, context.options.onFetchResponse);
    },
    async onResponseError(context: HttpFetchContext) {
      await callHooks(context, context.options.onFetchResponseError);

      if (context.response.status === 401) {
        const auth = useAuthStore();
        auth.reset();
      } else if (context.response.status !== 422 && import.meta.client) {
        toast.add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: "error",
          title: context.response._data?.message ?? context.response.statusText ?? 'Something went wrong'
        });
      }
    },
  });

  return {
    provide: {
      http
    }
  }
})
