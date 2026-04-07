<template>
    <AppLayout>
        <template #header>Tournament Teams</template>

        <div class="max-w-2xl mx-auto">
            <table class="w-full bg-white shadow rounded overflow-hidden">
                <thead>
                    <tr class="bg-gray-700 text-white">
                        <th class="py-3 px-6 text-left font-medium">Team Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="team in teams" :key="team.id"
                        class="border-b border-gray-200 last:border-0">
                        <td class="py-3 px-6 text-gray-800">{{ team.name }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-6">
                <button @click="generateFixtures"
                    :disabled="loading"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                           transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ loading ? 'Generating...' : 'Generate Fixtures' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    teams: Array,
});

const loading = ref(false);

function generateFixtures() {
    loading.value = true;
    router.post('/fixtures/generate', {}, {
        onFinish: () => loading.value = false,
    });
}
</script>
