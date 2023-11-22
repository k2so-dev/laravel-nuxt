<script lang="ts" setup>
const form = ref();
const router = useRouter();
const { token } = useAuth();
const loading = ref(false);
const alertVisible = ref(false);
const alertMessage = ref("");
const alertActions = ref([] as any[]);

const state = reactive({
  email: "test@test.com",
  password: "qweasd123",
  remember: false,
});

async function onSubmit(event: any) {
  form.value.clear();
  loading.value = true;

  const { data, error } = await useFetch<any>("login", {
    method: "POST",
    body: { ...event.data },
  });

  loading.value = false;

  if (error.value?.data) return form.value.setErrors(error.value.data);

  if (data.value.ok) {
    token.value = data.value.token;
    await router.push("/");
  } else if (data.value.action && data.value.action === "verify_email") {
    const { execute, data: verificationData, error } = useFetch<any>(
      "verification-notification",
      {
        immediate: false,
        method: "POST",
        body: { email: state.email },
      }
    );

    const resendEmailLoading = ref(false);

    alertVisible.value = true;
    alertMessage.value = data.value.message;
    alertActions.value = [
      {
        label: "Resend verification email",
        color: "emerald",
        variant: "outline",
        loading: resendEmailLoading,
        click: async () => {
          resendEmailLoading.value = true;

          try {
            await execute();

            if (verificationData.value.ok) {
              useToast().add({
                icon: "i-heroicons-check-circle-20-solid",
                title: verificationData.value.message,
                color: "emerald",
              });
            }
          } catch (e) {}

          resendEmailLoading.value = false;
        },
      },
    ];
  }
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

    <UNotification
      v-if="alertVisible"
      :close-button="{ disabled: true }"
      icon="i-heroicons-information-circle-20-solid"
      color="red"
      :description="alertMessage"
      :id="1"
      :timeout="0"
      :actions="alertActions"
      title="Before log-in"
    />

    <div class="text-sm">
      Don't have an account yet?
      <NuxtLink class="text-sm" to="/auth/register">Sign up now</NuxtLink>
    </div>
  </div>
</template>
