<script lang="ts" setup>
const form = ref();
const loading = ref(false);
const resendEmailLoading = ref(false);
const auth = useAuthStore();

const state = reactive({
  ...{
    email: auth.user.email,
    name: auth.user.name,
    avatar: auth.user.avatar,
  },
});

async function sendEmailVerification() {
  resendEmailLoading.value = true;

  try {
    const { data } = await useFetch<any>("verification-notification", {
      method: "POST",
      body: { email: state.email },
      watch: false,
    });

    if (data.value.ok) {
      useToast().add({
        icon: "i-heroicons-check-circle-20-solid",
        title: data.value.message,
        color: "emerald",
      });
    }
  } catch (error) {}

  resendEmailLoading.value = false;
}

async function onSubmit(event: any) {
  form.value.clear();

  loading.value = true;

  const { status, error } = await useFetch<any>("account/update", {
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
      title: "Account details have been successfully updated.",
      color: "emerald",
    });

    await auth.fetchUser();

    state.name = auth.user.name;
    state.email = auth.user.email;
    state.avatar = auth.user.avatar;
  }

  loading.value = false;
}
</script>

<template>
  <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
    <UFormGroup label="" name="avatar" class="flex">
      <InputUploadAvatar
        v-model="state.avatar"
        accept=".png, .jpg, .jpeg, .webp"
        entity="avatars"
        max-size="2"
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
      color="red"
      variant="soft"
      title="Please confirm your email address."
      description="A confirmation email has been sent to your email address. Please click on the confirmation link in the email to verify your email address."
      :actions="[
        {
          label: 'Resend verification email',
          variant: 'solid',
          color: 'red',
          loading: resendEmailLoading,
          click: sendEmailVerification,
        },
      ]"
    />

    <div class="pt-2">
      <UButton type="submit" label="Save" :loading="loading" />
    </div>
  </UForm>
</template>
