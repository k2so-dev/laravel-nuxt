<script lang="ts" setup>
definePageMeta({
  middleware: ['guest'],
});

useSeoMeta({
  title: 'Sign Up',
})
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <h1 class="text-3xl font-black mb-6 leading-tight tracking-tight">Sign Up</h1>
    <AuthRegister />
  </UCard>
</template>
