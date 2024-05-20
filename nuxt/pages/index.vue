<script setup lang="ts">
import { ModalDemo } from '#components'

const modal = useModal();
const router = useRouter();
const auth = useAuthStore();

function openDemoModal() {
  modal.open(ModalDemo)
}
</script>

<template>
  <div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-3">
      <UCard>
        <div class="font-bold text-lg leading-tight tracking-tighter mb-4">Demo</div>

        <div class="flex gap-3">
          <UButton label="Modal" @click="openDemoModal" color="gray" />
          <UButton label="404 page" color="gray" @click="router.push('/404')" />
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
