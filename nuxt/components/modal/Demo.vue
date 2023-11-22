<script lang="ts" setup>
const { modal, hideModal } = useModal();
</script>
<template>
  <UModal :model-value="modal === 'demo'" @close="hideModal">
    <UCard>
      <template #header>
        <div class="text-2xl leading-tight font-black">Welcome to LaravelNuxt</div>
      </template>

      <USkeleton class="w-full h-60" />

      <template #footer>
        <UButton label="Close" @click="hideModal" color="gray" />
      </template>
    </UCard>
  </UModal>
</template>
