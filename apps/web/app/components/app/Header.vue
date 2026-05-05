<script lang="ts" setup>
const auth = useAuthStore();

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
        label: 'Nuxt UI v4 Docs',
        description: 'Nuxt UI library: Styled, accessible, customizable components for web apps.',
        icon: 'i-simple-icons:nuxt',
        to: "https://ui.nuxt.com/docs/getting-started",
        target: "_blank",
      },
      {
        label: 'Laravel 13.x',
        description: 'Laravel is a web application framework with expressive, elegant syntax.',
        icon: 'i-simple-icons:laravel',
        to: "https://laravel.com/docs/13.x",
        target: "_blank",
      },
      {
        label: 'Tailwind CSS 4',
        icon: 'i-simple-icons:tailwindcss',
        description: 'Rapidly build modern websites without ever leaving your HTML.',
        to: "https://tailwindcss.com/docs",
        target: "_blank",
      }
    ]
  },
  {
    label: 'UI Components',
    icon: 'i-lucide-box',
    to: 'https://ui.nuxt.com/docs/components',
    target: "_blank",
    children: [
      {
        label: 'Link',
        icon: 'i-lucide-file-text',
        description: 'Use NuxtLink with superpowers.',
        to: 'https://ui.nuxt.com/docs/components/link',
        target: "_blank",
      },
      {
        label: 'Modal',
        icon: 'i-lucide-file-text',
        description: 'Display a modal within your application.',
        to: 'https://ui.nuxt.com/docs/components/modal',
        target: "_blank",
      },
      {
        label: 'NavigationMenu',
        icon: 'i-lucide-file-text',
        description: 'Display a list of links.',
        to: 'https://ui.nuxt.com/docs/components/navigation-menu',
        target: "_blank",
      },
      {
        label: 'Pagination',
        icon: 'i-lucide-file-text',
        description: 'Display a list of pages.',
        to: 'https://ui.nuxt.com/docs/components/pagination',
        target: "_blank",
      },
      {
        label: 'Popover',
        icon: 'i-lucide-file-text',
        description: 'Display a non-modal dialog that floats around a trigger element.',
        to: 'https://ui.nuxt.com/docs/components/popover',
        target: "_blank",
      },
      {
        label: 'Progress',
        icon: 'i-lucide-file-text',
        description: 'Show a horizontal bar to indicate task progression.',
        to: 'https://ui.nuxt.com/docs/components/progress',
        target: "_blank",
      }
    ]
  },
  {
    label: 'UI Composables',
    icon: 'i-lucide-database',
    to: 'https://ui.nuxt.com/docs/composables',
    target: "_blank",
    children: [
      {
        label: 'defineShortcuts',
        icon: 'i-lucide-file-text',
        description: 'Define shortcuts for your application.',
        to: 'https://ui.nuxt.com/docs/composables/define-shortcuts',
        target: "_blank",
      },
      {
        label: 'useModal',
        icon: 'i-lucide-file-text',
        description: 'Display a modal within your application.',
        to: 'https://ui.nuxt.com/docs/composables/use-modal',
        target: "_blank",
      },
      {
        label: 'useSlideover',
        icon: 'i-lucide-file-text',
        description: 'Display a slideover within your application.',
        to: 'https://ui.nuxt.com/docs/composables/use-slideover',
        target: "_blank",
      },
      {
        label: 'useToast',
        icon: 'i-lucide-file-text',
        description: 'Display a toast within your application.',
        to: 'https://ui.nuxt.com/docs/composables/use-toast',
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

  <UHeader toggle-side="left">
    <template #title>
      <AppLogo />
    </template>

    <UNavigationMenu :items="items" />

    <template #right>
      <UColorModeButton />

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
    </template>

    <template #body>
      <UNavigationMenu :items="items" orientation="vertical" class="-mx-2.5" />
    </template>
  </UHeader>
</template>
