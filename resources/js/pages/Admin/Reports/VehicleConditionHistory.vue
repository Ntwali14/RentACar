<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Vehicle {
    id: number;
    make: string;
    model: string;
    license_plate: string;
    status: string;
    condition_status: string;
    total_inspections: number;
    last_pickup_inspection: string | null;
    last_return_inspection: string | null;
    damage_reports_count: number;
    created_at: string;
}

const props = defineProps<{
    vehicles: Vehicle[];
    conditionStatuses: any[];
    filters: {
        status?: string;
    };
}>();

const statusFilter = ref(props.filters?.status || '');

const applyFilters = () => {
    router.get('/admin/reports/vehicle-condition-history', {
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    router.get('/admin/reports/vehicle-condition-history', {}, { preserveState: true, replace: true });
};

const conditionColors: Record<string, string> = {
    'Excellent': 'bg-green-100 text-green-800',
    'Good': 'bg-blue-100 text-blue-800',
    'Fair': 'bg-yellow-100 text-yellow-800',
    'Poor': 'bg-orange-100 text-orange-800',
    'Damaged': 'bg-red-100 text-red-800',
    'Unknown': 'bg-gray-100 text-gray-800',
};

const getConditionBadgeClass = (status: string) => {
    return conditionColors[status] || 'bg-gray-100 text-gray-800';
};

const statusColors: Record<string, string> = {
    'Available': 'bg-green-100 text-green-800',
    'Rented': 'bg-blue-100 text-blue-800',
    'Reserved': 'bg-purple-100 text-purple-800',
    'Maintenance': 'bg-yellow-100 text-yellow-800',
    'Cleaning': 'bg-orange-100 text-orange-800',
    'Unavailable': 'bg-red-100 text-red-800',
};

const getStatusBadgeClass = (status: string) => {
    return statusColors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Vehicle Condition History Report" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Vehicle Condition History</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <select v-model="statusFilter" class="rounded-md border-gray-300">
                        <option value="">All Condition Statuses</option>
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button @click="applyFilters" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                        Apply Filters
                    </button>
                    <button @click="clearFilters" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg bg-white shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">License Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inspections</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Pickup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Return</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Damage Reports</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="vehicle in vehicles" :key="vehicle.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/admin/cars/${vehicle.id}/edit`" class="text-primary hover:underline">
                                    {{ vehicle.make }} {{ vehicle.model }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ vehicle.license_plate }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadgeClass(vehicle.status)}`">
                                    {{ vehicle.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getConditionBadgeClass(vehicle.condition_status)}`">
                                    {{ vehicle.condition_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">{{ vehicle.total_inspections }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ vehicle.last_pickup_inspection || 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ vehicle.last_return_inspection || 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="vehicle.damage_reports_count > 0" class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                                    {{ vehicle.damage_reports_count }}
                                </span>
                                <span v-else class="text-sm text-gray-500">0</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-if="vehicles.length === 0" class="rounded-lg bg-gray-50 p-12 text-center">
                <p class="text-gray-600">No vehicles found matching the selected filters.</p>
            </div>
        </main>
    </AdminLayout>
</template>
