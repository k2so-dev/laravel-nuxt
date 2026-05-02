import type { $Fetch } from 'nitropack/types';
import type { HttpUseFetchOptions } from '~';

export function useLazyHttp<T>(
  url: string | (() => string),
  options?: HttpUseFetchOptions<T>,
) {
  return useLazyFetch(url, {
    ...options,
    $fetch: $http as $Fetch
  });
}