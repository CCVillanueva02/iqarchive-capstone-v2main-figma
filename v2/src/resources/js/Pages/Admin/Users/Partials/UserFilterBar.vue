<!--
================================================================================
IQArchive v2 — Accounts Management Filter Bar
================================================================================
File: resources/js/Pages/Admin/Users/Partials/UserFilterBar.vue
Role Scope: IQA Staff / Administration
Design System Ref: v1 02-accounts.png Reference & DaisyUI 5
================================================================================
-->

<script setup>
import { Search } from 'lucide-vue-next';

defineProps({
    searchQuery: {
        type: String,
        default: '',
    },
    selectedRole: {
        type: String,
        default: 'all',
    },
    activeTab: {
        type: String,
        default: 'all',
    },
    counts: {
        type: Object,
        default: () => ({ all: 14, active: 14, pending: 0, deactivated: 0 }),
    },
    totalShowing: {
        type: Number,
        default: 14,
    },
    roleOptions: {
        type: Array,
        default: () => [],
    },
});

const tabs = [
    { key: 'all', label: 'All Accounts' },
    { key: 'active', label: 'Active' },
    { key: 'pending', label: 'Pending Activation' },
    { key: 'deactivated', label: 'Deactivated' },
];

defineEmits(['update:searchQuery', 'update:selectedRole', 'update:activeTab']);
</script>

<template>
    <div class="card bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 space-y-4">
        <!-- Top Status Tabs & Total Counter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="bg-slate-100/80 p-1 rounded-xl inline-flex flex-wrap gap-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="text-xs font-medium gap-2 px-3.5 py-1.5 transition-all rounded-lg inline-flex items-center"
                    :class="activeTab === tab.key ? 'bg-white shadow-2xs font-semibold text-slate-900' : 'text-slate-500 hover:text-slate-800'"
                    @click="$emit('update:activeTab', tab.key)"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        class="text-[11px] font-bold px-1.5 py-0.5 rounded-md transition-colors"
                        :class="activeTab === tab.key ? 'bg-slate-100 text-slate-700' : 'bg-transparent text-slate-400'"
                    >
                        {{ counts[tab.key] }}
                    </span>
                </button>
            </div>

            <div class="text-xs font-medium text-slate-400 tabular-nums self-end sm:self-center">
                Showing {{ totalShowing }} accounts total
            </div>
        </div>

        <!-- Search Bar & Role Filter -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-0.5">
            <div class="relative w-full sm:w-80">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                    type="text"
                    :value="searchQuery"
                    placeholder="Search by name or email..."
                    class="input input-bordered input-sm w-full pl-9 bg-white text-xs text-slate-800 rounded-lg border-slate-200 focus:border-slate-400 focus:outline-none"
                    @input="$emit('update:searchQuery', $event.target.value)"
                />
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                <span class="text-xs text-slate-500 whitespace-nowrap">Filter by Role:</span>
                <select
                    :value="selectedRole"
                    class="select select-bordered select-sm text-xs bg-white text-slate-800 rounded-lg min-w-44 border-slate-200 focus:border-slate-400 focus:outline-none"
                    @change="$emit('update:selectedRole', $event.target.value)"
                >
                    <option value="all">All Roles</option>
                    <option v-for="role in roleOptions" :key="role" :value="role">
                        {{ role }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>
