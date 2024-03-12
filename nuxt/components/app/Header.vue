<script lang="ts" setup>
const auth = useAuthStore();
const { $storage } = useNuxtApp();

const userItems = [
  [
    {
      label: "User",
      slot: "overview",
      disabled: true,
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
      click: auth.logout,
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
  ],
];

const navItems = [
  {
    label: "Nuxt.js Docs",
    to: "https://nuxt.com/docs/getting-started/introduction",
    target: "_blank",
    icon: "i-heroicons-link-20-solid",
  },
  {
    label: "Nuxt UI",
    to: "https://ui.nuxt.com/getting-started",
    target: "_blank",
    icon: "i-heroicons-link-20-solid",
  },
  {
    label: "Laravel 11.x",
    to: "https://laravel.com/docs/11.x",
    target: "_blank",
    icon: "i-heroicons-link-20-solid",
  },
];

const isSideOpen = ref(false);
const openSide = () => {
  isSideOpen.value = true;
};

defineShortcuts({
  escape: {
    usingInput: true,
    whenever: [isSideOpen],
    handler: () => {
      isSideOpen.value = false;
    },
  },
});
</script>
<template>
  <header
    class="bg-background/75 backdrop-blur -mb-px sticky top-0 z-50 border-b border-dashed border-gray-200/80 dark:border-gray-800/80"
  >
    <UContainer class="flex items-center justify-between gap-3 h-16 py-2">
      <AppLogo class="lg:flex-1" />

      <nav class="hidden lg:flex">
        <ul class="flex flex-col items-end lg:flex-row lg:items-center lg:gap-x-8">
          <li v-for="item in navItems" class="relative">
            <NuxtLink
              class="text-sm/6 font-semibold flex items-center gap-1 hover:text-primary"
              :to="item.to"
              :target="item.target"
              >{{ item.label }}</NuxtLink
            >
          </li>
        </ul>
      </nav>

      <div class="flex items-center justify-end gap-3 lg:flex-1">
        <AppTheme />

        <UDropdown
          v-if="auth.logged"
          :items="userItems"
          :ui="{ item: { disabled: 'cursor-text select-text' } }"
          :popper="{ placement: 'bottom-end' }"
        >
          <UAvatar
            size="sm"
            :src="$storage(auth.user.avatar)"
            :alt="auth.user.name"
            :ui="{ rounded: 'rounded-md' }"
          />

          <template #overview>
            <div class="text-left">
              <p>Signed in as</p>
              <p class="truncate font-medium text-gray-900 dark:text-white">
                {{ auth.user.email }}
              </p>
            </div>
          </template>
        </UDropdown>
        <UButton v-else label="Log In" to="/auth/login" variant="ghost" color="gray" />

        <UButton
          class="lg:hidden"
          variant="ghost"
          color="gray"
          icon="i-heroicons-bars-3"
          @click="isSideOpen = true"
        />
      </div>
    </UContainer>
  </header>

  <USlideover v-model="isSideOpen" :ui="{ width: 'max-w-xs' }" @close="">
    <UContainer
      class="flex items-center justify-between gap-3 h-16 py-2 border-b border-dashed border-gray-200/80 dark:border-gray-800/80"
    >
      <AppLogo />
      <UButton
        variant="ghost"
        color="gray"
        icon="i-heroicons-x-mark-20-solid"
        @click="isSideOpen = false"
      />
    </UContainer>
    <UContainer class="flex-1 py-4 sm:py-6">
      <UVerticalNavigation :links="navItems">
        <template #default="{ link }">
          <span class="group-hover:text-primary relative">{{ link.label }}</span>
        </template>
      </UVerticalNavigation>
    </UContainer>
  </USlideover>
</template>
