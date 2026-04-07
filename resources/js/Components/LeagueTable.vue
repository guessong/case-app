<template>
    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-800">
            <h2 class="text-sm font-heading font-semibold text-slate-400 uppercase tracking-wider">League Table</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                    <th class="py-2 px-3 text-left font-medium">#</th>
                    <th class="py-2 px-3 text-left font-medium">Team</th>
                    <th class="py-2 px-3 text-center font-medium">P</th>
                    <th class="py-2 px-3 text-center font-medium">W</th>
                    <th class="py-2 px-3 text-center font-medium">D</th>
                    <th class="py-2 px-3 text-center font-medium">L</th>
                    <th class="py-2 px-3 text-center font-medium">GD</th>
                    <th class="py-2 px-3 text-center font-medium">PTS</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in standings" :key="row.team_id"
                    class="border-t border-slate-800/50 transition-colors hover:bg-slate-800/30"
                    :class="{ 'bg-teal-500/5': index === 0 && row.points > 0 }">
                    <td class="py-2.5 px-3 text-slate-500 text-xs">{{ index + 1 }}</td>
                    <td class="py-2.5 px-3 font-medium text-white">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-4 rounded-full"
                                 :class="index === 0 && row.points > 0 ? 'bg-teal-400' : 'bg-slate-700'"></div>
                            {{ row.team_name }}
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-center text-slate-400">{{ row.played }}</td>
                    <td class="py-2.5 px-3 text-center text-green-400">{{ row.won }}</td>
                    <td class="py-2.5 px-3 text-center text-yellow-400">{{ row.drawn }}</td>
                    <td class="py-2.5 px-3 text-center text-red-400">{{ row.lost }}</td>
                    <td class="py-2.5 px-3 text-center text-slate-300"
                        :class="row.goal_difference > 0 ? 'text-green-400' : row.goal_difference < 0 ? 'text-red-400' : 'text-slate-400'">
                        {{ row.goal_difference > 0 ? '+' : '' }}{{ row.goal_difference }}
                    </td>
                    <td class="py-2.5 px-3 text-center font-bold text-white text-base">{{ row.points }}</td>
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
