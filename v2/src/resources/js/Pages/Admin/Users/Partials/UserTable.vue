<!--
================================================================================
IQArchive v2 — Accounts Management User Table
================================================================================
File: resources/js/Pages/Admin/Users/Partials/UserTable.vue
Role Scope: IQA Staff / Administration
Design System Ref: v1 02-accounts.png Reference & DaisyUI 5
================================================================================
-->

<script setup>
import { SquarePen, Ban, ChevronLeft, ChevronRight, Shield } from 'lucide-vue-next';

defineProps({
    users: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    totalPages: { type: Number, default: 2 },
    totalUsers: { type: Number, default: 14 },
    showingStart: { type: Number, default: 1 },
    showingEnd: { type: Number, default: 10 },
});

defineEmits(['editUser', 'toggleUserStatus', 'pageChange']);

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.trim().split(/\s+/);
    return parts.length >= 2 ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
};

const ROLE_STYLES = {
    'Task Force Member': 'background-color: #FEF3C7; color: #92400E; border-color: #FDE68A;',
    'AACCUP Accreditor': 'background-color: #D1FAE5; color: #065F46; border-color: #A7F3D0;',
    'BU Executive': 'background-color: #E0E7FF; color: #3730A3; border-color: #C7D2FE;',
    'College Head': 'background-color: #E0F2FE; color: #075985; border-color: #BAE6FD;',
};
</script>

<template>
    <div class="card bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 space-y-4">
        <div class="overflow-x-auto">
            <table class="table table-pin-rows w-full text-xs">
                <thead>
                    <tr class="text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                        <th class="py-3.5 pl-2">Institutional User</th>
                        <th class="py-3.5">Email Address</th>
                        <th class="py-3.5">Role Assignment</th>
                        <th class="py-3.5">College / Program</th>
                        <th class="py-3.5">Status</th>
                        <th class="py-3.5">Registered Date</th>
                        <th class="py-3.5 pr-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                        <!-- User with Squircle Avatar & Green Indicator (Matching Reference Image) -->
                        <td class="py-3.5 pl-2">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0 w-8 h-8">
                                    <img
                                        v-if="user.avatar_url"
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="w-8 h-8 rounded-xl object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-8 h-8 rounded-xl font-bold flex items-center justify-center text-xs"
                                        style="background-color: #ECEFF4; color: #1E293B;"
                                    >
                                        {{ getInitials(user.name) }}
                                    </div>
                                    <!-- Active indicator on bottom-right with vibrant green color -->
                                    <span
                                        v-if="user.status === 'active'"
                                        class="w-2.5 h-2.5 rounded-full absolute z-10 pointer-events-none"
                                        style="bottom: -2px; right: -2px; background-color: #10B981;"
                                    ></span>
                                </div>
                                <div class="font-bold text-slate-900 whitespace-nowrap text-xs">{{ user.name }}</div>
                            </div>
                        </td>

                        <!-- Email Address -->
                        <td class="py-3.5 font-mono text-[11px] text-slate-600 whitespace-nowrap">{{ user.email }}</td>

                        <!-- Role Assignment Pill (Exact v1 reference colors) -->
                        <td class="py-3.5 whitespace-nowrap">
                            <span class="font-medium text-[11px] px-3 py-0.5 rounded-full inline-block border" :style="ROLE_STYLES[user.role] || 'background-color: #F1F5F9; color: #334155; border-color: #E2E8F0;'">
                                {{ user.role }}
                            </span>
                        </td>

                        <!-- College / Program -->
                        <td class="py-3.5 whitespace-nowrap">
                            <span v-if="user.is_institutional" class="text-slate-400 italic text-xs font-sans">Institutional</span>
                            <div v-else class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-50 border border-slate-200/80 font-mono text-[11px] text-slate-700">
                                <Shield class="w-3.5 h-3.5 shrink-0" :class="user.college_code === 'CENG' ? 'text-amber-500 fill-amber-100' : 'text-blue-600 fill-blue-100'" />
                                <span class="font-bold text-slate-800">{{ user.college_code }}</span>
                                <template v-if="user.program_code">
                                    <span class="text-slate-300">/</span>
                                    <span class="text-slate-600">{{ user.program_code }}</span>
                                </template>
                            </div>
                        </td>

                        <!-- Status Pill (Exact v1 reference colors) -->
                        <td class="py-3.5 whitespace-nowrap">
                            <span class="text-xs font-semibold px-3.5 py-0.5 rounded-full inline-block border text-center" style="border-color: #059669; background-color: #ECFDF5; color: #059669;">
                                Active
                            </span>
                        </td>

                        <!-- Registered Date -->
                        <td class="py-3.5 whitespace-nowrap">
                            <div class="font-semibold text-slate-800 text-xs">{{ user.created_at }}</div>
                            <div class="text-[11px] text-slate-400">{{ user.created_ago }}</div>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 pr-2 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1">
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs btn-square hover:bg-slate-100 transition-colors"
                                    style="color: #092c75;"
                                    title="Edit account details"
                                    @click="$emit('editUser', user)"
                                >
                                    <SquarePen
                                        color="#092c75"
                                        :size="16"
                                        :stroke-width="2"
                                    />
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs btn-square text-rose-400 hover:text-rose-600 hover:bg-rose-50" title="Deactivate account" @click="$emit('toggleUserStatus', user)">
                                    <Ban class="w-4 h-4 stroke-[1.75]" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div class="text-xs text-slate-500">
                Showing {{ showingStart }} to {{ showingEnd }} of {{ totalUsers }} results
            </div>

            <div class="join border border-slate-200 rounded-lg overflow-hidden bg-white">
                <button type="button" class="btn btn-xs join-item bg-white border-0 text-slate-500 hover:bg-slate-50 px-2.5" :disabled="currentPage === 1" @click="$emit('pageChange', currentPage - 1)">
                    <ChevronLeft class="w-3.5 h-3.5" />
                </button>
                <button v-for="page in totalPages" :key="page" type="button" class="btn btn-xs join-item border-y-0 border-r-0 text-xs font-semibold px-3" :class="currentPage === page ? 'bg-slate-50 text-slate-900 border-l border-slate-200' : 'bg-white text-slate-500 hover:bg-slate-50 border-l border-slate-200'" @click="$emit('pageChange', page)">
                    {{ page }}
                </button>
                <button type="button" class="btn btn-xs join-item bg-white border-0 text-slate-500 hover:bg-slate-50 px-2.5 border-l border-slate-200" :disabled="currentPage === totalPages" @click="$emit('pageChange', currentPage + 1)">
                    <ChevronRight class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
