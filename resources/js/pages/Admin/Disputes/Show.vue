<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const props = defineProps<{
  dispute: any
  disputeStatuses: Record<string, string>
  customerLiabilityStatuses: Record<string, string>
}>();

const showRespond = ref(false);
const showResolve = ref(false);
const showReject = ref(false);

const respondForm = useForm({
  admin_response: '',
  status: props.dispute.status,
});

const resolveForm = useForm({
  admin_response: '',
  customer_liability_status: props.dispute.damageReport?.customer_liability_status,
});

const rejectForm = useForm({
  admin_response: '',
});

function submitRespond() {
  respondForm.post(route('admin.disputes.respond', props.dispute.id), {
    onSuccess: () => (showRespond.value = false),
  });
}

function submitResolve() {
  resolveForm.post(route('admin.disputes.resolve', props.dispute.id), {
    onSuccess: () => (showResolve.value = false),
  });
}

function submitReject() {
  rejectForm.post(route('admin.disputes.reject', props.dispute.id), {
    onSuccess: () => (showReject.value = false),
  });
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}
</script>

<template>
  <Head :title="`Dispute #${dispute.id}`" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Dispute #{{ dispute.id }}</h1>
        <Link :href="route('admin.disputes.index')">
          <Button variant="outline">Back to List</Button>
        </Link>
      </div>

      <!-- Status Bar -->
      <div
        class="rounded-md border-2 p-4"
        :style="{
          borderColor:
            dispute.status === 'submitted'
              ? '#FFA726'
              : dispute.status === 'reviewing'
              ? '#42A5F5'
              : dispute.status === 'resolved'
              ? '#66BB6A'
              : '#9575CD',
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
            <div class="text-sm">Submitted on {{ fmtDate(dispute.created_at) }}</div>
          </div>
          <div v-if="dispute.admin_id" class="text-right">
            <div class="text-sm">Handled by</div>
            <div class="font-medium">{{ dispute.admin?.name }}</div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer & Reservation Info -->
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Customer & Reservation</h3>
          <div class="space-y-2 text-sm">
            <div>
              <span class="text-gray-600">Customer:</span>
              <div class="font-medium">{{ dispute.customer?.name }}</div>
            </div>
            <div>
              <span class="text-gray-600">Email:</span>
              <div class="font-medium">{{ dispute.customer?.email }}</div>
            </div>
            <div>
              <span class="text-gray-600">Reservation:</span>
              <div class="font-medium">{{ dispute.reservation?.reservation_number }}</div>
            </div>
            <div>
              <span class="text-gray-600">Dates:</span>
              <div class="font-medium">{{ fmtDate(dispute.reservation?.start_date) }} to {{ fmtDate(dispute.reservation?.end_date) }}</div>
            </div>
          </div>
        </div>

        <!-- Damage Report Info -->
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Damage Report</h3>
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
              <span class="text-gray-600">Liability Status:</span>
              <div class="font-medium capitalize">{{ dispute.damageReport?.customer_liability_status?.replace(/_/g, ' ') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Statement -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Customer Statement</h3>
        <p class="text-sm whitespace-pre-wrap text-gray-700">{{ dispute.customer_statement }}</p>
      </div>

      <!-- Damage Description -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Damage Description</h3>
        <p class="text-sm whitespace-pre-wrap text-gray-700">{{ dispute.damageReport?.damage_description }}</p>
      </div>

      <!-- Evidence -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pickup Evidence -->
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

        <!-- Return Evidence -->
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
        <h3 class="font-semibold mb-2 text-blue-900">Admin Response</h3>
        <p class="text-sm whitespace-pre-wrap text-blue-900">{{ dispute.admin_response }}</p>
      </div>

      <!-- Respond Form -->
      <div v-if="showRespond" class="rounded-md border bg-white p-6 space-y-4">
        <h3 class="font-semibold">Respond to Dispute</h3>

        <div>
          <label class="block text-sm font-medium mb-2">Your Response *</label>
          <textarea
            v-model="respondForm.admin_response"
            rows="4"
            placeholder="Provide your response to the customer's dispute..."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
          <div v-if="respondForm.errors.admin_response" class="text-red-600 text-sm mt-1">
            {{ respondForm.errors.admin_response }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Update Status</label>
          <select
            v-model="respondForm.status"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="(label, value) in disputeStatuses" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>

        <div class="flex gap-2">
          <Button @click="submitRespond" :disabled="respondForm.processing">Send Response</Button>
          <Button @click="showRespond = false" variant="outline">Cancel</Button>
        </div>
      </div>

      <!-- Resolve Form -->
      <div v-if="showResolve" class="rounded-md border bg-green-50 border-green-300 p-6 space-y-4">
        <h3 class="font-semibold text-green-900">Resolve Dispute</h3>

        <div>
          <label class="block text-sm font-medium mb-2">Your Decision *</label>
          <textarea
            v-model="resolveForm.admin_response"
            rows="4"
            placeholder="Explain your decision on the dispute..."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
          <div v-if="resolveForm.errors.admin_response" class="text-red-600 text-sm mt-1">
            {{ resolveForm.errors.admin_response }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Final Liability Status *</label>
          <select
            v-model="resolveForm.customer_liability_status"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option v-for="(label, value) in customerLiabilityStatuses" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
          <div v-if="resolveForm.errors.customer_liability_status" class="text-red-600 text-sm mt-1">
            {{ resolveForm.errors.customer_liability_status }}
          </div>
        </div>

        <div class="flex gap-2">
          <Button @click="submitResolve" :disabled="resolveForm.processing">Resolve Dispute</Button>
          <Button @click="showResolve = false" variant="outline">Cancel</Button>
        </div>
      </div>

      <!-- Reject Form -->
      <div v-if="showReject" class="rounded-md border bg-red-50 border-red-300 p-6 space-y-4">
        <h3 class="font-semibold text-red-900">Reject Dispute</h3>

        <div>
          <label class="block text-sm font-medium mb-2">Rejection Reason *</label>
          <textarea
            v-model="rejectForm.admin_response"
            rows="4"
            placeholder="Explain why this dispute is being rejected..."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
          <div v-if="rejectForm.errors.admin_response" class="text-red-600 text-sm mt-1">
            {{ rejectForm.errors.admin_response }}
          </div>
        </div>

        <div class="flex gap-2">
          <Button @click="submitReject" variant="destructive" :disabled="rejectForm.processing">Reject Dispute</Button>
          <Button @click="showReject = false" variant="outline">Cancel</Button>
        </div>
      </div>

      <!-- Action Buttons -->
      <div v-if="!showRespond && !showResolve && !showReject" class="flex gap-2 flex-wrap">
        <Button @click="showRespond = true">Add Response</Button>
        <Button
          v-if="dispute.status !== 'resolved' && dispute.status !== 'rejected'"
          @click="showResolve = true"
          variant="outline"
        >
          Resolve Dispute
        </Button>
        <Button
          v-if="dispute.status !== 'rejected' && dispute.status !== 'resolved'"
          @click="showReject = true"
          variant="outline"
          class="text-red-600 border-red-300 hover:bg-red-50"
        >
          Reject Dispute
        </Button>
        <Link :href="route('admin.damageReports.show', dispute.damageReport?.id)">
          <Button variant="outline">View Damage Report</Button>
        </Link>
      </div>
    </main>
  </AdminLayout>
</template>
