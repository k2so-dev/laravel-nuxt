<script lang="ts" setup>
const props = defineProps(["modelValue", "entity", "accept", "maxSize", "width", "height"]);
const emit = defineEmits(["update:modelValue"]);
const toast = useToast();

const file = ref<File | null>(null);
const loading = ref(false);

watch(() => props.modelValue, (newPath) => {
  if (!newPath) {
    file.value = null;
  }
}, { immediate: true });

const onFileChange = async (selectedFile: File | null) => {
  if (!selectedFile) {
    emit("update:modelValue", '');
    return;
  }

  if (selectedFile.size > props.maxSize * 1024 * 1024) {
    toast.add({
      title: "File is too large.",
      color: "error",
      icon: "i-heroicons-exclamation-circle-solid",
    });
    file.value = null;
    return;
  }

  loading.value = true;

  const formData = new FormData();
  formData.append("image", selectedFile);

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
        file.value = null;
      } else if (response._data?.ok) {
        emit("update:modelValue", response._data?.path);
      }

      loading.value = false;
    },
  });
};

const onRemoveFile = () => {
  file.value = null;
  emit("update:modelValue", '');
};
</script>

<template>
  <div class="flex gap-6">
    <UFileUpload
      v-slot="{ open }"
      v-model="file"
      :accept="accept"
      @update:model-value="onFileChange"
    >
      <div class="flex flex-wrap items-center gap-3 relative flex">
        <UAvatar
          :src="$storage(modelValue)"
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
            @click="open()"
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
            @click="onRemoveFile()"
          />
        </UTooltip>
      </div>
    </UFileUpload>
  </div>
</template>
