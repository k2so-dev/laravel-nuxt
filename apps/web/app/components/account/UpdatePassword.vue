<script lang="ts" setup>
import type { Form } from "#ui/types";

const form = useTemplateRef<Form<any>>('form');
const auth = useAuthStore();
const toast = useToast();

const state = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const submitting = ref(false);

async function onSubmit(): Promise<void> {
  submitting.value = true;
  try {
    const res = await $http<{ ok?: boolean }>("account/password", {
      method: "POST",
      body: { ...state },
    });
    if (res?.ok) {
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "The password was successfully updated.",
        color: "success",
      });

      state.current_password = "";
      state.password = "";
      state.password_confirmation = "";
    }
  } catch (err: any) {
    if (err?.response?.status === 422) {
      form.value?.setErrors(err.response?._data?.errors ?? []);
    }
  } finally {
    submitting.value = false;
  }
}

const sendingReset = ref(false);

async function sendResetPasswordEmail(): Promise<void> {
  sendingReset.value = true;
  try {
    const res = await $http<{ ok?: boolean }>("forgot-password", {
      method: "POST",
      body: { email: auth.user.email },
    });
    if (res?.ok) {
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "A link to reset your password has been sent to your email.",
        color: "success",
      });
    }
  } catch {
    // ошибки -> toast в plugin app.ts onResponseError
  } finally {
    sendingReset.value = false;
  }
}
</script>

<template>
  <div>
    <UForm
      v-if="auth.user.has_password"
      ref="form"
      :state="state"
      @submit="onSubmit"
      class="space-y-4"
    >
      <UFormField label="Current Password" name="current_password" required>
        <UInput v-model="state.current_password" class="w-full" type="password" autocomplete="off" />
      </UFormField>

      <UFormField
        label="New Password"
        name="password"
        hint="min 8 characters"
        required
      >
        <UInput v-model="state.password" class="w-full" type="password" autocomplete="off" />
      </UFormField>

      <UFormField label="Repeat Password" name="password_confirmation" required>
        <UInput
          v-model="state.password_confirmation"
          class="w-full"
          type="password"
          autocomplete="off"
        />
      </UFormField>

      <div class="pt-2">
        <UButton type="submit" label="Save" :loading="submitting" />
      </div>
    </UForm>

    <UAlert
      v-else
      variant="outline"
      color="neutral"
      icon="i-heroicons-information-circle-20-solid"
      title="Send a link to your email to reset your password."
      description="To create a password for your account, you must go through the password recovery process."
      :actions="[
        {
          label: 'Send link to Email',
          variant: 'subtle',
          color: 'neutral' as const,
          loading: sendingReset,
          onClick(event) {
            sendResetPasswordEmail();
          },
        },
      ]"
    />
  </div>
</template>
