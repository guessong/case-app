<template>
    <div class="mx-3 min-w-[70px] text-center">
        <!-- Edit Mode -->
        <div v-if="editing"
             class="inline-flex items-center gap-2 bg-slate-900 border border-teal-500/40 px-3 py-1.5 rounded-lg shadow-lg shadow-teal-500/5">
            <input ref="homeInput"
                   v-model.number="homeScore" type="number" min="0" max="99"
                   @keyup.enter="save" @keyup.escape="editing = false"
                   class="w-10 text-center bg-slate-800 border border-slate-600 rounded-md text-sm text-white py-1
                          focus:border-teal-400 focus:outline-none focus:ring-1 focus:ring-teal-400/30" />
            <span class="text-slate-500 font-bold">:</span>
            <input v-model.number="awayScore" type="number" min="0" max="99"
                   @keyup.enter="save" @keyup.escape="editing = false"
                   class="w-10 text-center bg-slate-800 border border-slate-600 rounded-md text-sm text-white py-1
                          focus:border-teal-400 focus:outline-none focus:ring-1 focus:ring-teal-400/30" />
            <div class="flex items-center gap-1 ml-1">
                <button @click="save"
                        class="w-6 h-6 flex items-center justify-center rounded-md bg-teal-500/20 text-teal-400
                               hover:bg-teal-500/30 transition-colors text-xs">
                    &#10003;
                </button>
                <button @click="editing = false"
                        class="w-6 h-6 flex items-center justify-center rounded-md bg-red-500/20 text-red-400
                               hover:bg-red-500/30 transition-colors text-xs">
                    &#10005;
                </button>
            </div>
        </div>

        <!-- View Mode -->
        <template v-else>
            <span v-if="result?.is_played"
                  @click="startEdit"
                  class="inline-flex items-center gap-1.5 bg-slate-900 px-2.5 py-1 rounded-md
                         font-mono font-bold text-white text-xs cursor-pointer
                         hover:bg-slate-700 hover:border-teal-500/30 border border-transparent
                         transition-all group">
                {{ result.home_score }} - {{ result.away_score }}
                <span class="text-slate-600 opacity-0 group-hover:opacity-100 text-[10px] transition-opacity">&#9998;</span>
            </span>
            <span v-else class="text-slate-600">vs</span>
        </template>
    </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    result: Object,
});

const editing = ref(false);
const homeScore = ref(0);
const awayScore = ref(0);
const homeInput = ref(null);

function startEdit() {
    if (!props.result?.is_played) return;
    homeScore.value = props.result.home_score;
    awayScore.value = props.result.away_score;
    editing.value = true;
    nextTick(() => homeInput.value?.select());
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
