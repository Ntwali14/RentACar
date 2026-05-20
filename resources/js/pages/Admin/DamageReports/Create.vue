<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  reservation: any
  pickupInspection: any
  returnInspection: any
  customerLiabilityStatuses: Record<string, string>
  damageReportStatuses: Record<string, string>
}>();

const form = useForm({
  damage_description: '',
  estimated_cost: null,
  customer_liability_status: 'pending',
  admin_decision: '',
  status: 'open',
});

function submit() {
  form.post(route('admin.damageReports.store', props.reservation.id));
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}

function getStatusColor(status: string) {
  const colorMap: Record<string, string> = {
    'good': '#10B981',
    'scratched': '#F59E0B',
    'dented': '#F59E0B',
    'cracked': '#EF5350',
    'missing': '#D32F2F',
    'dirty': '#757575',
    'damaged': '#D32F2F',
  };
  return colorMap[status] || '#6B7280';
}
</script>

<template>
  <Head title="Create Damage Report" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Create Damage Report</h1>
        <Link :href="route('admin.reservations.inspection-comparison', reservation.id)">
          <Button variant="outline">Back</Button>
        </Link>
      </div>

      <!-- Reservation Info -->
      <div class="rounded-md border bg-white">
        <div class="border-b px-4 py-3 font-medium">Reservation Information</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-sm text-gray-600">Reservation #</div>
            <div class="font-medium">{{ reservation.reservation_number }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Customer</div>
            <div class="font-medium">{{ reservation.user?.name }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Vehicle</div>
            <div class="font-medium">{{ reservation.car?.year }} {{ reservation.car?.make }} {{ reservation.car?.model }}</div>
          </div>
        </div>
      </div>

      <!-- Inspection Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pickup Inspection -->
        <div class="rounded-md border bg-white">
          <div class="border-b px-4 py-3 font-medium">Pickup Inspection</div>
          <div class="p-4 space-y-2 text-sm">
            <div>
              <span class="text-gray-600">Date:</span>
              <span class="font-medium">{{ fmtDate(pickupInspection.inspection_date) }}</span>
            </div>
            <div>
              <span class="text-gray-600">Mileage:</span>
              <span class="font-medium">{{ pickupInspection.mileage || '—' }}</span>
            </div>
            <div>
              <span class="text-gray-600">Fuel Level:</span>
              <span class="font-medium">{{ pickupInspection.fuel_level || '—' }}</span>
            </div>
            <div>
              <span class="text-gray-600">Condition:</span>
              <span class="font-medium">{{ pickupInspection.overall_condition }}</span>
            </div>
          </div>
        </div>

        <!-- Return Inspection -->
        <div class="rounded-md border bg-white">
          <div class="border-b px-4 py-3 font-medium">Return Inspection</div>
          <div class="p-4 space-y-2 text-sm">
            <div>
              <span class="text-gray-600">Date:</span>
              <span class="font-medium">{{ fmtDate(returnInspection.inspection_date) }}</span>
            </div>
            <div>
              <span class="text-gray-600">Mileage:</span>
              <span class="font-medium">{{ returnInspection.mileage || '—' }}</span>
            </div>
            <div>
              <span class="text-gray-600">Fuel Level:</span>
              <span class="font-medium">{{ returnInspection.fuel_level || '—' }}</span>
            </div>
            <div>
              <span class="text-gray-600">Condition:</span>
              <span class="font-medium">{{ returnInspection.overall_condition }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Evidence Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pickup Evidence -->
        <div class="rounded-md border bg-white">
          <div class="border-b px-4 py-3 font-medium">Pickup Evidence ({{ pickupInspection.evidence?.length || 0 }})</div>
          <div class="p-4">
            <div v-if="!pickupInspection.evidence || pickupInspection.evidence.length === 0" class="text-sm text-gray-600">
              No evidence uploaded
            </div>
            <div v-else class="grid grid-cols-2 gap-2">
              <a
                v-for="file in pickupInspection.evidence"
                :key="file.id"
                :href="'/' + file.file_path"
                target="_blank"
                class="block p-2 border rounded hover:bg-gray-50"
              >
                <div class="text-xs font-medium truncate">{{ file.caption || 'Untitled' }}</div>
                <div class="text-xs text-gray-600">{{ file.file_type }}</div>
              </a>
            </div>
          </div>
        </div>

        <!-- Return Evidence -->
        <div class="rounded-md border bg-white">
          <div class="border-b px-4 py-3 font-medium">Return Evidence ({{ returnInspection.evidence?.length || 0 }})</div>
          <div class="p-4">
            <div v-if="!returnInspection.evidence || returnInspection.evidence.length === 0" class="text-sm text-gray-600">
              No evidence uploaded
            </div>
            <div v-else class="grid grid-cols-2 gap-2">
              <a
                v-for="file in returnInspection.evidence"
                :key="file.id"
                :href="'/' + file.file_path"
                target="_blank"
                class="block p-2 border rounded hover:bg-gray-50"
              >
                <div class="text-xs font-medium truncate">{{ file.caption || 'Untitled' }}</div>
                <div class="text-xs text-gray-600">{{ file.file_type }}</div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Damage Report Form -->
      <form @submit.prevent="submit" class="rounded-md border bg-white p-6 space-y-6">
        <h2 class="text-lg font-semibold">Damage Details</h2>

        <div>
          <label class="block text-sm font-medium mb-2">Damage Description *</label>
          <textarea
            v-model="form.damage_description"
            rows="4"
            placeholder="Describe the damage observed and its location on the vehicle..."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
          <div v-if="form.errors.damage_description" class="text-red-600 text-sm mt-1">
            {{ form.errors.damage_description }}
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium mb-2">Estimated Cost ($)</label>
            <input
              v-model.number="form.estimated_cost"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <div v-if="form.errors.estimated_cost" class="text-red-600 text-sm mt-1">
              {{ form.errors.estimated_cost }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Customer Liability Status *</label>
            <select
              v-model="form.customer_liability_status"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            >
              <option v-for="(label, value) in customerLiabilityStatuses" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <div v-if="form.errors.customer_liability_status" class="text-red-600 text-sm mt-1">
              {{ form.errors.customer_liability_status }}
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Admin Decision/Notes</label>
          <textarea
            v-model="form.admin_decision"
            rows="3"
            placeholder="Add any notes or preliminary decision..."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          ></textarea>
          <div v-if="form.errors.admin_decision" class="text-red-600 text-sm mt-1">
            {{ form.errors.admin_decision }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Report Status *</label>
          <select
            v-model="form.status"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option v-for="(label, value) in damageReportStatuses" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
          <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">
            {{ form.errors.status }}
          </div>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create Damage Report' }}
          </Button>
          <Link :href="route('admin.reservations.inspection-comparison', reservation.id)">
            <Button type="button" variant="outline">Cancel</Button>
          </Link>
        </div>
      </form>
    </main>
  </AdminLayout>
</template>
