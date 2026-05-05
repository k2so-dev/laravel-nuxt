import type { NitroFetchRequest } from 'nitropack/types';
import type { HttpFetchOptions } from '~';

export function $http<T = unknown>(request: NitroFetchRequest, opts?: HttpFetchOptions): Promise<T> {
  const { $http: http } = useNuxtApp();

  return http(request, opts);
}

export function $storage(path: string): string {
  if (!path) return '';

  const config = useRuntimeConfig();

  return path.startsWith('http://') || path.startsWith('https://')
    ? path
    : config.public.storageBase + path;
}
