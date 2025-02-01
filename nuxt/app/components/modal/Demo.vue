<script lang="ts" setup>
const modal = useModal()
</script>
<template>
  <UModal title="Welcome to LaravelNuxt">
    <template #body>
      <USkeleton class="w-full h-60" />
    </template>
    <template #footer>
      <UButton label="Close" @click="modal.close" color="neutral" variant="soft" />
    </template>
  </UModal>
</template>
