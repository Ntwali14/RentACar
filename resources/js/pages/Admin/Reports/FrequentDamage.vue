<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface DamageVehicle {
    id: number;
    make: string;
    model: string;
    license_plate: string;
    status: string;
    damage_reports_count: number;
    disputes_count: number;
    latest_damage_date: string | null;
    estimated_total_cost: number;
}

const props = defineProps<{
    vehicles: DamageVehicle[];
}>();

const statusColors: Record<string, string> = {
    'Available': 'bg-green-100 text-green-800',
    'Rented': 'bg-blue-100 text-blue-800',
    'Reserved': 'bg-purple-100 text-purple-800',
    'Maintenance': 'bg-yellow-100 text-yellow-800',
    'Cleaning': 'bg-orange-100 text-orange-800',
    'Unavailable': 'bg-red-100 text-red-800',
};

const getStatusBadgeClass = (status: string) => statusColors[status] || 'bg-gray-100 text-gray-800';

const getRiskLevel = (count: number): string => {
    if (count >= 5) return 'Critical';
    if (count >= 3) return 'High';
    if (count >= 1) return 'Medium';
    return 'Low';
};

const getRiskColor = (count: number): string => {
    if (count >= 5) return 'bg-red-100 text-red-800';
    if (count >= 3) return 'bg-orange-100 text-orange-800';
    if (count >= 1) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
};
</script>

<template>
    <Head title="Frequent Damage Report" />
    <AdminLayout>
        <main class="flex-1 space-y-6 p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Frequent Damage Report</h1>
                <Link href="/admin/reports" class="text-primary hover:underline">Back to Reports</Link>
            </div>

            <!-- Info Card -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                <p class="text-sm text-blue-800">
                    This report shows vehicles with the most damage reports. Use this to identify problem vehicles that may need maintenance, repair, or inspection.
                </p>
            </div>

            <!-- Table -->
            <div class="rounded-lg bg-white shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">License Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Damage Reports</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disputes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Estimated Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Latest Damage Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="vehicle in vehicles" :key="vehicle.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/admin/cars/${vehicle.id}/edit`" class="text-primary hover:underline">
                                    {{ vehicle.make }} {{ vehicle.model }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ vehicle.license_plate }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadgeClass(vehicle.status)}`">
                                    {{ vehicle.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800">
                                    {{ vehicle.damage_reports_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="vehicle.disputes_count > 0" class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-800">
                                    {{ vehicle.disputes_count }}
                                </span>
                                <span v-else class="text-sm text-gray-500">0</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ${{ Number(vehicle.estimated_total_cost).toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ vehicle.latest_damage_date || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${getRiskColor(vehicle.damage_reports_count)}`">
                                    {{ getRiskLevel(vehicle.damage_reports_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link :href="`/admin/cars/${vehicle.id}/edit`" class="text-primary hover:underline">
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-if="vehicles.length === 0" class="rounded-lg bg-gray-50 p-12 text-center">
                <p class="text-gray-600">No vehicles with damage reports found.</p>
            </div>
        </main>
    </AdminLayout>
</template>
