<script lang="ts" setup>
const props = defineProps(["modelValue", "entity", "accept", "maxSize", "width", "height"]);
const emit = defineEmits(["update:modelValue"]);
const toast = useToast();

const { $storage, $http } = useNuxtApp();

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const inputRef = useTemplateRef('inputRef');
const loading = ref(false);

const onSelect = async (e: any) => {
  const file = e.target.files[0];
  e.target.value = null;

  if (file.size > props.maxSize * 1024 * 1024) {
    return toast.add({
      title: "File is too large.",
      color: "error",
      icon: "i-heroicons-exclamation-circle-solid",
    });
  }

  loading.value = true;

  const formData = new FormData();
  formData.append("image", file);

  await $http("upload", {
    method: "POST",
    body: formData,
    params: {
      entity: props.entity,
      width: props.width ?? null,
      height: props.height ?? null,
    },
    ignoreResponseError: true,
    onFetchResponse({ response }) {
      if (response.status !== 200) {
        toast.add({
          icon: 'i-heroicons-exclamation-circle-solid',
          color: "error",
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
        icon="i-heroicons-user"
        img-class="object-cover"
        class="w-20 h-20 rounded-xl"
        size="3xl"
      />

      <UTooltip
        text="Upload"
        class="absolute top-0 end-0 -m-2"
        :delay-duration="0"
        :content="{ side: 'right', align: 'center' }"
      >
        <UButton
          type="button"
          color="neutral"
          icon="i-heroicons-cloud-arrow-up"
          size="xs"
          variant="soft"
          class="rounded-full"
          :loading="loading"
          @click="inputRef.click()"
        />
      </UTooltip>
      <UTooltip
        text="Delete"
        class="absolute bottom-0 end-0 -m-2"
        :delay-duration="0"
        :content="{ side: 'right', align: 'center' }"
      >
        <UButton
          type="button"
          color="neutral"
          icon="i-heroicons-x-mark-20-solid"
          size="xs"
          variant="soft"
          class="rounded-full"
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
