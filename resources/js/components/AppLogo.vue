<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { computed } from 'vue';

const appName = computed(() => {
    const name = import.meta.env.VITE_APP_NAME || 'RentACar';
    return name;
});
</script>

<template>

        <AppLogoIcon  />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >{{ appName }}</span
        >
    </div>
</template>
