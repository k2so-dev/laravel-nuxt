<script lang="ts" setup>
const router = useRouter();
const form = ref();
const loading = ref(false);

const state = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

async function onSubmit(event: any) {
  form.value.clear();

  loading.value = true;

  const { status, error } = await useFetch<any>("register", {
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
      title: "You have been registered successfully.",
      color: "emerald",
      actions: [
        {
          label: "Log In now",
          to: "/auth/login",
          color: "emerald",
        },
      ],
    });

    router.push("/auth/login");
  }

  loading.value = false;
}
</script>

<template>
  <div class="space-y-4">
    <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
      <UFormGroup label="Name" name="name" required>
        <UInput v-model="state.name" type="text" autofocus />
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

      <UFormGroup
        label="Password"
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
        <UButton type="submit" label="Sign Up" :loading="loading" />
      </div>
    </UForm>

    <div class="text-sm">
      Already have an account?
      <NuxtLink class="text-sm" to="/auth/login">Login now</NuxtLink>
    </div>
  </div>
</template>
