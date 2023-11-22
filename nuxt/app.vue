<script lang="ts" setup></script>

<template>
  <AppHeader />

  <UContainer as="main" class="flex-grow py-4 sm:py-7 flex flex-col">
    <NuxtPage />
  </UContainer>

  <AppFooter />

  <ModalDemo />

  <NuxtLoadingIndicator class="!opacity-100" :throttle="0" />

  <UNotifications>
    <template #title="{ title }">
      <span v-html="title" />
    </template>
    <template #description="{ description }">
      <span v-html="description" />
    </template>
  </UNotifications>
</template>
