<script lang="ts" setup>
const auth = useAuthStore();
const { $storage } = useNuxtApp();

const userItems = [
  [
    {
      slot: "overview",
    },
  ],
  [
    {
      label: "Account",
      to: "/account/general",
      icon: "i-heroicons-user",
    },
    {
      label: "Devices",
      to: "/account/devices",
      icon: "i-heroicons-device-phone-mobile",
    },
  ],
  [
    {
      label: "Sign out",
      onSelect() {
        auth.logout();
      },
      class: 'cursor-pointer',
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
  ],
];

const items = [
  {
    label: 'Guide',
    icon: 'i-lucide-book-open',
    active: true,
    children: [
      {
        label: 'Nuxt.js Docs',
        description: 'Nuxt aims to simplify and optimize web dev with great developer experience.',
        icon: 'i-simple-icons:nuxt',
        to: "https://nuxt.com/docs/getting-started/introduction",
        target: "_blank",
      },
      {
        label: 'Nuxt UI v3 Docs',
        description: 'Nuxt UI library: Styled, accessible, customizable components for web apps.',
        icon: 'i-simple-icons:nuxt',
        to: "https://ui3.nuxt.dev/getting-started",
        target: "_blank",
      },
      {
        label: 'Laravel 11.x',
        description: 'Laravel is a web application framework with expressive, elegant syntax.',
        icon: 'i-simple-icons:laravel',
        to: "https://laravel.com/docs/11.x",
        target: "_blank",
      },
      {
        label: 'Tailwind CSS 4',
        icon: 'i-simple-icons:tailwindcss',
        description: 'Rapidly build modern websites without ever leaving your HTML.',
        to: "https://tailwindcss.com/docs/v4-beta",
        target: "_blank",
      }
    ]
  },
  {
    label: 'UI Components',
    icon: 'i-lucide-box',
    to: 'https://ui3.nuxt.dev/components',
    target: "_blank",
    children: [
      {
        label: 'Link',
        icon: 'i-lucide-file-text',
        description: 'Use NuxtLink with superpowers.',
        to: 'https://ui3.nuxt.dev/components/link',
        target: "_blank",
      },
      {
        label: 'Modal',
        icon: 'i-lucide-file-text',
        description: 'Display a modal within your application.',
        to: 'https://ui3.nuxt.dev/components/modal',
        target: "_blank",
      },
      {
        label: 'NavigationMenu',
        icon: 'i-lucide-file-text',
        description: 'Display a list of links.',
        to: 'https://ui3.nuxt.dev/components/navigation-menu',
        target: "_blank",
      },
      {
        label: 'Pagination',
        icon: 'i-lucide-file-text',
        description: 'Display a list of pages.',
        to: 'https://ui3.nuxt.dev/components/pagination',
        target: "_blank",
      },
      {
        label: 'Popover',
        icon: 'i-lucide-file-text',
        description: 'Display a non-modal dialog that floats around a trigger element.',
        to: 'https://ui3.nuxt.dev/components/popover',
        target: "_blank",
      },
      {
        label: 'Progress',
        icon: 'i-lucide-file-text',
        description: 'Show a horizontal bar to indicate task progression.',
        to: 'https://ui3.nuxt.dev/components/progress',
        target: "_blank",
      }
    ]
  },
  {
    label: 'UI Composables',
    icon: 'i-lucide-database',
    to: 'https://ui3.nuxt.dev/composables',
    target: "_blank",
    children: [
      {
        label: 'defineShortcuts',
        icon: 'i-lucide-file-text',
        description: 'Define shortcuts for your application.',
        to: 'https://ui3.nuxt.dev/composables/define-shortcuts',
        target: "_blank",
      },
      {
        label: 'useModal',
        icon: 'i-lucide-file-text',
        description: 'Display a modal within your application.',
        to: 'https://ui3.nuxt.dev/composables/use-modal',
        target: "_blank",
      },
      {
        label: 'useSlideover',
        icon: 'i-lucide-file-text',
        description: 'Display a slideover within your application.',
        to: 'https://ui3.nuxt.dev/composables/use-slideover',
        target: "_blank",
      },
      {
        label: 'useToast',
        icon: 'i-lucide-file-text',
        description: 'Display a toast within your application.',
        to: 'https://ui3.nuxt.dev/composables/use-toast',
        target: "_blank",
      }
    ]
  },
  {
    label: 'GitHub',
    icon: 'i-simple-icons-github',
    to: 'https://github.com/k2so-dev/laravel-nuxt',
    target: '_blank'
  },
  {
    label: 'Help',
    icon: 'i-lucide-circle-help',
    disabled: true
  }
]

const isSideOpen = ref(false);
</script>
<template>
  <header
    class="bg-background/75 backdrop-blur -mb-px sticky top-0 z-50 border-b border-dashed border-gray-200/80 dark:border-gray-800/80"
  >
    <UContainer class="flex items-center justify-between gap-3 h-16 py-2">
      <AppLogo class="lg:flex-1" />

      <UNavigationMenu orientation="horizontal" :items="items" class="hidden lg:block" />

      <div class="flex items-center justify-end gap-3 lg:flex-1">
        <AppTheme />

        <UDropdownMenu
          v-if="auth.logged"
          :items="userItems"
          :content="{ side: 'bottom', align: 'end' }"
        >
          <ULink class="cursor-pointer">
            <UAvatar
              icon="i-heroicons-user"
              class="rounded-lg"
              size="md"
              :src="$storage(auth.user.avatar)"
              :alt="auth.user.name"
            />
          </ULink>

          <template #overview>
            <div class="text-left">
              <p>Signed in as</p>
              <p class="truncate font-medium text-neutral-900 dark:text-white">
                {{ auth.user.email }}
              </p>
            </div>
          </template>
        </UDropdownMenu>
        <UButton v-else label="Log In" to="/auth/login" variant="ghost" color="neutral" />

        <UDrawer
          v-model:open="isSideOpen"
          direction="right"
        >
          <UButton
            class="lg:hidden"
            variant="ghost"
            color="neutral"
            icon="i-heroicons-bars-3"
          />
          <template #content>
            <div class="me-5">
              <div
                class="flex w-3xs items-center justify-between gap-3 h-16 py-2 border-b border-dashed border-gray-200/80 dark:border-gray-800/80"
              >
                <AppLogo />
                <UButton
                  variant="ghost"
                  color="neutral"
                  icon="i-heroicons-x-mark-20-solid"
                  @click="isSideOpen = false"
                />
              </div>

              <div class="flex-1 py-4 sm:py-6">
                <UNavigationMenu orientation="vertical" :items="items" />
              </div>
            </div>
          </template>
        </UDrawer>
      </div>
    </UContainer>
  </header>

</template>
