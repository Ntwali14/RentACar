<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  damageReport: any
  customerLiabilityStatuses: Record<string, string>
  damageReportStatuses: Record<string, string>
}>();

const form = useForm({
  damage_description: props.damageReport.damage_description,
  estimated_cost: props.damageReport.estimated_cost,
  customer_liability_status: props.damageReport.customer_liability_status,
  admin_decision: props.damageReport.admin_decision,
  status: props.damageReport.status,
});

function submit() {
  form.put(route('admin.damageReports.update', props.damageReport.id));
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}
</script>

<template>
  <Head :title="`Edit Damage Report #${damageReport.id}`" />
  <AdminLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Edit Damage Report #{{ damageReport.id }}</h1>
        <Link :href="route('admin.damageReports.show', damageReport.id)">
          <Button variant="outline">Back</Button>
        </Link>
      </div>

      <!-- Reservation Summary -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Reservation Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div>
            <span class="text-gray-600">Reservation #:</span>
            <div class="font-medium">{{ damageReport.reservation?.reservation_number }}</div>
          </div>
          <div>
            <span class="text-gray-600">Customer:</span>
            <div class="font-medium">{{ damageReport.reservation?.user?.name }}</div>
          </div>
          <div>
            <span class="text-gray-600">Vehicle:</span>
            <div class="font-medium">{{ damageReport.car?.year }} {{ damageReport.car?.make }} {{ damageReport.car?.model }}</div>
          </div>
        </div>
      </div>

      <!-- Edit Form -->
      <form @submit.prevent="submit" class="rounded-md border bg-white p-6 space-y-6">
        <div>
          <label class="block text-sm font-medium mb-2">Damage Description *</label>
          <textarea
            v-model="form.damage_description"
            rows="4"
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
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </Button>
          <Link :href="route('admin.damageReports.show', damageReport.id)">
            <Button type="button" variant="outline">Cancel</Button>
          </Link>
        </div>
      </form>
    </main>
  </AdminLayout>
</template>
