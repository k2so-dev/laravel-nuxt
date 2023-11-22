<script setup lang="ts">
const { user } = useAuth();
const { showModal } = useModal();
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <div class="col-span-3">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">Demo</div>

        <div class="flex gap-3">
          <UButton label="Modal" @click="showModal('demo')" color="gray" />
          <UButton label="404 page" to="404" color="gray" />
        </div>
      </UCard>
    </div>
    <div class="col-span-9">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">
          User Object
        </div>
        <pre>{{ user }}</pre>
      </UCard>
    </div>
  </div>
</template>
