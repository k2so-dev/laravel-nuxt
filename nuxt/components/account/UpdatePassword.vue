<script lang="ts" setup>
const form = ref();
const loading = ref(false);

const state = reactive({
  current_password: "",
  password: "",
  password_confirmation: "",
});

async function onSubmit(event: any) {
  form.value.clear();

  loading.value = true;

  const { status, error } = await useFetch<any>("account/password", {
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
      title: "The password was successfully updated.",
      color: "emerald",
    });

    state.current_password = "";
    state.password = "";
    state.password_confirmation = "";
  }

  loading.value = false;
}
</script>

<template>
  <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
    <UFormGroup label="Current Password" name="current_password" required>
      <UInput
        v-model="state.current_password"
        placeholder="********"
        type="password"
        autocomplete="off"
      />
    </UFormGroup>

    <UFormGroup
      label="New Password"
      name="password"
      hint="min 8 characters"
      :ui="{ hint: 'text-xs text-gray-500 dark:text-gray-400' }"
      required
    >
      <UInput
        v-model="state.password"
        placeholder="********"
        type="password"
        autocomplete="off"
      />
    </UFormGroup>

    <UFormGroup label="Repeat Password" name="password_confirmation" required>
      <UInput
        v-model="state.password_confirmation"
        placeholder="********"
        type="password"
        autocomplete="off"
      />
    </UFormGroup>

    <div class="pt-2">
      <UButton type="submit" label="Save" :loading="loading" />
    </div>
  </UForm>
</template>
