<script lang="ts" setup>
definePageMeta({
  middleware: ["auth"],
});

const auth = useAuthStore();

const links = [
  [
    {
      label: "Account",
      icon: "i-heroicons-user",
      to: "/account/general",
    },
    {
      label: "Devices",
      icon: "i-heroicons-device-phone-mobile",
      to: "/account/devices",
      // badge: auth.user.tokens_count,
    },
  ],
];
</script>
<template>
  <NuxtLayout>
    <UHorizontalNavigation
      :links="links"
      class="border-b border-gray-200 dark:border-gray-800 mb-4"
    />
    <NuxtPage class="col-span-10 md:col-span-8" />
  </NuxtLayout>
</template>
