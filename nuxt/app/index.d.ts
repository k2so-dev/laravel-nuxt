declare module '#app' {
  interface NuxtApp {
    $storage(msg: string): string
  }
}

export { }