<template>
    <AppLayout>
        <template #header>Simulation</template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- League Table -->
            <div class="lg:col-span-5">
                <LeagueTable :standings="standings" />
            </div>

            <!-- Week Results -->
            <div class="lg:col-span-4 space-y-4">
                <!-- Next week to play (unplayed) -->
                <div v-if="!isFinished" class="bg-white shadow rounded overflow-hidden">
                    <div class="bg-gray-700 text-white py-2 px-4 font-bold">
                        Week {{ currentWeek }}
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="fixture in weekFixtures" :key="fixture.id"
                             class="flex items-center justify-between text-gray-800">
                            <span class="flex-1 text-right pr-2 font-medium">
                                {{ fixture.home_team.name }}
                            </span>
                            <MatchResultEditor :result="fixture.result" />
                            <span class="flex-1 pl-2 font-medium">
                                {{ fixture.away_team.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- All played weeks results -->
                <template v-for="(fixtures, week) in allWeeksResults" :key="week">
                    <WeekResults :week="Number(week)" :fixtures="fixtures" />
                </template>
            </div>

            <!-- Championship Predictions -->
            <div class="lg:col-span-3">
                <ChampionshipPredictions :predictions="predictions" />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between mt-8">
            <button @click="playAll"
                :disabled="isFinished"
                class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                       transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Play All Weeks
            </button>

            <button @click="playNext"
                :disabled="isFinished"
                class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                       transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Play Next Week
            </button>

            <button @click="resetData"
                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-6 rounded
                       transition-colors">
                Reset Data
            </button>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LeagueTable from '@/Components/LeagueTable.vue';
import WeekResults from '@/Components/WeekResults.vue';
import ChampionshipPredictions from '@/Components/ChampionshipPredictions.vue';
import MatchResultEditor from '@/Components/MatchResultEditor.vue';

defineProps({
    standings: Array,
    currentWeek: Number,
    totalWeeks: Number,
    weekFixtures: Array,
    allWeeksResults: Object,
    predictions: Array,
    isFinished: Boolean,
});

function playNext() {
    router.post('/simulation/play-next');
}

function playAll() {
    router.post('/simulation/play-all');
}

function resetData() {
    if (confirm('Are you sure you want to reset all data?')) {
        router.post('/simulation/reset');
    }
}
</script>
