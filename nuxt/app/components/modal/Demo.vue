<script lang="ts" setup>
const modal = useModal()
</script>
<template>
  <UModal>
    <UCard>
      <template #header>
        <div class="text-2xl leading-tight font-black">Welcome to LaravelNuxt</div>
      </template>

      <USkeleton class="w-full h-60" />

      <template #footer>
        <UButton label="Close" @click="modal.close" color="gray" />
      </template>
    </UCard>
  </UModal>
</template>
