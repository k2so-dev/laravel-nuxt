<script setup lang="ts">
const props = defineProps({
  error: Object,
});

useSeoMeta({
  title: props.error?.message,
})
</script>

<template>
  <UContainer class="py-5 flex items-center justify-center">
    <AppLogo />
  </UContainer>
  <UContainer class="flex-grow flex flex-col items-center justify-center space-y-5">
    <UError
      :clear="{
        color: 'neutral',
        size: 'xl',
        icon: 'i-lucide-arrow-left',
        class: 'rounded-full'
      }"
      :error="{
        statusCode: error?.statusCode,
        statusMessage: error?.message,
        message: error?.statusCode === 404? 'The page you are looking for does not exist.'
        : error?.statusCode >= 500? 'Something went wrong on our side. Please try again later.'
        : 'Something went wrong. Please try again later.'
      }"
    />
  </UContainer>
</template>
