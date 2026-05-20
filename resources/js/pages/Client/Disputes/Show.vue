<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  dispute: any
  disputeStatuses: Record<string, string>
}>();

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}

function getStatusColor(status: string) {
  const colors: Record<string, string> = {
    'submitted': '#FFA726',
    'reviewing': '#42A5F5',
    'resolved': '#66BB6A',
    'rejected': '#9575CD',
  };
  return colors[status] || '#6B7280';
}
</script>

<template>
  <Head :title="`Dispute #${dispute.id}`" />
  <ClientLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Your Dispute #{{ dispute.id }}</h1>
        <Link :href="route('client.damageReports.show', dispute.damageReport?.id)">
          <Button variant="outline">Back to Report</Button>
        </Link>
      </div>

      <!-- Status Bar -->
      <div
        class="rounded-md border-2 p-4"
        :style="{
          borderColor: getStatusColor(dispute.status),
          backgroundColor:
            dispute.status === 'submitted'
              ? '#FFF3E0'
              : dispute.status === 'reviewing'
              ? '#E3F2FD'
              : dispute.status === 'resolved'
              ? '#E8F5E9'
              : '#F3E5F5',
        }"
      >
        <div class="flex items-center justify-between">
          <div>
            <div class="font-medium capitalize">{{ dispute.status?.replace(/_/g, ' ') }}</div>
            <div class="text-sm">Submitted {{ fmtDate(dispute.created_at) }}</div>
          </div>
          <div v-if="dispute.status === 'submitted'" class="text-right text-sm text-gray-600">
            <p>Awaiting review</p>
            <p class="font-medium">Within 5 business days</p>
          </div>
        </div>
      </div>

      <!-- Damage Report Info -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Associated Damage Report</h3>
        <div class="space-y-2 text-sm">
          <div>
            <span class="text-gray-600">Report ID:</span>
            <div class="font-medium">#{{ dispute.damageReport?.id }}</div>
          </div>
          <div>
            <span class="text-gray-600">Vehicle:</span>
            <div class="font-medium">{{ dispute.damageReport?.car?.year }} {{ dispute.damageReport?.car?.make }} {{ dispute.damageReport?.car?.model }}</div>
          </div>
          <div>
            <span class="text-gray-600">Estimated Cost:</span>
            <div class="font-medium">${{ parseFloat(dispute.damageReport?.estimated_cost || 0).toFixed(2) }}</div>
          </div>
          <div>
            <span class="text-gray-600">Reservation:</span>
            <div class="font-medium">{{ dispute.reservation?.reservation_number }}</div>
          </div>
        </div>
      </div>

      <!-- Damage Description -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Reported Damage</h3>
        <p class="text-sm whitespace-pre-wrap text-gray-700">{{ dispute.damageReport?.damage_description }}</p>
      </div>

      <!-- Your Statement -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Your Statement</h3>
        <p class="text-sm whitespace-pre-wrap text-gray-700">{{ dispute.customer_statement }}</p>
      </div>

      <!-- Evidence -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Pickup Inspection Evidence</h3>
          <div v-if="!dispute.damageReport?.pickupInspection?.evidence || dispute.damageReport.pickupInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in dispute.damageReport.pickupInspection.evidence"
              :key="file.id"
              :href="'/' + file.file_path"
              target="_blank"
              class="block p-2 border rounded hover:bg-gray-50 text-blue-600 text-sm"
            >
              {{ file.caption || 'View evidence' }}
            </a>
          </div>
        </div>

        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Return Inspection Evidence</h3>
          <div v-if="!dispute.damageReport?.returnInspection?.evidence || dispute.damageReport.returnInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in dispute.damageReport.returnInspection.evidence"
              :key="file.id"
              :href="'/' + file.file_path"
              target="_blank"
              class="block p-2 border rounded hover:bg-gray-50 text-blue-600 text-sm"
            >
              {{ file.caption || 'View evidence' }}
            </a>
          </div>
        </div>
      </div>

      <!-- Admin Response (if exists) -->
      <div v-if="dispute.admin_response" class="rounded-md border bg-blue-50 border-blue-300 p-4">
        <h3 class="font-semibold mb-3 text-blue-900">Our Response</h3>
        <p class="text-sm whitespace-pre-wrap text-blue-900">{{ dispute.admin_response }}</p>
        <div v-if="dispute.admin?.name" class="text-xs text-blue-700 mt-3">
          Handled by {{ dispute.admin?.name }} on {{ fmtDate(dispute.updated_at) }}
        </div>
      </div>

      <!-- No Response Yet -->
      <div v-else-if="dispute.status === 'submitted'" class="rounded-md border bg-yellow-50 border-yellow-300 p-4">
        <h3 class="font-semibold mb-2 text-yellow-900">Awaiting Response</h3>
        <p class="text-sm text-yellow-900">Our team is reviewing your dispute. We will respond within 5 business days.</p>
      </div>

      <Link :href="route('client.damageReports.show', dispute.damageReport?.id)">
        <Button variant="outline">Back to Report</Button>
      </Link>
    </main>
  </ClientLayout>
</template>
