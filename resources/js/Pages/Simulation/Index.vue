<template>
    <AppLayout>
        <template #header>Simulation</template>

        <div class="flex flex-col" style="height: calc(100vh - 65px);">
            <!-- Main Content -->
            <div class="flex-1 overflow-hidden">
                <div class="h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-full">
                        <!-- League Table -->
                        <div class="lg:col-span-5 overflow-y-auto">
                            <LeagueTable :standings="standings" />
                        </div>

                        <!-- Week Results - SCROLLABLE -->
                        <div class="lg:col-span-4 overflow-y-auto space-y-3 pr-1">
                            <!-- Next week to play -->
                            <div v-if="!isFinished" class="bg-slate-900 rounded-xl border border-teal-500/30 overflow-hidden">
                                <div class="px-3 py-2 bg-teal-500/10 border-b border-teal-500/20 flex items-center justify-between">
                                    <span class="text-xs font-heading font-semibold text-teal-400 uppercase tracking-wider">
                                        Week {{ currentWeek }}
                                    </span>
                                    <span class="text-[10px] text-teal-500/70 uppercase tracking-widest">Upcoming</span>
                                </div>
                                <div class="divide-y divide-slate-800/50">
                                    <div v-for="fixture in weekFixtures" :key="fixture.id"
                                         class="px-3 py-2 flex items-center text-sm">
                                        <span class="flex-1 text-right text-slate-200 font-medium truncate">
                                            {{ fixture.home_team.name }}
                                        </span>
                                        <MatchResultEditor :result="fixture.result" />
                                        <span class="flex-1 text-slate-200 font-medium truncate">
                                            {{ fixture.away_team.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Played weeks (newest first) -->
                            <template v-for="week in sortedWeeks" :key="week">
                                <WeekResults :week="Number(week)" :fixtures="allWeeksResults[week]" />
                            </template>
                        </div>

                        <!-- Predictions -->
                        <div class="lg:col-span-3 overflow-y-auto">
                            <ChampionshipPredictions :predictions="predictions" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fixed Action Bar -->
            <div class="border-t border-slate-800 bg-slate-900/90 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                    <button @click="playAll"
                        :disabled="isFinished"
                        class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-heading font-semibold py-2 px-5 rounded-lg
                               text-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        Play All Weeks
                    </button>

                    <button @click="playNext"
                        :disabled="isFinished"
                        class="bg-slate-700 hover:bg-slate-600 text-white font-heading font-semibold py-2 px-5 rounded-lg
                               text-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        Play Next Week
                    </button>

                    <button @click="resetData"
                        class="bg-transparent border border-red-500/50 hover:bg-red-500/10 text-red-400 font-heading font-semibold py-2 px-5 rounded-lg
                               text-sm transition-all">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LeagueTable from '@/Components/LeagueTable.vue';
import WeekResults from '@/Components/WeekResults.vue';
import ChampionshipPredictions from '@/Components/ChampionshipPredictions.vue';
import MatchResultEditor from '@/Components/MatchResultEditor.vue';

const props = defineProps({
    standings: Array,
    currentWeek: Number,
    totalWeeks: Number,
    weekFixtures: Array,
    allWeeksResults: Object,
    predictions: Array,
    isFinished: Boolean,
});

const sortedWeeks = computed(() =>
    Object.keys(props.allWeeksResults).sort((a, b) => Number(b) - Number(a))
);

function playNext() {
    router.post('/simulation/play-next');
}

function playAll() {
    router.post('/simulation/play-all');
}

function resetData() {
    if (confirm('Reset all results and fixtures?')) {
        router.post('/simulation/reset');
    }
}
</script>
