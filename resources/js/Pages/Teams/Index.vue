<template>
    <AppLayout>
        <template #header>Tournament Teams</template>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Teams Table -->
            <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4 text-left font-medium">Team Name</th>
                            <th class="py-3 px-4 text-center font-medium w-24">Power</th>
                            <th class="py-3 px-4 text-center font-medium w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="team in teams" :key="team.id"
                            class="border-t border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                            <template v-if="editingId !== team.id">
                                <td class="py-3 px-4 text-white font-medium">{{ team.name }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center bg-slate-800 px-2.5 py-0.5 rounded-md font-mono text-sm text-teal-400">
                                        {{ team.power }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center space-x-3">
                                    <button @click="startEdit(team)" class="text-slate-400 hover:text-teal-400 text-xs font-medium transition-colors">Edit</button>
                                    <button @click="deleteTeam(team)" class="text-slate-400 hover:text-red-400 text-xs font-medium transition-colors">Delete</button>
                                </td>
                            </template>
                            <template v-else>
                                <td class="py-2 px-4">
                                    <input v-model="editForm.name" type="text"
                                        class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-sm text-white focus:border-teal-500 focus:outline-none" />
                                </td>
                                <td class="py-2 px-4">
                                    <input v-model.number="editForm.power" type="number" min="1" max="100"
                                        class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-sm text-white text-center focus:border-teal-500 focus:outline-none" />
                                </td>
                                <td class="py-2 px-4 text-center space-x-3">
                                    <button @click="saveEdit(team)" class="text-teal-400 hover:text-teal-300 text-xs font-medium">Save</button>
                                    <button @click="editingId = null" class="text-slate-500 hover:text-slate-300 text-xs font-medium">Cancel</button>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add Team -->
            <div class="mt-4 bg-slate-900 rounded-xl border border-slate-800 p-4">
                <h3 class="text-xs font-heading font-semibold text-slate-500 uppercase tracking-wider mb-3">Add Team</h3>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs text-slate-500 mb-1">Name</label>
                        <input v-model="addForm.name" type="text" placeholder="e.g. Barcelona"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600 focus:border-teal-500 focus:outline-none" />
                    </div>
                    <div class="w-24">
                        <label class="block text-xs text-slate-500 mb-1">Power</label>
                        <input v-model.number="addForm.power" type="number" min="1" max="100" placeholder="85"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white text-center placeholder-slate-600 focus:border-teal-500 focus:outline-none" />
                    </div>
                    <button @click="addTeam"
                        :disabled="!addForm.name || !addForm.power"
                        class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-heading font-semibold py-2 px-4 rounded-lg
                               text-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        Add
                    </button>
                </div>
                <p v-if="addError" class="text-red-400 text-xs mt-2">{{ addError }}</p>
            </div>

            <!-- Warning -->
            <p v-if="teams.length % 2 !== 0 && teams.length > 0"
               class="mt-4 text-sm text-amber-400 bg-amber-500/10 border border-amber-500/20 p-3 rounded-lg">
                Odd number of teams — some teams will have bye weeks.
            </p>

            <!-- Generate -->
            <div class="mt-6 flex items-center gap-4">
                <button @click="generateFixtures"
                    :disabled="loading || teams.length < 2"
                    class="bg-teal-500 hover:bg-teal-400 text-slate-950 font-heading font-semibold py-2.5 px-6 rounded-lg
                           text-sm transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                    {{ loading ? 'Generating...' : 'Generate Fixtures' }}
                </button>
                <span v-if="teams.length < 2" class="text-xs text-slate-600">Need at least 2 teams</span>
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
