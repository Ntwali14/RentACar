<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const props = defineProps<{
  damageReport: any
  customerLiabilityStatuses: Record<string, string>
  damageReportStatuses: Record<string, string>
}>();

const showEditForm = ref(false);
const showResolveForm = ref(false);
const showRejectForm = ref(false);
const activeTab = ref('details');

const editForm = useForm({
  damage_description: props.damageReport.damage_description,
  estimated_cost: props.damageReport.estimated_cost,
  customer_liability_status: props.damageReport.customer_liability_status,
  admin_decision: props.damageReport.admin_decision,
  status: props.damageReport.status,
});

const resolveForm = useForm({
  customer_liability_status: props.damageReport.customer_liability_status,
});

const rejectForm = useForm({
  reason: '',
});

function submitEdit() {
  editForm.put(route('admin.damageReports.update', props.damageReport.id), {
    onSuccess: () => (showEditForm.value = false),
  });
}

function submitResolve() {
  resolveForm.post(route('admin.damageReports.resolve', props.damageReport.id), {
    onSuccess: () => (showResolveForm.value = false),
  });
}

function submitReject() {
  rejectForm.post(route('admin.damageReports.reject', props.damageReport.id), {
    onSuccess: () => (showRejectForm.value = false),
  });
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}
</script>

