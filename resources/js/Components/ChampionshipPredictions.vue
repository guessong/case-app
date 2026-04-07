<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-red-600 text-white">
                    <th class="py-2 px-3 text-left font-medium">Championship Predictions</th>
                    <th class="py-2 px-3 text-right font-medium w-16">%</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="prediction in predictions" :key="prediction.team_id"
                    class="border-b border-gray-100 last:border-0">
                    <td class="py-2 px-3 text-gray-800">{{ prediction.team_name }}</td>
                    <td class="py-2 px-3 text-right font-bold"
                        :class="prediction.percentage > 0 ? 'text-red-600' : 'text-gray-400'">
                        {{ prediction.percentage }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    predictions: Array,
});
</script>
