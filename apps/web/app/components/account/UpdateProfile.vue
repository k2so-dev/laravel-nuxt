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

const resending = ref(false);

async function sendEmailVerification(): Promise<void> {
  resending.value = true;
  try {
    const res = await $http<{ ok?: boolean; message?: string }>("verification-notification", {
      method: "POST",
      body: { email: state.email },
    });
    if (res?.ok) {
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: res.message,
        color: "success",
      });
    }
  } catch {
    // ошибки -> toast в plugin app.ts onResponseError
  } finally {
    resending.value = false;
  }
}

const submitting = ref(false);

async function onSubmit(): Promise<void> {
  submitting.value = true;
  try {
    const res = await $http<{ ok?: boolean }>("account/update", {
      method: "POST",
      body: { ...state },
    });
    if (res?.ok) {
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
          loading: resending,
          onClick(event) {
              sendEmailVerification();
          },
        },
      ]"
    />

    <div class="pt-2">
      <UButton type="submit" label="Save" :loading="submitting" />
    </div>
  </UForm>
</template>
