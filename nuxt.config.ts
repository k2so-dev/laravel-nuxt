// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  srcDir: 'nuxt/',

  $development: {
    ssr: true,
    devtools: {
      enabled: false,
    },
  },

  app: {
    head: {
      title: 'Laravel/Nuxt Boilerplate',
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

  css: [
    '@/assets/css/main.css',
  ],

  /**
   * @see https://v3.nuxtjs.org/api/configuration/nuxt.config#modules
   */
  modules: [
    '@nuxt/ui',
    '@nuxt/image',
    '@pinia/nuxt',
    'dayjs-nuxt',
  ],

  ui: {
    icons: ['heroicons'],
  },

  image: {
    domains: [
      process.env.API_URL || 'http://127.0.0.1:8000'
    ],
    alias: {
      api: process.env.API_URL || 'http://127.0.0.1:8000'
    }
  },

  dayjs: {
    locales: ['en'],
    plugins: ['relativeTime', 'utc', 'timezone'],
    defaultLocale: 'en',
    defaultTimezone: 'America/New_York',
  },

  /**
   * @see https://v3.nuxtjs.org/guide/features/runtime-config#exposing-runtime-config
   */
  runtimeConfig: {
    apiLocal: process.env.API_LOCAL_URL,
    public: {
      apiBase: process.env.API_URL,
      apiPrefix: '/api/v1',
      storageBase: process.env.API_URL + '/storage/',
    },
  },
})
