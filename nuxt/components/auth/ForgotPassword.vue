<script lang="ts" setup>
const form = ref();

const state = reactive({
  email: "",
});

const { refresh: onSubmit, status: forgotStatus } = useFetch<any>("forgot-password", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      useToast().add({
        title: "Success",
        description: response._data.message,
        color: "emerald",
      });
    }
  }
});
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormGroup label="Email" name="email" required>
        <UInput
          v-model="state.email"
          placeholder="you@example.com"
          icon="i-heroicons-envelope"
          trailing
          type="email"
          autofocus
        />
      </UFormGroup>

      <div class="flex items-center justify-end space-x-4">
        <UButton type="submit" label="Send reset link" :loading="forgotStatus === 'pending'" />
      </div>
    </UForm>

    <div class="text-sm">
      <NuxtLink class="text-sm" to="/auth/login">Back to Log In</NuxtLink>
    </div>
  </div>
</template>
