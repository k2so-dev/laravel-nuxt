<script lang="ts" setup>
import type { Form } from "#ui/types";

const form = useTemplateRef<Form<any>>('form');
const auth = useAuthStore();
const toast = useToast();

const state = reactive({
  ...{
    email: auth.user.email,
    name: auth.user.name,
    avatar: auth.user.avatar,
  },
});

const { refresh: sendEmailVerification, status: resendEmailStatus } = useHttp<any>("verification-notification", {
  method: "POST",
  body: { email: state.email },
  immediate: false,
  watch: false,
  onFetchResponse({ response }) {
    if (response._data?.ok) {
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: response._data.message,
        color: "success",
      });
    }
  }
});

const { refresh: onSubmit, status: accountUpdateStatus } = useHttp<any>("account/update", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onFetchResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "Account details have been successfully updated.",
        color: "success",
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
    <UFormField label="" name="avatar" class="flex">
      <InputUploadImage
        v-model="state.avatar"
        accept=".png, .jpg, .jpeg, .webp"
        entity="avatars"
        max-size="5"
        :width="300"
        :height="300"
      />
    </UFormField>

    <UFormField label="Name" name="name" required>
      <UInput v-model="state.name" type="text" class="w-full" />
    </UFormField>

    <UFormField label="Email" name="email" required>
      <UInput
        v-model="state.email"
        placeholder="you@example.com"
        icon="i-heroicons-envelope"
        trailing
        type="email"
        class="w-full"
      />
    </UFormField>

    <UAlert
      v-if="auth.user.must_verify_email"
      variant="outline"
      color="neutral"
      icon="i-heroicons-information-circle-20-solid"
      title="Please confirm your email address."
      description="A confirmation email has been sent to your email address. Please click on the confirmation link in the email to verify your email address."
      :actions="[
        {
          label: 'Resend verification email',
          variant: 'subtle',
          color: 'neutral' as const,
          loading: resendEmailStatus === 'pending',
          onClick(event) {
              sendEmailVerification();
          },
        },
      ]"
    />

    <div class="pt-2">
      <UButton type="submit" label="Save" :loading="accountUpdateStatus === 'pending'" />
    </div>
  </UForm>
</template>
