<script lang="ts" setup>
const form = ref();
const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);

const state = reactive({
  email: "",
  password: "",
  remember: false,
});

async function onSubmit(event: any) {
  form.value.clear();
  loading.value = true;

  const { data, error, status } = await useFetch<any>("login", {
    method: "POST",
    body: event.data,
    watch: false,
  });

  if (error.value?.statusCode === 422) {
    form.value.setErrors(error.value.data.errors);
  }

  if (status.value === "success") {
    auth.token = data.value.token;

    await auth.fetchUser();
    await router.push("/");
  }

  loading.value = false;
}
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormGroup label="Email" name="email" required>
        <UInput
          v-model="state.email"
          placeholder="you@example.com"
          icon="i-heroicons-envelope"
          trailing
          type="email"
          autofocus
        />
      </UFormGroup>

      <UFormGroup label="Password" name="password" required>
        <UInput
          v-model="state.password"
          placeholder="********"
          type="password"
          autocomplete="current-password"
        />
      </UFormGroup>

      <UTooltip text="for 1 month" :popper="{ placement: 'right' }">
        <UCheckbox v-model="state.remember" label="Remember me" />
      </UTooltip>

      <div class="flex items-center justify-end space-x-4">
        <NuxtLink class="text-sm" to="/auth/forgot">Forgot your password?</NuxtLink>
        <UButton type="submit" label="Login" :loading="loading" />
      </div>
    </UForm>

    <div class="text-sm">
      Don't have an account yet?
      <NuxtLink class="text-sm" to="/auth/register">Sign up now</NuxtLink>
    </div>
  </div>
</template>
