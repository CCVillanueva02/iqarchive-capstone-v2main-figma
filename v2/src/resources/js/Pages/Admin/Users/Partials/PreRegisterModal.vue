<!--
================================================================================
IQArchive v2 — Pre-Register User Modal
================================================================================
File: resources/js/Pages/Admin/Users/Partials/PreRegisterModal.vue
Role Scope: IQA Staff / Administration
Design System: DaisyUI 5 modal, fieldsets, and form controls
================================================================================
-->

<script setup>
import { ref } from 'vue';
import { UserPlus } from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    defaultRole: {
        type: String,
        default: 'Task Force Member',
    },
    roleOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    name: '',
    email: '',
    role: props.defaultRole,
    college: 'College of Science',
    program: 'BS Information Technology',
});

const resetAndClose = () => {
    form.value = {
        name: '',
        email: '',
        role: props.defaultRole,
        college: 'College of Science',
        program: 'BS Information Technology',
    };
    emit('close');
};

const handleSubmit = () => {
    if (!form.value.name || !form.value.email) return;
    emit('submit', { ...form.value });
    resetAndClose();
};
</script>

<template>
    <dialog v-if="isOpen" class="modal modal-open bg-slate-900/40 backdrop-blur-xs">
        <div class="modal-box max-w-lg bg-white p-6 rounded-2xl shadow-xl border border-slate-100">
            <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-bu-orange-50 text-bu-orange-600 flex items-center justify-center shrink-0">
                    <UserPlus class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Pre-Register Institutional User</h3>
                    <p class="text-xs text-slate-500">Assign role and college permissions before Google SSO activation.</p>
                </div>
            </div>

            <form class="space-y-4 pt-4 text-xs" @submit.prevent="handleSubmit">
                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">Full Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="e.g. Dr. Maria Santos"
                        class="input input-bordered input-sm w-full bg-white text-xs focus:outline-bu-orange-500"
                    />
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">Institutional Email (@bicol-u.edu.ph)</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="e.g. msantos@bicol-u.edu.ph"
                        class="input input-bordered input-sm w-full bg-white text-xs focus:outline-bu-orange-500"
                    />
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-slate-700">Role Assignment</label>
                    <select
                        v-model="form.role"
                        class="select select-bordered select-sm w-full bg-white text-xs focus:outline-bu-orange-500"
                    >
                        <option v-for="r in roleOptions" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">College Affiliation</label>
                        <select
                            v-model="form.college"
                            class="select select-bordered select-sm w-full bg-white text-xs focus:outline-bu-orange-500"
                        >
                            <option>College of Science</option>
                            <option>College of Engineering</option>
                            <option>College of Arts and Letters</option>
                            <option>College of Education</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-semibold text-slate-700">Assigned Program</label>
                        <select
                            v-model="form.program"
                            class="select select-bordered select-sm w-full bg-white text-xs focus:outline-bu-orange-500"
                        >
                            <option>BS Information Technology</option>
                            <option>BS Computer Science</option>
                            <option>BS Biology</option>
                            <option>BS Chemistry</option>
                        </select>
                    </div>
                </div>

                <div class="modal-action pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm text-xs text-slate-600"
                        @click="resetAndClose"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none text-xs px-4"
                    >
                        Pre-Register Account
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" @click="resetAndClose">close</button>
        </form>
    </dialog>
</template>
