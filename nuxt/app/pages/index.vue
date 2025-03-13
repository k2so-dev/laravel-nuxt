<script setup lang="ts">
const router = useRouter();
const auth = useAuthStore();

const open = ref(false);

useSeoMeta({
  title: 'Home',
})
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-3">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">Demo</div>

        <div class="flex gap-3">
          <UModal v-model:open="open"  title="Welcome to LaravelNuxt" >
            <UButton label="Modal" color="neutral" variant="soft" />
            <template #body>
              <USkeleton class="w-full h-60" />
            </template>
            <template #footer>
              <UButton @click="open = false" label="Close" color="neutral" variant="soft" />
            </template>
          </UModal>
          <UButton label="404 page" color="neutral" @click="router.push('/404')" variant="soft" />
        </div>
      </UCard>
    </div>
    <div class="col-span-12 lg:col-span-9">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">
          User Object
        </div>
        <pre>{{ auth.user }}</pre>
      </UCard>
    </div>
  </div>
</template>
