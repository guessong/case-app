<template>
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="py-2 px-3 text-left font-medium">Team Name</th>
                    <th class="py-2 px-3 text-center font-medium w-10">P</th>
                    <th class="py-2 px-3 text-center font-medium w-10">W</th>
                    <th class="py-2 px-3 text-center font-medium w-10">D</th>
                    <th class="py-2 px-3 text-center font-medium w-10">L</th>
                    <th class="py-2 px-3 text-center font-medium w-12">GD</th>
                    <th class="py-2 px-3 text-center font-medium w-12">PTS</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in standings" :key="row.team_id"
                    class="border-b border-gray-100 last:border-0"
                    :class="{ 'bg-green-50': index === 0 && row.points > 0 }">
                    <td class="py-2 px-3 font-medium text-gray-800">{{ row.team_name }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.played }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.won }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.drawn }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.lost }}</td>
                    <td class="py-2 px-3 text-center text-gray-600">{{ row.goal_difference }}</td>
                    <td class="py-2 px-3 text-center font-bold text-gray-800">{{ row.points }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    standings: Array,
});
</script>
