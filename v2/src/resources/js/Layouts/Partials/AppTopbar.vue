<!--
================================================================================
IQArchive v2 — Master AppTopbar
================================================================================
File: resources/js/Layouts/Partials/AppTopbar.vue
Responsibility: 64px persistent workstation header with multi-tenant scope badge,
                live institutional clock, notification pill, and role indicator.
================================================================================
-->

<script setup>
import { onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Building2, Clock, Bell, ExternalLink, ZoomIn, Check } from 'lucide-vue-next';
import { useDisplayScale } from '@/Composables/useDisplayScale.js';

const { currentScale, availableScales, setScale, initScale } = useDisplayScale();

onMounted(() => {
    initScale();
});

defineProps({
    user: {
        type: Object,
        required: true,
    },
    roleMeta: {
        type: Object,
        required: true,
    },
    systemTime: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between shrink-0 shadow-2xs z-20">
        <!-- Left: Academic Year & Multi-Tenant Scope -->
        <div class="flex items-center gap-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-medium text-slate-700">
                <Building2 class="w-3.5 h-3.5 text-bu-orange-500" />
                <span>Scope: <strong>{{ user.college_id ? `College #${user.college_id}` : 'University-Wide (IQA)' }}</strong></span>
            </div>

            <div class="hidden md:flex items-center gap-2 text-xs text-slate-500 font-medium border-l border-slate-200 pl-4">
                <span>Bicol University</span>
                <span>·</span>
                <span class="text-slate-700 font-semibold">AY 2025–2026</span>
            </div>
        </div>

        <!-- Center: Live Institutional System Time -->
        <div class="hidden lg:flex items-center gap-2 text-xs font-mono text-slate-500 tabular-nums">
            <Clock class="w-3.5 h-3.5 text-slate-400" />
            <span>System Time: {{ systemTime }}</span>
        </div>

        <!-- Right: Scale, Notifications & Role Indicator -->
        <div class="flex items-center gap-2.5">
            <!-- Interactive Display Scale Switcher -->
            <div class="dropdown dropdown-end">
                <button
                    tabindex="0"
                    type="button"
                    class="btn btn-ghost btn-xs gap-1 px-2 text-xs font-medium text-slate-600 hover:text-slate-900 border border-slate-200/80 rounded-lg hover:bg-slate-100 cursor-pointer shadow-2xs"
                    title="Interface Display Scale"
                >
                    <ZoomIn class="w-3.5 h-3.5 text-slate-500" />
                    <span class="font-mono text-[11px] font-semibold">{{ currentScale }}</span>
                </button>
                <ul
                    tabindex="0"
                    class="dropdown-content menu menu-sm bg-white border border-slate-200 rounded-xl z-50 p-1.5 shadow-xl w-48 text-slate-700 mt-1"
                >
                    <li class="menu-title text-[10px] uppercase font-bold text-slate-400 px-2 py-1">
                        Display Scale
                    </li>
                    <li v-for="scale in availableScales" :key="scale.value">
                        <button
                            type="button"
                            :class="[
                                'flex items-center justify-between text-xs py-1.5 rounded-lg cursor-pointer',
                                currentScale === scale.value ? 'active font-semibold bg-bu-blue-50 text-bu-blue-700' : ''
                            ]"
                            @click="setScale(scale.value)"
                        >
                            <span>{{ scale.label }}</span>
                            <Check v-if="currentScale === scale.value" class="w-3.5 h-3.5 text-bu-blue-700" />
                        </button>
                    </li>
                </ul>
            </div>

            <button
                type="button"
                class="btn btn-ghost btn-circle btn-sm relative text-slate-500 hover:text-slate-800 cursor-pointer"
                title="Institutional Notifications"
                aria-label="Institutional Notifications"
            >
                <Bell class="w-4 h-4" />
                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-bu-orange-500 ring-2 ring-white"></span>
            </button>

            <div :class="['badge badge-sm font-semibold', roleMeta.badgeClass]">
                {{ roleMeta.label }}
            </div>

            <!-- Fast Developer Switcher Shortcut -->
            <Link
                href="/dev"
                class="btn btn-outline btn-xs gap-1 text-slate-600 border-slate-300 hover:border-slate-400 hover:bg-slate-100"
                title="Role Switcher Sandbox"
            >
                <ExternalLink class="w-3 h-3" />
                <span class="hidden sm:inline">Switch Role</span>
            </Link>
        </div>
    </header>
</template>
