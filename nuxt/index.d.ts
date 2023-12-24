import type { FetchOptions, FetchContext, FetchResponse } from 'ofetch';

declare module '#app' {
  interface NuxtApp {
    $storage(msg: string): string
  }
}

declare global {
  type FetchContextRequestError = FetchContext & { error: Error };
  type FetchContextResponse = FetchContext & { response: FetchResponse<ResponseType> };

  type WithHooks = {
    hooks: {
      onRequest?(context: FetchContext): Promise<void> | void;
      onRequestError?(context: FetchContextRequestError): Promise<void> | void;
      onResponse?(context: FetchContextResponse): Promise<void> | void;
      onResponseError?(context: FetchContextResponse): Promise<void> | void;
    }
  }

  type FetchOptionsWithHooks = FetchOptions & WithHooks;
  type FetchContextWithHooks = FetchContext & { options: FetchOptionsWithHooks };
  type FetchContextWithHooksRequestError = FetchContextWithHooks & { error: Error };
  type FetchContextWithHooksResponse = FetchContextWithHooks & { response: FetchResponse<ResponseType> };
}

export { }