<script setup lang="ts">
const props = defineProps({
  error: Object,
});

const handleError = () => clearError({ redirect: "/" });
</script>

<template>
  <UContainer class="py-5 flex items-center justify-center">
    <AppLogo />
  </UContainer>
  <UContainer class="flex-grow flex flex-col items-center justify-center space-y-5">
    <h1 class="text-9xl font-bold">{{ error?.statusCode }}</h1>
    <div>{{ error?.message }}</div>
    <div v-if="error?.statusCode >= 500" v-html="error?.stack"></div>
    <div>
      <UButton
        @click="handleError"
        color="gray"
        size="xl"
        variant="ghost"
        label="Go back"
      />
    </div>
  </UContainer>
</template>
