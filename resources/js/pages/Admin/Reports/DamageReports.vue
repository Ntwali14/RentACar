<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface DamageReport {
    id: number;
    car: { id: number; make: string; model: string; license_plate: string };
    reservation: { id: number; reservation_number: string } | null;
    damage_description: string;
    estimated_cost: number;
    customer_liability_status: string;
    status: string;
    reported_by: { name: string } | null;
    created_at: string;
    disputes_count?: number;
}

const props = defineProps<{
    damageReports: {
        data: DamageReport[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    statuses: any[];
    liabilityStatuses: any[];
    cars: any[];
    filters: {
        status?: string;
        liability_status?: string;
        car_id?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const statusFilter = ref(props.filters?.status || '');
const liabilityFilter = ref(props.filters?.liability_status || '');
const carFilter = ref(props.filters?.car_id || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get('/admin/reports/damage-reports', {
        status: statusFilter.value || undefined,
        liability_status: liabilityFilter.value || undefined,
        car_id: carFilter.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    liabilityFilter.value = '';
    carFilter.value = '';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/reports/damage-reports', {}, { preserveState: true, replace: true });
};

const statusColors: Record<string, string> = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-blue-100 text-blue-800',
    'rejected': 'bg-red-100 text-red-800',
    'resolved': 'bg-green-100 text-green-800',
};

const liabilityColors: Record<string, string> = {
    'customer_responsible': 'bg-red-100 text-red-800',
    'company_responsible': 'bg-green-100 text-green-800',
    'split_liability': 'bg-orange-100 text-orange-800',
    'pending': 'bg-gray-100 text-gray-800',
};

const getStatusClass = (status: string) => statusColors[status] || 'bg-gray-100 text-gray-800';
const getLiabilityClass = (status: string) => liabilityColors[status] || 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head title="Damage Reports" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Damage Reports</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <select v-model="statusFilter" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <select v-model="liabilityFilter" class="rounded-md border-gray-300">
                        <option value="">All Liabilities</option>
                        <option value="customer_responsible">Customer Responsible</option>
                        <option value="company_responsible">Company Responsible</option>
                        <option value="split_liability">Split Liability</option>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estimated Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Liability</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="report in damageReports.data" :key="report.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ report.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ report.car.make }} {{ report.car.model }}<br>
                                <span class="text-gray-500">{{ report.car.license_plate }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm max-w-xs truncate">{{ report.damage_description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${{ Number(report.estimated_cost).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getLiabilityClass(report.customer_liability_status)}`">
                                    {{ report.customer_liability_status.replace(/_/g, ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusClass(report.status)}`">
                                    {{ report.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ report.reported_by?.name || 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ report.created_at.split(' ')[0] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="`/admin/damage-reports/${report.id}`" class="text-primary hover:underline">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ (damageReports.current_page - 1) * 20 + 1 }} to {{ Math.min(damageReports.current_page * 20, damageReports.total) }} of {{ damageReports.total }}
                </div>
                <div class="flex gap-2">
                    <Link
                        v-for="link in damageReports.links"
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
