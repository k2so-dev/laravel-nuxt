<script lang="ts" setup>
const dayjs = useDayjs();
const auth = useAuthStore();
const { $http } = useNuxtApp();

const { data, status, refresh } = useHttp<any>("devices");
const loading = computed(() => status.value === 'pending');

const columns = [
  {
    accessorKey: "name",
    header: "Device",
  },
  {
    accessorKey: "last_used_at",
    header: "Last used at",
    class: "max-w-[9rem] w-[9rem] min-w-[9rem]",
  },
  {
    id: "actions",
  },
];

const items = (row: any) => [
  [
    {
      label: "Delete",
      icon: "i-heroicons-trash-20-solid",
      color: 'error' as const,
      onSelect: async () => {
        await $http("devices/disconnect", {
          method: "POST",
          body: {
            hash: row.hash,
          },
          async onFetchResponse({ response }) {
            if (response._data?.ok) {
              await refresh();
              await auth.fetchUser();
            }
          }
        });
      },
    },
  ],
];

useSeoMeta({
  title: 'Devices',
})
</script>
<template>
  <UCard :ui="{ body: 'sm:p-0 p-0 font-medium' }">
    <UTable :data="data?.devices" :columns="columns" size="lg" :loading="loading" loading-color="primary" loading-animation="carousel">
      <template #name-cell="{ row }">
        <div class="font-semibold">
          {{ row.original.name }}
          <UBadge v-if="row.original.is_current as boolean" label="active" color="primary" variant="soft" size="md" class="ms-1" />
        </div>
        <div class="font-medium text-sm">IP: {{ row.original.ip }}</div>
      </template>
      <template #last_used_at-cell="{ row }">
        {{ dayjs(row.original.last_used_at as string).fromNow() }}
      </template>
      <template #actions-cell="{ row }">
        <div class="flex justify-end">
          <UDropdownMenu :items="items(row.original)" :content="{ side: 'bottom', align: 'end' }">
            <UButton :disabled="row.original.is_current as boolean" color="neutral" variant="ghost"
              icon="i-heroicons-ellipsis-horizontal-20-solid" />
          </UDropdownMenu>
        </div>
      </template>
    </UTable>
  </UCard>
</template>
