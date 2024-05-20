<script lang="ts" setup>
const props = defineProps(["modelValue", "entity", "accept", "maxSize", "width", "height"]);
const emit = defineEmits(["update:modelValue"]);

const { $storage } = useNuxtApp();

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const inputRef = ref();
const loading = ref(false);

const onSelect = async (e: any) => {
  const file = e.target.files[0];
  e.target.value = null;

  if (file.size > props.maxSize * 1024 * 1024) {
    return useToast().add({
      title: "File is too large.",
      color: "red",
      icon: "i-heroicons-exclamation-circle-solid",
    });
  }

  loading.value = true;

  const formData = new FormData();
  formData.append("image", file);

  await $fetch<any>("upload", {
    method: "POST",
    body: formData,
    params: {
      entity: props.entity,
      width: props.width ?? null,
      height: props.height ?? null,
    },
    ignoreResponseError: true,
    onResponse({ response }) {
      if (response.status !== 200) {
        useToast().add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: 'red',
          title: response._data?.message ?? response.statusText ?? 'Something went wrong',
        });
      } else if (response._data?.ok) {
        value.value = response._data?.path;
      }

      loading.value = false;
    },
  });
};
</script>

<template>
  <div class="flex gap-6">
    <div class="relative flex">
      <UAvatar
        :src="$storage(value)"
        size="3xl"
        img-class="object-cover"
        :ui="{ rounded: 'rounded-lg' }"
      />

      <UTooltip
        text="Upload avatar"
        class="absolute top-0 end-0 -m-2"
        :popper="{ placement: 'right' }"
      >
        <UButton
          type="button"
          color="gray"
          icon="i-heroicons-cloud-arrow-up"
          size="2xs"
          :ui="{ rounded: 'rounded-full' }"
          :loading="loading"
          @click="inputRef.click()"
        />
      </UTooltip>
      <UTooltip
        text="Delete avatar"
        class="absolute bottom-0 end-0 -m-2"
        :popper="{ placement: 'right' }"
      >
        <UButton
          type="button"
          color="gray"
          icon="i-heroicons-x-mark-20-solid"
          size="2xs"
          :ui="{ rounded: 'rounded-full' }"
          :disabled="loading"
          @click="value = ''"
        />
      </UTooltip>
      <input
        ref="inputRef"
        type="file"
        class="hidden"
        :accept="accept"
        @change="onSelect"
      />
    </div>
    <div class="text-sm opacity-80">
      <div>Max upload size: {{ maxSize }}Mb</div>
      <div>Accepted formats: {{ accept }}</div>
    </div>
  </div>
</template>
