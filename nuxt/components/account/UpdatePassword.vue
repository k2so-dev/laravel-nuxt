<script lang="ts" setup>
const form = ref();
const loading = ref(false);
const auth = useAuthStore();
const sendResetPasswordEmailLoading = ref(false);

const state = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

async function onSubmit(event: any) {
  form.value.clear();

  loading.value = true;

  const { status, error } = await useFetch<any>("account/password", {
    method: "POST",
    body: event.data,
    watch: false,
  });

  if (error.value?.statusCode === 422) {
    form.value.setErrors(error.value.data.errors);
  }

  if (status.value === "success") {
    useToast().add({
      icon: "i-heroicons-check-circle-20-solid",
      title: "The password was successfully updated.",
      color: "emerald",
    });

    state.current_password = "";
    state.password = "";
    state.password_confirmation = "";
  }

  loading.value = false;
}

async function sendResetPasswordEmail() {
  sendResetPasswordEmailLoading.value = true;

  const { status } = await useFetch<any>("forgot-password", {
    method: "POST",
    body: {
      email: auth.user.email,
    },
    watch: false,
  });

  if (status.value === "success") {
    useToast().add({
      icon: "i-heroicons-check-circle-20-solid",
      title: "A link to reset your password has been sent to your email.",
      color: "emerald",
    });
  }

  sendResetPasswordEmailLoading.value = false;
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
        <UButton type="submit" label="Save" :loading="loading" />
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
          loading: sendResetPasswordEmailLoading,
          click: sendResetPasswordEmail,
        },
      ]"
    />
  </div>
</template>
