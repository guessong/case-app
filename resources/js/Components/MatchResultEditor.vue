<template>
    <div class="mx-3 min-w-[56px] text-center">
        <div v-if="editing" class="inline-flex items-center gap-1 bg-slate-900 px-1.5 py-0.5 rounded-md">
            <input v-model.number="homeScore" type="number" min="0" max="99"
                   class="w-8 text-center bg-slate-800 border border-slate-600 rounded text-xs text-white py-0.5" />
            <span class="text-slate-500 text-xs">-</span>
            <input v-model.number="awayScore" type="number" min="0" max="99"
                   class="w-8 text-center bg-slate-800 border border-slate-600 rounded text-xs text-white py-0.5" />
            <button @click="save" class="text-teal-400 hover:text-teal-300 text-xs ml-0.5">&#10003;</button>
            <button @click="editing = false" class="text-red-400 hover:text-red-300 text-xs">&#10005;</button>
        </div>
        <template v-else>
            <span v-if="result?.is_played"
                  @click="startEdit"
                  class="inline-flex items-center gap-1.5 bg-slate-900 px-2.5 py-0.5 rounded-md font-mono font-bold text-white text-xs cursor-pointer hover:bg-slate-800 transition-colors group">
                {{ result.home_score }} - {{ result.away_score }}
                <span class="text-slate-600 opacity-0 group-hover:opacity-100 text-[10px]">&#9998;</span>
            </span>
            <span v-else class="text-slate-600">vs</span>
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    result: Object,
});

const editing = ref(false);
const homeScore = ref(0);
const awayScore = ref(0);

function startEdit() {
    if (!props.result?.is_played) return;
    homeScore.value = props.result.home_score;
    awayScore.value = props.result.away_score;
    editing.value = true;
}

function save() {
    router.put(`/matches/${props.result.id}`, {
        home_score: homeScore.value,
        away_score: awayScore.value,
    }, {
        onSuccess: () => editing.value = false,
    });
}
</script>
