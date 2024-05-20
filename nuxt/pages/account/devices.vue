<script lang="ts" setup>
const dayjs = useDayjs();
const auth = useAuthStore();
const loading = ref(false);
const devices = ref([]);

async function fetchData() {
  loading.value = true;

  const response = await $fetch<any>("devices");

  if (response.ok) {
    devices.value = response.devices;
  }

  loading.value = false;
}

const columns = [
  {
    key: "name",
    label: "Device",
  },
  {
    key: "last_used_at",
    label: "Last used at",
    class: "max-w-[9rem] w-[9rem] min-w-[9rem]",
  },
  {
    key: "actions",
  },
];

const items = (row: any) => [
  [
    {
      label: "Delete",
      icon: "i-heroicons-trash-20-solid",
      click: async () => {
        await $fetch<any>("devices/disconnect", {
          method: "POST",
          body: {
            hash: row.hash,
          },
          async onResponse({ response }) {
            if (response._data?.ok) {
              await fetchData();
              await auth.fetchUser();
            }
          }
        });
      },
    },
  ],
];

if (import.meta.client) {
  fetchData();
}
</script>
<template>
  <UCard :ui="{ body: { padding: 'p-0' } }">
    <ClientOnly>
      <UTable :rows="devices" :columns="columns" size="lg" :loading="loading">
        <template #name-data="{ row }">
          <div class="font-semibold">
            {{ row.name }}
            <UBadge v-if="row.is_current" label="active" color="emerald" variant="soft" size="xs" class="ms-1" />
          </div>
          <div class="font-medium text-sm">IP: {{ row.ip }}</div>
        </template>
        <template #last_used_at-data="{ row }">
          {{ dayjs(row.last_used_at).fromNow() }}
        </template>
        <template #actions-data="{ row }">
          <div class="flex justify-end">
            <UDropdown :items="items(row)">
              <UButton :disabled="row.is_current" color="gray" variant="ghost"
                icon="i-heroicons-ellipsis-horizontal-20-solid" />
            </UDropdown>
          </div>
        </template>
      </UTable>
    </ClientOnly>
  </UCard>
</template>
