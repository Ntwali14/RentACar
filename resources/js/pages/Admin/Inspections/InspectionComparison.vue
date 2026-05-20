<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { show } from '@/routes/admin/reservations';

const props = defineProps<{
  reservation: any
  pickupInspection: any
  returnInspection: any
  comparison: any[]
}>()

const damageSummary = computed(() => {
  const changed = props.comparison.filter(item => item.changed)
  return {
    total: props.comparison.length,
    changed: changed.length,
    percentage: changed.length > 0 ? ((changed.length / props.comparison.length) * 100).toFixed(0) : 0,
    items: changed,
  }
})

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—'
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
  }
  return colorMap[status] || '#6B7280'
}

function getSeverityLabel(severity: string | null) {
  if (!severity) return '—'
  return severity.charAt(0).toUpperCase() + severity.slice(1)
}
</script>

<template>
  <Head :title="`Inspection Comparison - ${reservation?.reservation_number || ''}`" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
          Inspection Comparison - {{ reservation?.reservation_number }}
        </h1>
        <div class="flex gap-2">
          <Link :href="show(reservation.id).url">
            <Button variant="outline">Back to Reservation</Button>
          </Link>
        </div>
      </div>

      <!-- Reservation Info -->
      <div class="rounded-md border">
        <div class="border-b px-4 py-3 font-medium">Reservation Information</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-sm text-gray-600">Reservation #</div>
            <div class="font-medium">{{ reservation.reservation_number }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Vehicle</div>
            <div class="font-medium">{{ reservation.car?.year }} {{ reservation.car?.make }} {{ reservation.car?.model }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Customer</div>
            <div class="font-medium">{{ reservation.user?.name }}</div>
          </div>
        </div>
      </div>

      <!-- Damage Summary -->
      <div
        v-if="damageSummary.changed > 0"
        class="rounded-md border-2 border-yellow-300 bg-yellow-50 p-4"
      >
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-yellow-900">⚠️ Possible Changes Detected</h3>
            <p class="text-sm text-yellow-800 mt-1">
              {{ damageSummary.changed }} out of {{ damageSummary.total }} areas show changes from the pickup inspection
              ({{ damageSummary.percentage }}%)
            </p>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold text-yellow-600">{{ damageSummary.changed }}</div>
            <div class="text-xs text-yellow-700">areas changed</div>
          </div>
        </div>
      </div>

      <div
        v-else
        class="rounded-md border-2 border-green-300 bg-green-50 p-4"
      >
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-green-900">✓ No Changes Detected</h3>
            <p class="text-sm text-green-800 mt-1">
              All vehicle areas maintain their condition from pickup inspection
            </p>
          </div>
        </div>
      </div>

      <!-- Comparison Table -->
      <div class="rounded-md border">
        <div class="border-b px-4 py-3 font-medium">Detailed Condition Comparison</div>
        <div class="p-4 overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Area</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Pickup Status</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Return Status</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Change</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in comparison"
                :key="item.area_name"
                class="border-b hover:bg-gray-50"
                :class="{ 'bg-yellow-50': item.changed }"
              >
                <td class="px-4 py-2 font-medium">{{ item.area_name }}</td>
                <td class="px-4 py-2">
                  <div v-if="item.pickup" class="space-y-1">
                    <span
                      class="inline-block px-2 py-1 rounded text-xs font-medium text-white"
                      :style="{ backgroundColor: getStatusColor(item.pickup.condition_status) }"
                    >
                      {{ item.pickup.condition_status }}
                    </span>
                    <div v-if="item.pickup.severity" class="text-xs text-gray-600">
                      Severity: {{ getSeverityLabel(item.pickup.severity) }}
                    </div>
                    <div v-if="item.pickup.notes" class="text-xs text-gray-600 italic">
                      {{ item.pickup.notes }}
                    </div>
                  </div>
                  <div v-else class="text-gray-500">—</div>
                </td>
                <td class="px-4 py-2">
                  <div v-if="item.return" class="space-y-1">
                    <span
                      class="inline-block px-2 py-1 rounded text-xs font-medium text-white"
                      :style="{ backgroundColor: getStatusColor(item.return.condition_status) }"
                    >
                      {{ item.return.condition_status }}
                    </span>
                    <div v-if="item.return.severity" class="text-xs text-gray-600">
                      Severity: {{ getSeverityLabel(item.return.severity) }}
                    </div>
                    <div v-if="item.return.notes" class="text-xs text-gray-600 italic">
                      {{ item.return.notes }}
                    </div>
                  </div>
                  <div v-else class="text-gray-500">—</div>
                </td>
                <td class="px-4 py-2">
                  <div v-if="item.changed" class="space-y-1">
                    <span class="inline-block px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                      Changed
                    </span>
                    <div class="text-xs text-red-600">{{ item.changeReason }}</div>
                  </div>
                  <div v-else class="text-green-600 text-xs font-medium">No change</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pickup Inspection Details -->
      <div class="rounded-md border bg-blue-50">
        <div class="border-b px-4 py-3 font-medium bg-blue-100">Pickup Inspection Details</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-600">Inspection Date</div>
            <div class="font-medium">{{ fmtDate(pickupInspection.inspection_date) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Mileage</div>
            <div class="font-medium">{{ pickupInspection.mileage || '—' }} km</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Fuel Level</div>
            <div class="font-medium">{{ pickupInspection.fuel_level || '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Overall Condition</div>
            <div class="font-medium">{{ pickupInspection.overall_condition }}</div>
          </div>
          <div class="md:col-span-2">
            <div class="text-sm text-gray-600">Notes</div>
            <div class="font-medium">{{ pickupInspection.notes || '—' }}</div>
          </div>
        </div>
      </div>

      <!-- Return Inspection Details -->
      <div class="rounded-md border bg-green-50">
        <div class="border-b px-4 py-3 font-medium bg-green-100">Return Inspection Details</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-600">Inspection Date</div>
            <div class="font-medium">{{ fmtDate(returnInspection.inspection_date) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Mileage</div>
            <div class="font-medium">{{ returnInspection.mileage || '—' }} km</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Fuel Level</div>
            <div class="font-medium">{{ returnInspection.fuel_level || '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-600">Overall Condition</div>
            <div class="font-medium">{{ returnInspection.overall_condition }}</div>
          </div>
          <div class="md:col-span-2">
            <div class="text-sm text-gray-600">Notes</div>
            <div class="font-medium">{{ returnInspection.notes || '—' }}</div>
          </div>
        </div>
      </div>

      <!-- Pickup Evidence -->
      <div v-if="pickupInspection.evidence && pickupInspection.evidence.length > 0" class="rounded-md border">
        <div class="border-b px-4 py-3 font-medium">Pickup Inspection Evidence</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="file in pickupInspection.evidence" :key="file.id" class="border rounded p-3 bg-gray-50">
            <div class="text-sm font-medium mb-1">{{ file.file_type.toUpperCase() }} File</div>
            <div v-if="file.caption" class="text-sm text-gray-600 mb-2">{{ file.caption }}</div>
            <a
              :href="`/storage/${file.file_path}`"
              target="_blank"
              rel="noopener"
              class="text-xs text-blue-600 hover:text-blue-800 underline"
            >
              View File
            </a>
          </div>
        </div>
      </div>

      <!-- Return Evidence -->
      <div v-if="returnInspection.evidence && returnInspection.evidence.length > 0" class="rounded-md border">
        <div class="border-b px-4 py-3 font-medium">Return Inspection Evidence</div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="file in returnInspection.evidence" :key="file.id" class="border rounded p-3 bg-gray-50">
            <div class="text-sm font-medium mb-1">{{ file.file_type.toUpperCase() }} File</div>
            <div v-if="file.caption" class="text-sm text-gray-600 mb-2">{{ file.caption }}</div>
            <a
              :href="`/storage/${file.file_path}`"
              target="_blank"
              rel="noopener"
              class="text-xs text-blue-600 hover:text-blue-800 underline"
            >
              View File
            </a>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 justify-end">
        <Link :href="route('admin.damageReports.create', reservation.id)">
          <Button>Create Damage Report</Button>
        </Link>
        <Link :href="show(reservation.id).url">
          <Button variant="outline">Back to Reservation</Button>
        </Link>
      </div>
    </main>
  </AdminLayout>
</template>
