<script lang="ts" setup>
import { type Form } from "#ui/types";

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const form = useTemplateRef<Form<any>>('form');
const toast = useToast();

const state = reactive({
  email: route.query.email as string,
  token: route.params.token,
  password: "",
  password_confirmation: "",
});

const { refresh: onSubmit, status: resetStatus } = useHttp<any>("reset-password", {
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

      if (auth.logged) {
        await auth.fetchUser();
        await router.push("/");
      } else {
        await router.push("/auth/login");
      }
    }
  }
});

useSeoMeta({
  title: 'Reset Password',
})
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <h1 class="text-3xl font-black mb-6 leading-tight tracking-tight">Reset Password</h1>

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
            readonly=""
          />
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

        <div class="flex items-center justify-end space-x-4">
          <UButton type="submit" label="Reset password" :loading="resetStatus === 'pending'" />
        </div>
      </UForm>

      <div class="text-sm">
        <NuxtLink class="text-sm" to="/auth/login">Back to Log In</NuxtLink>
      </div>
    </div>
  </UCard>
</template>
