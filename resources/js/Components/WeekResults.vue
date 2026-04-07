<template>
    <div class="bg-slate-800/50 rounded-lg border border-slate-700/50 overflow-hidden">
        <div class="px-3 py-2 bg-slate-800 border-b border-slate-700/50">
            <span class="text-xs font-heading font-semibold text-slate-400 uppercase tracking-wider">Week {{ week }}</span>
        </div>
        <div class="divide-y divide-slate-700/30">
            <div v-for="fixture in fixtures" :key="fixture.id"
                 class="px-3 py-2 flex items-center text-sm">
                <span class="flex-1 text-right text-slate-200 font-medium truncate">{{ fixture.home_team.name }}</span>
                <MatchResultEditor :result="fixture.result" />
                <span class="flex-1 text-slate-200 font-medium truncate">{{ fixture.away_team.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import MatchResultEditor from '@/Components/MatchResultEditor.vue';

defineProps({
    week: Number,
    fixtures: Array,
});
</script>
