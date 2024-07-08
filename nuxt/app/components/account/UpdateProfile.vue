<script lang="ts" setup>
const form = ref();
const auth = useAuthStore();

const state = reactive({
  ...{
    email: auth.user.email,
    name: auth.user.name,
    avatar: auth.user.avatar,
  },
});

const { refresh: sendEmailVerification, status: resendEmailStatus } = useFetch<any>("verification-notification", {
  method: "POST",
  body: { email: state.email },
  immediate: false,
  watch: false,
  onResponse({ response }) {
    if (response._data?.ok) {
      useToast().add({
        icon: "i-heroicons-check-circle-20-solid",
        title: response._data.message,
        color: "emerald",
      });
    }
  }
});

const { refresh: onSubmit, status: accountUpdateStatus } = useFetch<any>("account/update", {
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
        title: "Account details have been successfully updated.",
        color: "emerald",
      });

      await auth.fetchUser();

      state.name = auth.user.name;
      state.email = auth.user.email;
      state.avatar = auth.user.avatar;
    }
  }
});
</script>

<template>
  <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
    <UFormGroup label="" name="avatar" class="flex">
      <InputUploadImage
        v-model="state.avatar"
        accept=".png, .jpg, .jpeg, .webp"
        entity="avatars"
        max-size="2"
        :width="300"
        :height="300"
      />
    </UFormGroup>

    <UFormGroup label="Name" name="name" required>
      <UInput v-model="state.name" type="text" />
    </UFormGroup>

    <UFormGroup label="Email" name="email" required>
      <UInput
        v-model="state.email"
        placeholder="you@example.com"
        icon="i-heroicons-envelope"
        trailing
        type="email"
      />
    </UFormGroup>

    <UAlert
      v-if="auth.user.must_verify_email"
      icon="i-heroicons-information-circle-20-solid"
      title="Please confirm your email address."
      description="A confirmation email has been sent to your email address. Please click on the confirmation link in the email to verify your email address."
      :actions="[
        {
          label: 'Resend verification email',
          variant: 'solid',
          color: 'gray',
          loading: resendEmailStatus === 'pending',
          click: sendEmailVerification,
        },
      ]"
    />

    <div class="pt-2">
      <UButton type="submit" label="Save" :loading="accountUpdateStatus === 'pending'" />
    </div>
  </UForm>
</template>
