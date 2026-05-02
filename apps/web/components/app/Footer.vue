<script lang="ts" setup>
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  {
    label: 'Laravel',
    to: 'https://laravel.com/docs/13.x',
    target: '_blank'
  },
  {
    label: 'Nuxt',
    to: 'https://nuxt.com/docs/getting-started/introduction',
    target: '_blank'
  },
  {
    label: 'Nuxt UI',
    to: 'https://ui.nuxt.com/docs/getting-started',
    target: '_blank'
  }
]
</script>
<template>
  <UFooter>
    <template #left>
      <p class="text-muted text-sm">Copyright © {{ new Date().getFullYear() }}</p>
    </template>

    <UNavigationMenu :items="items" variant="link" />

    <template #right>
      <UButton
        icon="i-simple-icons-github"
        color="neutral"
        variant="ghost"
        to="https://github.com/k2so-dev/laravel-nuxt"
        target="_blank"
        aria-label="GitHub"
      />
    </template>
  </UFooter>
</template>
