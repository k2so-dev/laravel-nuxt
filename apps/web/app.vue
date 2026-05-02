<script lang="ts" setup>
</script>

<template>
  <UApp>
    <AppHeader />

    <UContainer as="main" class="flex-grow py-4 sm:py-7 flex flex-col">
      <NuxtPage />
    </UContainer>

    <AppFooter />

    <NuxtLoadingIndicator class="!opacity-100" :throttle="0" />
  </UApp>
</template>
