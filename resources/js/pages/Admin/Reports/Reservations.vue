<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Reservation {
    id: number;
    reservation_number: string;
    user: { id: number; name: string; email: string } | null;
    car: { id: number; make: string; model: string; license_plate: string } | null;
    start_date: string;
    end_date: string;
    total_amount: number;
    status: string;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    reservations: {
        data: Reservation[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    statuses: any[];
    filters: {
        status?: string;
        search?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get('/admin/reports/reservations', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/reports/reservations', {}, { preserveState: true, replace: true });
};

const statusLabels: Record<string, string> = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'active': 'Active',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
};

const statusColors: Record<string, string> = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'active': 'bg-green-100 text-green-800',
    'completed': 'bg-gray-100 text-gray-800',
    'cancelled': 'bg-red-100 text-red-800',
};

const getStatusBadgeClass = (status: string) => {
    return statusColors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Reservations Report" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Reservations Report</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Reservation # or customer..."
                    />
                    <select v-model="statusFilter" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <Input v-model="startDate" type="date" placeholder="Start Date" />
                    <Input v-model="endDate" type="date" placeholder="End Date" />
                </div>
                <div class="flex gap-2">
                    <Button @click="applyFilters">Apply Filters</Button>
                    <Button variant="outline" @click="clearFilters">Clear</Button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg bg-white shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reservation #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="res in reservations.data" :key="res.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/admin/reservations/${res.id}`" class="text-primary hover:underline">
                                    {{ res.reservation_number }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-medium">{{ res.user?.name || 'N/A' }}</div>
                                <div class="text-gray-500">{{ res.user?.email || '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-medium">{{ res.car?.make }} {{ res.car?.model }}</div>
                                <div class="text-gray-500">{{ res.car?.license_plate }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div>{{ res.start_date }}</div>
                                <div class="text-gray-500">to {{ res.end_date }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${{ Number(res.total_amount).toFixed(2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadgeClass(res.status)}`">
                                    {{ statusLabels[res.status] || res.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ res.created_at.split(' ')[0] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ (reservations.current_page - 1) * 20 + 1 }} to {{ Math.min(reservations.current_page * 20, reservations.total) }} of {{ reservations.total }} reservations
                </div>
                <div class="flex gap-2">
                    <Link
                        v-for="link in reservations.links"
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
