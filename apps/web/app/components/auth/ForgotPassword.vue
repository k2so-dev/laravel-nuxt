<script lang="ts" setup>
import type { Form } from "#ui/types";

const form = useTemplateRef<Form<any>>('form');
const toast = useToast();

const state = reactive({
  email: "",
});

const submitting = ref(false);

async function onSubmit(): Promise<void> {
  submitting.value = true;
  try {
    const res = await $http<{ ok?: boolean; message?: string }>("forgot-password", {
      method: "POST",
      body: { ...state },
    });
    if (res?.ok) {
      toast.add({
        title: "Success",
        description: res.message,
        color: "success",
      });
    }
  } catch (err: any) {
    if (err?.response?.status === 422) {
      form.value?.setErrors(err.response?._data?.errors ?? []);
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormField label="Email" name="email" required>
        <UInput
          v-model="state.email"
          class="w-full"
          placeholder="you@example.com"
          icon="i-heroicons-envelope"
          trailing
          type="email"
          autofocus
        />
      </UFormField>

      <div class="flex items-center justify-end space-x-4">
        <UButton type="submit" label="Send reset link" :loading="submitting" />
      </div>
    </UForm>

    <div class="text-sm">
      <NuxtLink class="text-sm" to="/auth/login">Back to Log In</NuxtLink>
    </div>
  </div>
</template>
