<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';

const props = defineProps<{
  damageReports: any
  statuses: Record<string, any>
  filters: { search: string; status: string }
}>();

const search = ref(props.filters.search);
const selectedStatus = ref(props.filters.status || 'all');

const statusOptions = computed(() => [
  { value: 'all', label: 'All Statuses' },
  ...Object.entries(props.statuses).map(([value, data]) => ({
    value,
    label: `${data.label} (${data.count})`,
  })),
]);

function handleSearch() {
  router.get(
    route('admin.damageReports.index'),
    { search: search.value, status: selectedStatus.value || 'all' },
    { preserveScroll: true }
  );
}

function getStatusColor(status: string) {
  const colors: Record<string, string> = {
    open: '#EF5350',
    under_review: '#FFA726',
    resolved: '#66BB6A',
    rejected: '#9575CD',
  };
  return colors[status] || '#6B7280';
}

function getStatusBgColor(status: string) {
  const colors: Record<string, string> = {
    open: '#FFEBEE',
    under_review: '#FFF3E0',
    resolved: '#E8F5E9',
    rejected: '#F3E5F5',
  };
  return colors[status] || '#F3F4F6';
}
</script>

<template>
  <Head title="Damage Reports" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Damage Reports</h1>
      </div>

      <!-- Search and Filter -->
      <div class="rounded-md border bg-white p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Search</label>
            <input
              v-model="search"
              type="text"
              placeholder="Search by description, reservation, or customer..."
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="handleSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select
              v-model="selectedStatus"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="handleSearch"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
        <Button @click="handleSearch" class="w-full md:w-auto">Search</Button>
      </div>

      <!-- Damage Reports Table -->
      <div class="rounded-md border bg-white overflow-hidden">
        <div v-if="damageReports.data.length === 0" class="p-8 text-center text-gray-600">
          No damage reports found.
        </div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-700">ID</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Customer</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Vehicle</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Reservation</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Cost</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Liability</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Status</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Created</th>
              <th class="px-4 py-3 text-left font-medium text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="report in damageReports.data" :key="report.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-3 font-medium">#{{ report.id }}</td>
              <td class="px-4 py-3">{{ report.reservation?.user?.name }}</td>
              <td class="px-4 py-3">{{ report.car?.year }} {{ report.car?.make }} {{ report.car?.model }}</td>
              <td class="px-4 py-3">{{ report.reservation?.reservation_number }}</td>
              <td class="px-4 py-3">{{ report.estimated_cost ? `$${parseFloat(report.estimated_cost).toFixed(2)}` : '—' }}</td>
              <td class="px-4 py-3 text-xs">
                <span class="inline-block px-2 py-1 rounded font-medium capitalize">
                  {{ report.customer_liability_status?.replace(/_/g, ' ') }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-block px-2 py-1 rounded text-xs font-medium text-white"
                  :style="{ backgroundColor: getStatusColor(report.status) }"
                >
                  {{ report.status?.replace(/_/g, ' ').toUpperCase() }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs">{{ new Date(report.created_at).toLocaleDateString() }}</td>
              <td class="px-4 py-3">
                <Link :href="route('admin.damageReports.show', report.id)">
                  <Button variant="outline" size="sm">View</Button>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Showing {{ damageReports.from }} to {{ damageReports.to }} of {{ damageReports.total }} results
        </div>
        <div class="space-x-2">
          <Link
            v-if="damageReports.prev_page_url"
            :href="damageReports.prev_page_url"
            class="inline-block px-4 py-2 border rounded-md hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link
            v-if="damageReports.next_page_url"
            :href="damageReports.next_page_url"
            class="inline-block px-4 py-2 border rounded-md hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
    </main>
  </AdminLayout>
</template>
