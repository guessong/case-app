<template>
    <AppLayout>
        <template #header>Generated Fixtures</template>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-for="(fixtures, week) in fixturesByWeek" :key="week"
                 class="bg-white shadow rounded overflow-hidden">
                <div class="bg-gray-700 text-white py-2 px-4 font-bold">
                    Week {{ week }}
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="fixture in fixtures" :key="fixture.id"
                         class="flex items-center justify-between text-gray-800">
                        <span class="flex-1 text-right pr-3">{{ fixture.home_team.name }}</span>
                        <span class="text-gray-400 px-2">-</span>
                        <span class="flex-1 pl-3">{{ fixture.away_team.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <button @click="startSimulation"
            class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                   transition-colors">
            Start Simulation
        </button>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    fixturesByWeek: Object,
});

function startSimulation() {
    router.visit('/simulation');
}
</script>
