import type { NitroFetchRequest } from 'nitropack/types';
import type { HttpFetchOptions } from '~';

export function $http<T = unknown>(request: NitroFetchRequest, opts?: HttpFetchOptions): Promise<T> {
  const { $http: http } = useNuxtApp();

  return http(request, opts);
}
