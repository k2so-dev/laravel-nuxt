import type { NitroFetchRequest } from 'nitropack/types';
import type { HttpFetchOptions, HttpFetchContext } from '~';

async function callHooks(context, hooks) {
  if (Array.isArray(hooks)) {
    for (const hook of hooks) {
      await hook(context);
    }
  } else if (hooks) {
    await hooks(context);
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();
  const requestUrl = useRequestURL();
  const requestHeaders = useRequestHeaders(['x-forwarded-for', 'user-agent']);
  const toast = useToast();
  const token = useCookie('token', {
    path: '/',
    sameSite: 'strict',
    secure: config.public.apiBase.startsWith('https://'),
    maxAge: 60 * 60 * 24 * 365
  });

  function storage(path: string): string {
    if (!path) return '';
    return path.startsWith('http://') || path.startsWith('https://')
      ? path
      : config.public.storageBase + path;
  }

  function buildHeaders(headers: any): Headers {
    return {
      Accept: 'application/json',
      Authorization: token.value ? `Bearer ${token.value}` : undefined,
      ...headers,
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

        if (import.meta.client) {
          toast.add({
            title: 'Please log in to continue',
            icon: 'i-heroicons-exclamation-circle-solid',
            color: 'warning'
          });
        }
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
      storage,
      token,
      http
    }
  }
})
