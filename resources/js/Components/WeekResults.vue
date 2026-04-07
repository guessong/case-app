<template>
    <div class="bg-slate-800/50 rounded-lg border border-slate-700/50 overflow-hidden">
        <div class="px-3 py-2 bg-slate-800 border-b border-slate-700/50">
            <span class="text-xs font-heading font-semibold text-slate-400 uppercase tracking-wider">Week {{ week }}</span>
        </div>
        <div class="divide-y divide-slate-700/30">
            <div v-for="fixture in fixtures" :key="fixture.id"
                 class="px-3 py-2 flex items-center text-sm">
                <span class="flex-1 text-right text-slate-200 font-medium truncate">{{ fixture.home_team.name }}</span>
                <div class="mx-3 min-w-[56px] text-center">
                    <template v-if="fixture.result?.is_played">
                        <span class="inline-flex items-center gap-1.5 bg-slate-900 px-2.5 py-0.5 rounded-md font-mono font-bold text-white text-xs">
                            {{ fixture.result.home_score }} - {{ fixture.result.away_score }}
                        </span>
                    </template>
                    <span v-else class="text-slate-600">vs</span>
                </div>
                <span class="flex-1 text-slate-200 font-medium truncate">{{ fixture.away_team.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    week: Number,
    fixtures: Array,
});
</script>
