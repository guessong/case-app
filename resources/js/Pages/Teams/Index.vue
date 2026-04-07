<template>
    <AppLayout>
        <template #header>Tournament Teams</template>

        <div class="max-w-3xl mx-auto">
            <!-- Teams Table -->
            <table class="w-full bg-white shadow rounded overflow-hidden">
                <thead>
                    <tr class="bg-gray-700 text-white">
                        <th class="py-3 px-4 text-left font-medium">Team Name</th>
                        <th class="py-3 px-4 text-center font-medium w-24">Power</th>
                        <th class="py-3 px-4 text-center font-medium w-32">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="team in teams" :key="team.id"
                        class="border-b border-gray-200 last:border-0">
                        <!-- View Mode -->
                        <template v-if="editingId !== team.id">
                            <td class="py-3 px-4 text-gray-800">{{ team.name }}</td>
                            <td class="py-3 px-4 text-center text-gray-600">{{ team.power }}</td>
                            <td class="py-3 px-4 text-center space-x-2">
                                <button @click="startEdit(team)"
                                    class="text-teal-600 hover:text-teal-800 text-sm font-medium">
                                    Edit
                                </button>
                                <button @click="deleteTeam(team)"
                                    class="text-red-500 hover:text-red-700 text-sm font-medium">
                                    Delete
                                </button>
                            </td>
                        </template>
                        <!-- Edit Mode -->
                        <template v-else>
                            <td class="py-2 px-4">
                                <input v-model="editForm.name" type="text"
                                    class="w-full border rounded px-2 py-1 text-sm" />
                            </td>
                            <td class="py-2 px-4">
                                <input v-model.number="editForm.power" type="number"
                                    min="1" max="100"
                                    class="w-full border rounded px-2 py-1 text-sm text-center" />
                            </td>
                            <td class="py-2 px-4 text-center space-x-2">
                                <button @click="saveEdit(team)"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Save
                                </button>
                                <button @click="editingId = null"
                                    class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                    Cancel
                                </button>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>

            <!-- Add Team Form -->
            <div class="mt-6 bg-white shadow rounded p-4">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Add Team</h3>
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Team Name</label>
                        <input v-model="addForm.name" type="text" placeholder="e.g. Barcelona"
                            class="w-full border rounded px-3 py-2 text-sm" />
                    </div>
                    <div class="w-24">
                        <label class="block text-xs text-gray-500 mb-1">Power (1-100)</label>
                        <input v-model.number="addForm.power" type="number" min="1" max="100"
                            placeholder="85"
                            class="w-full border rounded px-3 py-2 text-sm text-center" />
                    </div>
                    <button @click="addTeam"
                        :disabled="!addForm.name || !addForm.power"
                        class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-4 rounded
                               text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Add
                    </button>
                </div>
                <p v-if="addError" class="text-red-500 text-xs mt-2">{{ addError }}</p>
            </div>

            <!-- Warning for odd teams -->
            <p v-if="teams.length % 2 !== 0 && teams.length > 0"
               class="mt-4 text-sm text-yellow-600 bg-yellow-50 p-3 rounded">
                Odd number of teams. The fixture generator supports this but some teams will have bye weeks.
            </p>

            <!-- Generate Fixtures -->
            <div class="mt-6 flex items-center gap-4">
                <button @click="generateFixtures"
                    :disabled="loading || teams.length < 2"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-6 rounded
                           transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ loading ? 'Generating...' : 'Generate Fixtures' }}
                </button>
                <span v-if="teams.length < 2" class="text-sm text-gray-400">
                    Need at least 2 teams
                </span>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    teams: Array,
});

const loading = ref(false);
const editingId = ref(null);
const addError = ref('');

const addForm = reactive({ name: '', power: 80 });
const editForm = reactive({ name: '', power: 0 });

function addTeam() {
    addError.value = '';
    router.post('/teams', { ...addForm }, {
        onSuccess: () => {
            addForm.name = '';
            addForm.power = 80;
        },
        onError: (errors) => {
            addError.value = Object.values(errors).flat().join(', ');
        },
    });
}

function startEdit(team) {
    editingId.value = team.id;
    editForm.name = team.name;
    editForm.power = team.power;
}

function saveEdit(team) {
    router.put(`/teams/${team.id}`, { ...editForm }, {
        onSuccess: () => editingId.value = null,
    });
}

function deleteTeam(team) {
    if (confirm(`Delete ${team.name}?`)) {
        router.delete(`/teams/${team.id}`);
    }
}

function generateFixtures() {
    loading.value = true;
    router.post('/fixtures/generate', {}, {
        onFinish: () => loading.value = false,
    });
}
</script>
