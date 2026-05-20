<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';
import { show } from '@/routes/admin/reservations';

const props = defineProps<{
  reservation: any
  conditionStatuses: Record<string, string>
  severities: Record<string, string>
  checklist_areas: string[]
}>()

const isSubmitting = ref(false)
const selectedFiles = ref<File[]>([])
const fileCaptions = ref<string[]>([])

const formData = ref({
  mileage: '',
  fuel_level: '',
  overall_condition: '',
  notes: '',
  inspection_date: new Date().toISOString().split('T')[0],
  condition_items: props.checklist_areas.map(area => ({
    area_name: area,
    condition_status: 'good',
    severity: '',
    notes: '',
  })),
})

function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  if (input.files) {
    selectedFiles.value = Array.from(input.files)
    fileCaptions.value = Array(selectedFiles.value.length).fill('')
  }
}

function removeFile(index: number) {
  selectedFiles.value.splice(index, 1)
  fileCaptions.value.splice(index, 1)
}

function handleSubmit() {
  isSubmitting.value = true

  const formElement = document.getElementById('pickupForm') as HTMLFormElement
  const data = new FormData(formElement)

  // Add file captions
  fileCaptions.value.forEach((caption, index) => {
    data.append(`evidence_captions[${index}]`, caption)
  })

  router.post(route('admin.pickupInspection.store', props.reservation.id), data, {
    onError: () => {
      isSubmitting.value = false
    },
  })
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—'
}
</script>

<template>
  <Head :title="`Pickup Inspection - ${reservation?.reservation_number || ''}`" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">
          Pickup Inspection - {{ reservation?.reservation_number }}
        </h1>
        <div class="flex gap-2">
          <Link :href="show(reservation.id).url">
            <Button variant="outline">Cancel</Button>
          </Link>
        </div>
      </div>

      <form id="pickupForm" class="space-y-6" @submit.prevent="handleSubmit">
        <!-- Reservation Info -->
        <div class="rounded-md border">
          <div class="border-b px-4 py-3 font-medium">Reservation Information</div>
          <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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
            <div>
              <div class="text-sm text-gray-600">License Plate</div>
              <div class="font-medium">{{ reservation.car?.license_plate }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Pickup Date</div>
              <div class="font-medium">{{ fmtDate(reservation.start_date) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Return Date</div>
              <div class="font-medium">{{ fmtDate(reservation.end_date) }}</div>
            </div>
          </div>
        </div>

        <!-- Inspection Details -->
        <div class="rounded-md border">
          <div class="border-b px-4 py-3 font-medium">Inspection Details</div>
          <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Inspection Date
                </label>
                <input
                  v-model="formData.inspection_date"
                  type="date"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Mileage
                </label>
                <input
                  v-model="formData.mileage"
                  type="number"
                  placeholder="0"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Fuel Level
              </label>
              <input
                v-model="formData.fuel_level"
                type="text"
                placeholder="e.g., Full, 3/4, Half, 1/4, Empty"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Overall Condition <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.overall_condition"
                type="text"
                placeholder="Excellent, Good, Fair, Poor"
                required
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                General Notes
              </label>
              <textarea
                v-model="formData.notes"
                rows="3"
                placeholder="Any general observations about the vehicle..."
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Condition Checklist -->
        <div class="rounded-md border">
          <div class="border-b px-4 py-3 font-medium">Vehicle Condition Checklist</div>
          <div class="p-4 overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-4 py-2 text-left font-medium text-gray-700">Area</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-700">Condition</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-700">Severity</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-700">Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in formData.condition_items"
                  :key="index"
                  class="border-b hover:bg-gray-50"
                >
                  <td class="px-4 py-2">
                    <input
                      v-model="item.area_name"
                      type="text"
                      class="w-full border rounded px-2 py-1"
                    />
                  </td>
                  <td class="px-4 py-2">
                    <select
                      v-model="item.condition_status"
                      class="w-full border rounded px-2 py-1"
                    >
                      <option v-for="(label, value) in conditionStatuses" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                  </td>
                  <td class="px-4 py-2">
                    <select
                      v-model="item.severity"
                      class="w-full border rounded px-2 py-1"
                    >
                      <option value="">None</option>
                      <option v-for="(label, value) in severities" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                  </td>
                  <td class="px-4 py-2">
                    <input
                      v-model="item.notes"
                      type="text"
                      placeholder="Notes..."
                      class="w-full border rounded px-2 py-1"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Evidence Upload -->
        <div class="rounded-md border">
          <div class="border-b px-4 py-3 font-medium">Evidence (Photos/Documents)</div>
          <div class="p-4 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Upload Files
              </label>
              <input
                type="file"
                name="evidence[]"
                multiple
                accept="image/jpeg,image/png,image/webp,.pdf"
                @change="handleFileSelect"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              />
              <p class="mt-1 text-xs text-gray-500">JPG, PNG, WebP, or PDF (max 5MB each)</p>
            </div>

            <div v-if="selectedFiles.length > 0" class="space-y-2">
              <div
                v-for="(file, index) in selectedFiles"
                :key="index"
                class="flex items-center gap-3 bg-gray-50 p-3 rounded border"
              >
                <div class="flex-1">
                  <div class="text-sm font-medium">{{ file.name }}</div>
                  <input
                    v-model="fileCaptions[index]"
                    type="text"
                    placeholder="Caption (optional)"
                    class="w-full text-xs border rounded px-2 py-1 mt-1"
                  />
                </div>
                <button
                  type="button"
                  @click="removeFile(index)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-2 justify-end">
          <Link :href="show(reservation.id).url">
            <Button variant="outline">Cancel</Button>
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Completing...' : 'Complete Pickup Inspection' }}
          </button>
        </div>
      </form>
    </main>
  </AdminLayout>
</template>
