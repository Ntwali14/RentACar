<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const props = defineProps<{
  damageReport: any
}>();

const activeTab = ref('details');

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
  <Head :title="`Damage Report #${damageReport.id}`" />
  <ClientLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Damage Report #{{ damageReport.id }}</h1>
        <Link :href="route('client.damageReports.index')">
          <Button variant="outline">Back to Reports</Button>
        </Link>
      </div>

      <!-- Status Bar -->
      <div
        class="rounded-md border-2 p-4"
        :style="{
          borderColor: getStatusColor(damageReport.status),
          backgroundColor:
            damageReport.status === 'open'
              ? '#FFEBEE'
              : damageReport.status === 'under_review'
              ? '#FFF3E0'
              : damageReport.status === 'resolved'
              ? '#E8F5E9'
              : '#F3E5F5',
        }"
      >
        <div class="flex items-center justify-between">
          <div>
            <div class="font-medium capitalize">{{ damageReport.status?.replace(/_/g, ' ') }}</div>
            <div class="text-sm">Liability: {{ damageReport.customer_liability_status?.replace(/_/g, ' ') }}</div>
          </div>
          <div class="text-right">
            <div class="text-sm">Created</div>
            <div class="font-medium">{{ fmtDate(damageReport.created_at) }}</div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b">
        <div class="flex gap-4">
          <button
            @click="activeTab = 'details'"
            :class="[
              'px-4 py-2 font-medium border-b-2',
              activeTab === 'details'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900',
            ]"
          >
            Details
          </button>
          <button
            @click="activeTab = 'evidence'"
            :class="[
              'px-4 py-2 font-medium border-b-2',
              activeTab === 'evidence'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900',
            ]"
          >
            Evidence
          </button>
          <button
            v-if="damageReport.disputes && damageReport.disputes.length > 0"
            @click="activeTab = 'disputes'"
            :class="[
              'px-4 py-2 font-medium border-b-2',
              activeTab === 'disputes'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900',
            ]"
          >
            Your Dispute
          </button>
        </div>
      </div>

      <!-- Details Tab -->
      <div v-if="activeTab === 'details'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-md border bg-white p-4">
            <h3 class="font-semibold mb-3">Reservation Information</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="text-gray-600">Reservation #:</span>
                <div class="font-medium">{{ damageReport.reservation?.reservation_number }}</div>
              </div>
              <div>
                <span class="text-gray-600">Dates:</span>
                <div class="font-medium">{{ fmtDate(damageReport.reservation?.start_date) }} to {{ fmtDate(damageReport.reservation?.end_date) }}</div>
              </div>
              <div>
                <span class="text-gray-600">Vehicle:</span>
                <div class="font-medium">{{ damageReport.car?.year }} {{ damageReport.car?.make }} {{ damageReport.car?.model }}</div>
              </div>
            </div>
          </div>

          <div class="rounded-md border bg-white p-4">
            <h3 class="font-semibold mb-3">Report Information</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="text-gray-600">Estimated Cost:</span>
                <div class="font-medium">${{ damageReport.estimated_cost ? parseFloat(damageReport.estimated_cost).toFixed(2) : '0.00' }}</div>
              </div>
              <div>
                <span class="text-gray-600">Liability Status:</span>
                <div class="font-medium capitalize">{{ damageReport.customer_liability_status?.replace(/_/g, ' ') }}</div>
              </div>
              <div>
                <span class="text-gray-600">Report Status:</span>
                <div class="font-medium capitalize">{{ damageReport.status?.replace(/_/g, ' ') }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Damage Description</h3>
          <p class="text-sm whitespace-pre-wrap text-gray-700">{{ damageReport.damage_description }}</p>
        </div>

        <div class="flex gap-2">
          <Link
            v-if="damageReport.status !== 'rejected' && damageReport.status !== 'resolved'"
            :href="route('client.disputes.create', damageReport.id)"
          >
            <Button>File a Dispute</Button>
          </Link>
          <Link :href="route('client.damageReports.index')">
            <Button variant="outline">Back to Reports</Button>
          </Link>
        </div>
      </div>

      <!-- Evidence Tab -->
      <div v-if="activeTab === 'evidence'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-4">Pickup Inspection Evidence</h3>
          <div v-if="!damageReport.pickupInspection?.evidence || damageReport.pickupInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence uploaded
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in damageReport.pickupInspection.evidence"
              :key="file.id"
              :href="'/' + file.file_path"
              target="_blank"
              class="block p-3 border rounded hover:bg-gray-50"
            >
              <div class="font-medium text-blue-600">{{ file.caption || 'View Evidence' }}</div>
              <div class="text-xs text-gray-600">{{ file.file_type }}</div>
            </a>
          </div>
        </div>

        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-4">Return Inspection Evidence</h3>
          <div v-if="!damageReport.returnInspection?.evidence || damageReport.returnInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence uploaded
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in damageReport.returnInspection.evidence"
              :key="file.id"
              :href="'/' + file.file_path"
              target="_blank"
              class="block p-3 border rounded hover:bg-gray-50"
            >
              <div class="font-medium text-blue-600">{{ file.caption || 'View Evidence' }}</div>
              <div class="text-xs text-gray-600">{{ file.file_type }}</div>
            </a>
          </div>
        </div>
      </div>

      <!-- Disputes Tab -->
      <div v-if="activeTab === 'disputes' && damageReport.disputes && damageReport.disputes.length > 0">
        <Link
          v-for="dispute in damageReport.disputes"
          :key="dispute.id"
          :href="route('client.disputes.show', dispute.id)"
          class="block rounded-md border bg-white p-4 hover:shadow-md"
        >
          <div class="flex items-start justify-between">
            <div>
              <div class="font-medium">Your Dispute #{{ dispute.id }}</div>
              <div class="text-sm text-gray-600 mt-1">Status: {{ dispute.status?.replace(/_/g, ' ') }}</div>
              <div class="text-sm text-gray-600">Submitted {{ new Date(dispute.created_at).toLocaleDateString() }}</div>
            </div>
            <Button variant="outline" size="sm">View</Button>
          </div>
        </Link>
      </div>
    </main>
  </ClientLayout>
</template>