<template>
  <Head :title="`Damage Report #${damageReport.id}`" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Damage Report #{{ damageReport.id }}</h1>
        <Link :href="route('admin.damageReports.index')">
          <Button variant="outline">Back to List</Button>
        </Link>
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
            Disputes ({{ damageReport.disputes.length }})
          </button>
        </div>
      </div>

      <!-- Details Tab -->
      <div v-if="activeTab === 'details'" class="space-y-6">
        <!-- Status Bar -->
        <div
          class="rounded-md border-2 p-4"
          :style="{
            borderColor:
              damageReport.status === 'open'
                ? '#EF5350'
                : damageReport.status === 'under_review'
                ? '#FFA726'
                : damageReport.status === 'resolved'
                ? '#66BB6A'
                : '#9575CD',
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
              <div class="text-sm text-gray-600">Liability: {{ damageReport.customer_liability_status?.replace(/_/g, ' ') }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-600">Created</div>
              <div class="font-medium">{{ fmtDate(damageReport.created_at) }}</div>
            </div>
          </div>
        </div>

        <!-- Reservation Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-md border bg-white p-4">
            <h3 class="font-semibold mb-4">Reservation Information</h3>
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-gray-600">Reservation #:</span>
                <span class="block font-medium">{{ damageReport.reservation?.reservation_number }}</span>
              </div>
              <div>
                <span class="text-gray-600">Customer:</span>
                <span class="block font-medium">{{ damageReport.reservation?.user?.name }}</span>
              </div>
              <div>
                <span class="text-gray-600">Vehicle:</span>
                <span class="block font-medium"
                  >{{ damageReport.car?.year }} {{ damageReport.car?.make }} {{ damageReport.car?.model }}</span
                >
              </div>
              <div>
                <span class="text-gray-600">Dates:</span>
                <span class="block font-medium"
                  >{{ fmtDate(damageReport.reservation?.start_date) }} to
                  {{ fmtDate(damageReport.reservation?.end_date) }}</span
                >
              </div>
            </div>
          </div>

          <div class="rounded-md border bg-white p-4">
            <h3 class="font-semibold mb-4">Inspection Information</h3>
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-gray-600">Pickup Inspection:</span>
                <span class="block font-medium">{{ fmtDate(damageReport.pickupInspection?.inspection_date) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Return Inspection:</span>
                <span class="block font-medium">{{ fmtDate(damageReport.returnInspection?.inspection_date) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Reported by:</span>
                <span class="block font-medium">{{ damageReport.reportedBy?.name || 'System' }}</span>
              </div>
              <div>
                <span class="text-gray-600">Estimated Cost:</span>
                <span class="block font-medium"
                  >${{ damageReport.estimated_cost ? parseFloat(damageReport.estimated_cost).toFixed(2) : '0.00' }}</span
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Damage Description -->
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Damage Description</h3>
          <p class="text-sm whitespace-pre-wrap text-gray-700">{{ damageReport.damage_description }}</p>
        </div>

        <!-- Admin Decision -->
        <div v-if="damageReport.admin_decision" class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Admin Decision/Notes</h3>
          <p class="text-sm whitespace-pre-wrap text-gray-700">{{ damageReport.admin_decision }}</p>
        </div>

        <!-- Edit Form -->
        <div v-if="showEditForm" class="rounded-md border bg-white p-6 space-y-4">
          <h3 class="font-semibold">Edit Damage Report</h3>

          <div>
            <label class="block text-sm font-medium mb-2">Damage Description</label>
            <textarea
              v-model="editForm.damage_description"
              rows="4"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
            <div v-if="editForm.errors.damage_description" class="text-red-600 text-sm mt-1">
              {{ editForm.errors.damage_description }}
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Estimated Cost</label>
              <input
                v-model.number="editForm.estimated_cost"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Liability Status</label>
              <select
                v-model="editForm.customer_liability_status"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option v-for="(label, value) in customerLiabilityStatuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <select
              v-model="editForm.status"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="(label, value) in damageReportStatuses" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Admin Notes</label>
            <textarea
              v-model="editForm.admin_decision"
              rows="3"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="flex gap-2">
            <Button @click="submitEdit" :disabled="editForm.processing">Save</Button>
            <Button @click="showEditForm = false" variant="outline">Cancel</Button>
          </div>
        </div>

        <!-- Resolve Form -->
        <div v-if="showResolveForm" class="rounded-md border bg-yellow-50 border-yellow-300 p-6 space-y-4">
          <h3 class="font-semibold text-yellow-900">Resolve Damage Report</h3>

          <div>
            <label class="block text-sm font-medium mb-2">Final Liability Status</label>
            <select
              v-model="resolveForm.customer_liability_status"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            <Button @click="submitResolve" :disabled="resolveForm.processing">Confirm Resolution</Button>
            <Button @click="showResolveForm = false" variant="outline">Cancel</Button>
          </div>
        </div>

        <!-- Reject Form -->
        <div v-if="showRejectForm" class="rounded-md border bg-red-50 border-red-300 p-6 space-y-4">
          <h3 class="font-semibold text-red-900">Reject Damage Report</h3>

          <div>
            <label class="block text-sm font-medium mb-2">Rejection Reason (Optional)</label>
            <textarea
              v-model="rejectForm.reason"
              rows="3"
              placeholder="Explain why this report is being rejected..."
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
            <div v-if="rejectForm.errors.reason" class="text-red-600 text-sm mt-1">
              {{ rejectForm.errors.reason }}
            </div>
          </div>

          <div class="flex gap-2">
            <Button @click="submitReject" variant="destructive" :disabled="rejectForm.processing">Confirm Rejection</Button>
            <Button @click="showRejectForm = false" variant="outline">Cancel</Button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="!showEditForm && !showResolveForm && !showRejectForm" class="flex gap-2 flex-wrap">
          <Button @click="showEditForm = true">Edit Report</Button>
          <Button
            v-if="damageReport.status !== 'resolved' && damageReport.status !== 'rejected'"
            @click="showResolveForm = true"
            variant="outline"
          >
            Resolve
          </Button>
          <Button
            v-if="damageReport.status !== 'rejected' && damageReport.status !== 'resolved'"
            @click="showRejectForm = true"
            variant="outline"
            class="text-red-600 border-red-300 hover:bg-red-50"
          >
            Reject
          </Button>
          <Link v-if="damageReport.disputes && damageReport.disputes.length > 0" :href="route('admin.disputes.show', damageReport.disputes[0].id)">
            <Button variant="outline">View Dispute</Button>
          </Link>
        </div>
      </div>

      <!-- Evidence Tab -->
      <div v-if="activeTab === 'evidence'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Pickup Evidence -->
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

        <!-- Return Evidence -->
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
        <div class="space-y-4">
          <Link
            v-for="dispute in damageReport.disputes"
            :key="dispute.id"
            :href="route('admin.disputes.show', dispute.id)"
            class="block rounded-md border bg-white p-4 hover:shadow-md"
          >
            <div class="flex items-start justify-between">
              <div>
                <div class="font-medium">Dispute #{{ dispute.id }}</div>
                <div class="text-sm text-gray-600">Status: {{ dispute.status?.replace(/_/g, ' ') }}</div>
              </div>
              <Button variant="outline" size="sm">View</Button>
            </div>
          </Link>
        </div>
      </div>
    </main>
  </AdminLayout>
</template>
