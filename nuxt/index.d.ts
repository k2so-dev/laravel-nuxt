import type { NitroFetchRequest, NitroFetchOptions } from 'nitropack/types';
import type { FetchContext, FetchOptions, FetchResponse, ResolvedFetchOptions } from 'ofetch';
import type { UseFetchOptions } from 'nuxt/app';
import type { $Fetch } from 'nitropack/types';

declare module '#app' {
  interface NuxtApp {
    $storage(msg: string): string;
    $http<T = unknown>(
      request: NitroFetchRequest,
      opts?: HttpFetchOptions
    ): Promise<T>;
  }
}

type AuthProvider = {
  name: string;
  icon: string;
  color: string;
  variant: string;
  loading?: boolean;
};

type AuthProviders = {
  [key: string]: AuthProvider
}

type MaybePromise<T> = T | Promise<T>;
type MaybeArray<T> = T | T[];
type HttpFetchHook<HttpContext extends HttpFetchContext = HttpFetchContext> = (context: HttpContext) => MaybePromise<void>;

interface HttpFetchOptions extends FetchOptions {
  onFetch?: MaybeArray<HttpFetchHook<HttpFetchContext>>;
  onFetchError?: MaybeArray<HttpFetchHook<HttpFetchContext & {
    error: Error;
  }>>;
  onFetchResponse?: MaybeArray<HttpFetchHook<HttpFetchContext & {
    response: FetchResponse<any>;
  }>>;
  onFetchResponseError?: MaybeArray<HttpFetchHook<HttpFetchContext & {
    response: FetchResponse<any>;
  }>>;
}

interface HttpFetchContext extends FetchContext<any, any> {
  options: ResolvedFetchOptions<any, any> & HttpFetchOptions;
}

type HttpUseFetchOptions<T> = UseFetchOptions<T> & HttpFetchOptions & {
  $fetch?: $Fetch
};


export {
  AuthProviders,
  HttpFetchContext,
  HttpFetchOptions,
  HttpUseFetchOptions,
}
