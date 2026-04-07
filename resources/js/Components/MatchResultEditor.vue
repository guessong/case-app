<template>
    <div v-if="editing" class="flex items-center gap-1 px-2 min-w-[80px] justify-center">
        <input v-model.number="homeScore" type="number" min="0" max="99"
               class="w-10 text-center border rounded py-0.5 text-sm" />
        <span class="text-gray-400">-</span>
        <input v-model.number="awayScore" type="number" min="0" max="99"
               class="w-10 text-center border rounded py-0.5 text-sm" />
        <button @click="save" class="text-green-600 hover:text-green-800 ml-1" title="Save">
            &#10003;
        </button>
        <button @click="editing = false" class="text-red-500 hover:text-red-700" title="Cancel">
            &#10005;
        </button>
    </div>
    <div v-else class="flex items-center gap-1 px-2 min-w-[60px] justify-center cursor-pointer group"
         @click="startEdit">
        <template v-if="result?.is_played">
            <span class="font-bold">{{ result.home_score }}</span>
            <span class="text-gray-400">-</span>
            <span class="font-bold">{{ result.away_score }}</span>
            <span class="text-gray-300 opacity-0 group-hover:opacity-100 ml-1 text-xs">&#9998;</span>
        </template>
        <span v-else class="text-gray-400">-</span>
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
