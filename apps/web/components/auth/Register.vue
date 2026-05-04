<script lang="ts" setup>
import type { Form } from "#ui/types";

const router = useRouter();
const form = useTemplateRef<Form<any>>('form');
const toast = useToast();

const state = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const { refresh: onSubmit, status: registerStatus } = useHttp<any>("register", {
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
        title: "You have been registered successfully.",
        color: "success",
        actions: [
          {
            label: "Log In now",
            to: "/auth/login",
            color: "success",
          },
        ],
      });

      router.push("/auth/login");
    }
  }
});
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormField label="Name" name="name" required>
        <UInput v-model="state.name" class="w-full" type="text" autofocus />
      </UFormField>

      <UFormField label="Email" name="email" required>
        <UInput
          v-model="state.email"
          class="w-full"
          placeholder="you@example.com"
          icon="i-heroicons-envelope"
          trailing
          type="email"
        />
      </UFormField>

      <UFormField
        label="Password"
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
        <UButton type="submit" label="Sign Up" :loading="registerStatus === 'pending'" />
      </div>
    </UForm>

    <div class="text-sm">
      Already have an account?
      <NuxtLink class="text-sm" to="/auth/login">Login now</NuxtLink>
    </div>
  </div>
</template>
