<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <div class="bg-gray-700 text-white py-2 px-4 font-bold">
            Week {{ week }}
        </div>
        <div class="p-4 space-y-3">
            <div v-for="fixture in fixtures" :key="fixture.id"
                 class="flex items-center justify-between text-gray-800">
                <span class="flex-1 text-right pr-2 font-medium">{{ fixture.home_team.name }}</span>
                <div class="flex items-center gap-1 px-2 min-w-[60px] justify-center">
                    <template v-if="fixture.result?.is_played">
                        <span class="font-bold">{{ fixture.result.home_score }}</span>
                        <span class="text-gray-400">-</span>
                        <span class="font-bold">{{ fixture.result.away_score }}</span>
                    </template>
                    <span v-else class="text-gray-400">-</span>
                </div>
                <span class="flex-1 pl-2 font-medium">{{ fixture.away_team.name }}</span>
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
