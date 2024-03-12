<script lang="ts" setup>
const form = ref();
const auth = useAuthStore();

const state = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const { refresh: onSubmit, status: accountPasswordStatus } = useFetch<any>("account/password", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      useToast().add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "The password was successfully updated.",
        color: "emerald",
      });

      state.current_password = "";
      state.password = "";
      state.password_confirmation = "";
    }
  }
});

const { refresh: sendResetPasswordEmail, status: resetPasswordEmailStatus } = useFetch<any>("verification-notification", {
  method: "POST",
  body: { email: auth.user.email },
  immediate: false,
  watch: false,
  onResponse({ response }) {
    if (response._data?.ok) {
      useToast().add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "A link to reset your password has been sent to your email.",
        color: "emerald",
      });
    }
  }
});
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
      <UFormGroup label="Current Password" name="current_password" required>
        <UInput v-model="state.current_password" type="password" autocomplete="off" />
      </UFormGroup>

      <UFormGroup
        label="New Password"
        name="password"
        hint="min 8 characters"
        :ui="{ hint: 'text-xs text-gray-500 dark:text-gray-400' }"
        required
      >
        <UInput v-model="state.password" type="password" autocomplete="off" />
      </UFormGroup>

      <UFormGroup label="Repeat Password" name="password_confirmation" required>
        <UInput
          v-model="state.password_confirmation"
          type="password"
          autocomplete="off"
        />
      </UFormGroup>

      <div class="pt-2">
        <UButton type="submit" label="Save" :loading="accountPasswordStatus === 'pending'" />
      </div>
    </UForm>

    <UAlert
      v-else
      icon="i-heroicons-information-circle-20-solid"
      title="Send a link to your email to reset your password."
      description="To create a password for your account, you must go through the password recovery process."
      :actions="[
        {
          label: 'Send link to Email',
          variant: 'solid',
          color: 'gray',
          loading: resetPasswordEmailStatus === 'pending',
          click: sendResetPasswordEmail,
        },
      ]"
    />
  </div>
</template>
