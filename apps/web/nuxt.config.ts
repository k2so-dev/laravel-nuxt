// https://nuxt.com/docs/4.x/api/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-05-04',

  vite: {
    server: {
      allowedHosts: ["localhost", "127.0.0.1"],
    },
  },

  /**
   * Manually disable nuxt telemetry.
   * @see [Nuxt Telemetry](https://github.com/nuxt/telemetry) for more information.
   */
  telemetry: {
    enabled: true,
  },

  $development: {
    ssr: true,
    devtools: {
      enabled: false,
    },
  },

  $production: {
    ssr: true,
  },

  app: {
    head: {
      title: 'Home',
      titleTemplate: '%s | LaravelNuxt Boilerplate',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      ],
    },
  },

  routeRules: {
    'auth/verify': { ssr: false }
  },

  /**
   * Fontshare API is unreachable from some networks and hangs SSR rendering.
   * @see https://fonts.nuxt.com/get-started/configuration#providers
   */
  fonts: {
    providers: {
      fontshare: false,
    },
  },

  css: ['~/assets/css/main.css'],

  /**
   * @see https://https://nuxt.com/docs/4.x/api/nuxt-config#modules-1
   */
  modules: [
    '@nuxt/ui',
    '@pinia/nuxt',
    'dayjs-nuxt',
    'nuxt-security',
  ],

  security: {
    headers: {
      crossOriginEmbedderPolicy: 'unsafe-none',
      crossOriginOpenerPolicy: 'same-origin-allow-popups',
      contentSecurityPolicy: {
        "img-src": ["'self'", "data:", "blob:", "https://*", import.meta.env.APP_URL || 'http://127.0.0.1:8000', new URL(import.meta.env.IMGPROXY_URL || 'http://127.0.0.1:8080').origin],
      },
    },
  },

  dayjs: {
    locales: ['en'],
    plugins: ['relativeTime', 'utc', 'timezone'],
    defaultLocale: 'en',
    defaultTimezone: 'UTC',
  },

  typescript: {
    strict: false,
  },

  ui: {
    prose: true,
    content: true
  },

  /**
   * @see https://nuxt.com/docs/4.x/api/nuxt-config#runtimeconfig-1
   */
  runtimeConfig: {
    apiLocal: import.meta.env.API_LOCAL_URL,
    public: {
      apiBase: import.meta.env.APP_URL,
      apiPrefix: '/api/v1',
      providers: {
        google: {
          name: "Google",
          icon: "",
          color: "neutral",
          variant: "soft",
        },
      },
    },
  },
})
