<script setup lang="ts">
import ClientLayout from '@/layouts/ClientLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  damageReport: any
}>();

const form = useForm({
  customer_statement: '',
});

function submit() {
  form.post(route('client.disputes.store', props.damageReport.id));
}

function fmtDate(d?: string) {
  return d ? new Date(d).toLocaleDateString() : '—';
}
</script>

<template>
  <Head title="File Dispute" />
  <ClientLayout>
    <main class="flex-1 p-8 space-y-6">
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">File a Dispute</h1>
        <Link :href="route('client.damageReports.show', damageReport.id)">
          <Button variant="outline">Back</Button>
        </Link>
      </div>

      <p class="text-gray-700">
        If you disagree with the damage report, you can file a dispute. Our team will review your statement and respond within 5 business days.
      </p>

      <!-- Damage Report Summary -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Damage Report Summary</h3>
        <div class="space-y-2 text-sm">
          <div>
            <span class="text-gray-600">Report ID:</span>
            <span class="block font-medium">#{{ damageReport.id }}</span>
          </div>
          <div>
            <span class="text-gray-600">Estimated Cost:</span>
            <span class="block font-medium">${{ parseFloat(damageReport.estimated_cost || 0).toFixed(2) }}</span>
          </div>
          <div>
            <span class="text-gray-600">Vehicle:</span>
            <span class="block font-medium">{{ damageReport.car?.year }} {{ damageReport.car?.make }} {{ damageReport.car?.model }}</span>
          </div>
          <div>
            <span class="text-gray-600">Reservation:</span>
            <span class="block font-medium">{{ damageReport.reservation?.reservation_number }} ({{ fmtDate(damageReport.reservation?.start_date) }} - {{ fmtDate(damageReport.reservation?.end_date) }})</span>
          </div>
        </div>
      </div>

      <!-- Damage Description -->
      <div class="rounded-md border bg-white p-4">
        <h3 class="font-semibold mb-3">Reported Damage</h3>
        <p class="text-sm whitespace-pre-wrap text-gray-700">{{ damageReport.damage_description }}</p>
      </div>

      <!-- Evidence -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-md border bg-white p-4">
          <h3 class="font-semibold mb-3">Pickup Evidence</h3>
          <div v-if="!damageReport.pickupInspection?.evidence || damageReport.pickupInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in damageReport.pickupInspection.evidence"
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
          <h3 class="font-semibold mb-3">Return Evidence</h3>
          <div v-if="!damageReport.returnInspection?.evidence || damageReport.returnInspection.evidence.length === 0" class="text-sm text-gray-600">
            No evidence
          </div>
          <div v-else class="space-y-2">
            <a
              v-for="file in damageReport.returnInspection.evidence"
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

      <!-- Dispute Form -->
      <form @submit.prevent="submit" class="rounded-md border bg-white p-6 space-y-6">
        <h2 class="text-lg font-semibold">Your Statement</h2>

        <div>
          <label class="block text-sm font-medium mb-2">
            Why do you disagree with this damage report? *
          </label>
          <textarea
            v-model="form.customer_statement"
            rows="6"
            placeholder="Please provide a detailed explanation of why you believe this damage report is incorrect or unfair. Include any relevant details that support your position."
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          ></textarea>
          <div v-if="form.errors.customer_statement" class="text-red-600 text-sm mt-1">
            {{ form.errors.customer_statement }}
          </div>
          <div class="text-xs text-gray-500 mt-1">Minimum 10 characters required</div>
        </div>

        <div class="rounded-md bg-blue-50 border border-blue-200 p-3">
          <p class="text-sm text-blue-900">
            <strong>Next Steps:</strong> After you submit your dispute, our team will review your statement and the damage report evidence. We will contact you within 5 business days with our decision.
          </p>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Submitting...' : 'Submit Dispute' }}
          </Button>
          <Link :href="route('client.damageReports.show', damageReport.id)">
            <Button type="button" variant="outline">Cancel</Button>
          </Link>
        </div>
      </form>
    </main>
  </ClientLayout>
</template>
