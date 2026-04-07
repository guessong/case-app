<template>
    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-800">
            <h2 class="text-sm font-heading font-semibold text-slate-400 uppercase tracking-wider">Predictions</h2>
        </div>
        <div class="p-3 space-y-2">
            <div v-for="prediction in predictions" :key="prediction.team_id"
                 class="relative overflow-hidden rounded-lg bg-slate-800/50 px-3 py-2.5">
                <div class="absolute inset-y-0 left-0 bg-teal-500/15 transition-all duration-500"
                     :style="{ width: prediction.percentage + '%' }"></div>
                <div class="relative flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-200">{{ prediction.team_name }}</span>
                    <span class="text-sm font-bold font-mono"
                          :class="prediction.percentage > 0 ? 'text-teal-400' : 'text-slate-600'">
                        {{ prediction.percentage }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    predictions: Array,
});
</script>
