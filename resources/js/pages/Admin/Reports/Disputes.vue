<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Dispute {
    id: number;
    damage_report_id: number;
    customer: { id: number; name: string; email: string };
    admin: { id: number; name: string } | null;
    status: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    disputes: {
        data: Dispute[];
        links: any[];
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

const statusFilter = ref(props.filters?.status || '');
const search = ref(props.filters?.search || '');
const startDate = ref(props.filters?.start_date || '');
const endDate = ref(props.filters?.end_date || '');

const applyFilters = () => {
    router.get('/admin/reports/disputes', {
        status: statusFilter.value || undefined,
        search: search.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = '';
    search.value = '';
    startDate.value = '';
    endDate.value = '';
    router.get('/admin/reports/disputes', {}, { preserveState: true, replace: true });
};

const statusColors: Record<string, string> = {
    'open': 'bg-yellow-100 text-yellow-800',
    'pending_admin': 'bg-orange-100 text-orange-800',
    'under_review': 'bg-blue-100 text-blue-800',
    'resolved': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800',
};

const getStatusClass = (status: string) => statusColors[status] || 'bg-gray-100 text-gray-800';
</script>

<template>
    <Head title="Disputes Report" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Disputes Report</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <input v-model="search" type="text" placeholder="Customer name or email..." class="rounded-md border-gray-300" />
                    <select v-model="statusFilter" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="pending_admin">Pending Admin</option>
                        <option value="under_review">Under Review</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Damage Report</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin Assigned</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="dispute in disputes.data" :key="dispute.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ dispute.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="`/admin/damage-reports/${dispute.damage_report_id}`" class="text-primary hover:underline">
                                    DR-{{ dispute.damage_report_id }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-medium">{{ dispute.customer.name }}</div>
                                <div class="text-gray-500 text-xs">{{ dispute.customer.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusClass(dispute.status)}`">
                                    {{ dispute.status.replace(/_/g, ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ dispute.admin?.name || 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ dispute.created_at.split(' ')[0] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ dispute.updated_at.split(' ')[0] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="`/admin/disputes/${dispute.id}`" class="text-primary hover:underline">
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
                    Showing {{ (disputes.current_page - 1) * 20 + 1 }} to {{ Math.min(disputes.current_page * 20, disputes.total) }} of {{ disputes.total }}
                </div>
                <div class="flex gap-2">
                    <Link
                        v-for="link in disputes.links"
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
