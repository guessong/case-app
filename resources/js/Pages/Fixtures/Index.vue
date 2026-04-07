<template>
    <AppLayout>
        <template #header>Generated Fixtures</template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                <div v-for="(fixtures, week) in fixturesByWeek" :key="week"
                     class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
                    <div class="px-3 py-2 bg-slate-800 border-b border-slate-700">
                        <span class="text-xs font-heading font-semibold text-slate-400 uppercase tracking-wider">Week {{ week }}</span>
                    </div>
                    <div class="divide-y divide-slate-800/50">
                        <div v-for="fixture in fixtures" :key="fixture.id"
                             class="px-3 py-2.5 flex items-center text-sm">
                            <span class="flex-1 text-right text-slate-200 font-medium truncate pr-2">{{ fixture.home_team.name }}</span>
                            <span class="text-slate-600 text-xs">vs</span>
                            <span class="flex-1 text-slate-200 font-medium truncate pl-2">{{ fixture.away_team.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <button @click="startSimulation"
                class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-heading font-semibold py-2.5 px-6 rounded-lg
                       text-sm transition-all">
                Start Simulation
            </button>
        </div>
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
