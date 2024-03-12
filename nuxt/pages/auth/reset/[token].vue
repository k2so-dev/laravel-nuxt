<script lang="ts" setup>
const router = useRouter();
const route = useRoute();
const auth = useAuthStore();
const form = ref();

const state = reactive({
  email: route.query.email as string,
  token: route.params.token,
  password: "",
  password_confirmation: "",
});

const { refresh: onSubmit, status: resetStatus } = useFetch<any>("reset-password", {
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

      if (auth.logged) {
        await auth.fetchUser();
        await router.push("/");
      } else {
        await router.push("/auth/login");
      }
    }
  }
});
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <h1 class="text-3xl font-black mb-6 leading-tight tracking-tight">Reset Password</h1>

    <div class="space-y-4">
      <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
        <UFormGroup label="Email" name="email" required>
          <UInput
            v-model="state.email"
            placeholder="you@example.com"
            icon="i-heroicons-envelope"
            trailing
            type="email"
            readonly=""
          />
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
