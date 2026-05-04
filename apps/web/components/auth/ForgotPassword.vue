<script lang="ts" setup>
import type { Form } from "#ui/types";

const form = useTemplateRef<Form<any>>('form');
const toast = useToast();

const state = reactive({
  email: "",
});

const { refresh: onSubmit, status: forgotStatus } = useHttp<any>("forgot-password", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onFetchResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      toast.add({
        title: "Success",
        description: response._data.message,
        color: "success",
      });
    }
  }
});
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
        <UButton type="submit" label="Send reset link" :loading="forgotStatus === 'pending'" />
      </div>
    </UForm>

    <div class="text-sm">
      <NuxtLink class="text-sm" to="/auth/login">Back to Log In</NuxtLink>
    </div>
  </div>
</template>
