<!--
================================================================================
IQArchive v2 — User Profile & Institutional Identity
================================================================================
File: resources/js/Pages/Auth/AccountSettings.vue
Responsibility: Displays verified identity, role, and session info for the
                authenticated user without tabs or redundant customization controls.
Architecture: Inertia SPA Page (<AppShell> + DaisyUI Components)
================================================================================
-->

<script setup>
import { Head } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import { Mail, Building, CheckCircle2, ShieldCheck, Calendar, History, Laptop, Lock } from 'lucide-vue-next';

defineProps({
    profile: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="User Profile" />

    <AppShell>
        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Profile</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Institutional account details and authenticated identity for Bicol University personnel.
                </p>
            </div>

            <!-- Profile & Identity Card (DaisyUI Card) -->
            <div class="card card-border bg-base-100 shadow-xs">
                <div class="card-body p-6 md:p-8 space-y-6">
                    <!-- Top Identity Row -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div class="flex items-center gap-4">
                            <!-- Institutional Avatar with Fallback Initials -->
                            <div class="avatar placeholder">
                                <div class="w-16 h-16 rounded-2xl bg-[#0B1B3D] text-white font-bold text-xl ring-2 ring-[#F26522]/30 shadow-inner">
                                    <img v-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.name" class="object-cover" />
                                    <span v-else>{{ profile.name ? profile.name.charAt(0).toUpperCase() : 'U' }}</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ profile.name }}</h2>
                                    <div class="badge badge-success badge-soft badge-sm gap-1 text-emerald-700 font-medium">
                                        <CheckCircle2 class="w-3 h-3" />
                                        <span>Google SSO Verified</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
                                    <Mail class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span>{{ profile.email }}</span>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <Building class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span>{{ profile.department || 'Internal Quality Assurance Office' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Status Badge -->
                        <div class="badge badge-outline border-emerald-600/30 text-emerald-700 bg-emerald-50/50 font-semibold text-xs px-3 py-2 gap-1.5 self-start sm:self-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="capitalize">Account Status: {{ profile.status || 'Active' }}</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <ShieldCheck class="w-3.5 h-3.5 text-[#0038A8]" />
                                <span>Assigned Roles & Permissions</span>
                            </span>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <div class="badge bg-[#0B1B3D] text-white border-none font-semibold text-xs px-2.5 py-1.5">
                                    {{ profile.role ? profile.role.replace('_', ' ').toUpperCase() : 'IQA STAFF' }}
                                </div>
                                <template v-if="profile.roles">
                                    <span v-for="r in profile.roles.filter(x => x !== profile.role)" :key="r" class="badge badge-outline text-xs px-2.5 py-1.5 text-slate-600">
                                        {{ r.replace('_', ' ') }}
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Calendar class="w-3.5 h-3.5 text-[#F26522]" />
                                <span>Member Since</span>
                            </span>
                            <div class="text-sm font-semibold text-slate-800 pt-0.5">
                                {{ profile.joined_at || 'Academic Year 2026' }}
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <History class="w-3.5 h-3.5 text-[#0038A8]" />
                                <span>Last Authenticated Session</span>
                            </span>
                            <div class="text-sm font-semibold text-slate-800 pt-0.5 font-mono text-xs">
                                {{ profile.last_sign_in || 'Active Session' }}
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <Laptop class="w-3.5 h-3.5 text-slate-500" />
                                <span>Session Network Origin</span>
                            </span>
                            <div class="text-sm font-semibold text-slate-800 pt-0.5 font-mono text-xs">
                                {{ profile.ip_address || '127.0.0.1' }}
                            </div>
                        </div>
                    </div>

                    <!-- Institutional Identity Governance Note -->
                    <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/60 text-xs text-slate-500 flex items-start gap-2.5 leading-relaxed">
                        <Lock class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                        <span>
                            Your account profile and login credentials are governed centrally by Bicol University Google Workspace. Password changes, Multi-Factor Authentication (MFA), and official name corrections must be processed through the BU ICTO Service Portal.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
