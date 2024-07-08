<script lang="ts" setup></script>
<template>
  <footer
    class="bg-background/75 border-t border-dashed border-gray-200/80 dark:border-gray-800/80"
  >
    <UContainer class="flex justify-between py-4 text-sm">
      <span> Laravel Nuxt Boilerplate {{ new Date().getFullYear() }} </span>
      <NuxtLink to="https://github.com/k2so-dev/laravel-nuxt"> GitHub </NuxtLink>
    </UContainer>
  </footer>
</template>
