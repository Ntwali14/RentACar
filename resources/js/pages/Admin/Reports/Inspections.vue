<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Inspection {
    id: number;
    car: { id: number; make: string; model: string; license_plate: string };
    reservation: { id: number; reservation_number: string } | null;
    inspection_type: string;
    overall_condition: string;
    mileage: number;
    fuel_level: string;
    inspected_by: { name: string } | null;
    inspection_date: string;
    status: string;
    evidence_count: number;
}

const props = defineProps<{
    inspections: {
        data: Inspection[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    inspectionTypes: any[];
    inspectionStatuses: any[];
    cars: any[];
    filters: {
        inspection_type?: string;
        status?: string;
        car_id?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const inspectionTypeFilter = ref(props.filters?.inspection_type || '');
const statusFilter = ref(props.filters?.status || '');
const carFilter = ref(props.filters?.car_id || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get('/admin/reports/inspections', {
        inspection_type: inspectionTypeFilter.value || undefined,
        status: statusFilter.value || undefined,
        car_id: carFilter.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    inspectionTypeFilter.value = '';
    statusFilter.value = '';
    carFilter.value = '';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/reports/inspections', {}, { preserveState: true, replace: true });
};

const typeColors: Record<string, string> = {
    'pickup': 'bg-blue-100 text-blue-800',
    'return': 'bg-green-100 text-green-800',
};

const statusColors: Record<string, string> = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'incomplete': 'bg-orange-100 text-orange-800',
};

const getTypeClass = (type: string) => typeColors[type] || 'bg-gray-100 text-gray-800';
const getStatusClass = (status: string) => statusColors[status] || 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head title="Inspections Report" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Inspections Report</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <select v-model="inspectionTypeFilter" class="rounded-md border-gray-300">
                        <option value="">All Types</option>
                        <option value="pickup">Pickup</option>
                        <option value="return">Return</option>
                    </select>
                    <select v-model="statusFilter" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                    <select v-model="carFilter" class="rounded-md border-gray-300">
                        <option value="">All Vehicles</option>
                        <option v-for="car in cars" :key="car.id" :value="String(car.id)">
                            {{ car.make }} {{ car.model }} ({{ car.license_plate }})
                        </option>
                    </select>
                    <input v-model="startDate" type="date" class="rounded-md border-gray-300" />
                    <input v-model="endDate" type="date" class="rounded-md border-gray-300" />
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reservation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mileage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fuel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inspector</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="inspection in inspections.data" :key="inspection.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ inspection.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ inspection.car.make }} {{ inspection.car.model }}<br>
                                <span class="text-gray-500">{{ inspection.car.license_plate }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link v-if="inspection.reservation" :href="`/admin/reservations/${inspection.reservation.id}`" class="text-primary hover:underline">
                                    {{ inspection.reservation.reservation_number }}
                                </Link>
                                <span v-else class="text-gray-500">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getTypeClass(inspection.inspection_type)}`">
                                    {{ inspection.inspection_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inspection.mileage }} km</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inspection.fuel_level }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inspection.overall_condition }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inspection.inspected_by?.name || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ inspection.inspection_date.split(' ')[0] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusClass(inspection.status)}`">
                                    {{ inspection.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                <span v-if="inspection.evidence_count > 0" class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                    {{ inspection.evidence_count }}
                                </span>
                                <span v-else class="text-gray-500">0</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ (inspections.current_page - 1) * 20 + 1 }} to {{ Math.min(inspections.current_page * 20, inspections.total) }} of {{ inspections.total }}
                </div>
                <div class="flex gap-2">
                    <Link
                        v-for="link in inspections.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="{
                            'px-3 py-1 rounded border': true,
                            'bg-primary text-white': link.active,
                            'bg-white text-gray-700 border-gray-300': !link.active,
                            'opacity-50 cursor-not-allowed': !link.url,
                        }"
                    >
                        {{ link.label }}
                    </Link>
                </div>
            </div>
        </main>
    </AdminLayout>
</template>
