<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  damageReports: any
}>();

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}

function getStatusColor(status: string) {
  const colors: Record<string, string> = {
    'open': '#EF5350',
    'under_review': '#FFA726',
    'resolved': '#66BB6A',
    'rejected': '#9575CD',
  };
  return colors[status] || '#6B7280';
}
</script>

<template>
  <Head title="My Damage Reports" />
  <ClientLayout>
    <main class="flex-1 p-8 space-y-6">
      <h1 class="text-2xl font-semibold">My Damage Reports</h1>

      <div v-if="damageReports.data.length === 0" class="rounded-md border bg-white p-8 text-center text-gray-600">
        <p>You have no damage reports at this time.</p>
        <p class="text-sm mt-2">Damage reports are created by our staff after vehicle return inspection if any damage is found.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="report in damageReports.data"
          :key="report.id"
          class="rounded-md border bg-white hover:shadow-md transition-shadow"
        >
          <div class="p-4 border-b">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="font-semibold">Damage Report #{{ report.id }}</h3>
                <p class="text-sm text-gray-600 mt-1">
                  Reservation: {{ report.reservation?.reservation_number }} ({{ fmtDate(report.reservation?.start_date) }} to
                  {{ fmtDate(report.reservation?.end_date) }})
                </p>
              </div>
              <span
                class="inline-block px-3 py-1 rounded text-xs font-medium text-white"
                :style="{ backgroundColor: getStatusColor(report.status) }"
              >
                {{ report.status?.replace(/_/g, ' ').toUpperCase() }}
              </span>
            </div>
          </div>

          <div class="p-4 space-y-2 text-sm">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="text-gray-600">Vehicle:</span>
                <div class="font-medium">{{ report.car?.year }} {{ report.car?.make }} {{ report.car?.model }}</div>
              </div>
              <div>
                <span class="text-gray-600">Estimated Cost:</span>
                <div class="font-medium">${{ report.estimated_cost ? parseFloat(report.estimated_cost).toFixed(2) : '0.00' }}</div>
              </div>
            </div>
            <div>
              <span class="text-gray-600">Liability Status:</span>
              <div class="font-medium capitalize">{{ report.customer_liability_status?.replace(/_/g, ' ') }}</div>
            </div>
          </div>

          <div class="p-4 border-t bg-gray-50 flex gap-2">
            <Link :href="route('client.damageReports.show', report.id)">
              <Button variant="outline" size="sm">View Details</Button>
            </Link>
            <Link
              v-if="report.status !== 'rejected' && report.status !== 'resolved'"
              :href="route('client.disputes.create', report.id)"
            >
              <Button size="sm">File Dispute</Button>
            </Link>
          </div>
        </div>
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
  </ClientLayout>
</template>
