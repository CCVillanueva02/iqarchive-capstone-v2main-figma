<!--
================================================================================
IQArchive v2 — Tier Context Card (Tier 1–4 Upper-Right Details Component)
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/Navigation/TierContextCard.vue
Role: Renders adaptive contextual details in the upper-right corner per tier:
      - Tier 1: Program Accreditation overview (17 colleges, AACCUP scope)
      - Tier 2: Selected College details (logo, code, campus, programs count)
      - Tier 3: Selected Program details (code, title, accreditation level)
      - Tier 4: Active Workspace details (category title, target program, mode)
UI Standard: DaisyUI card, badge (< 190 lines).
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { GraduationCap, FolderOpen } from 'lucide-vue-next';

const props = defineProps({
    currentTier: { type: Number, required: true },
    activeCategory: { type: String, default: 'hub' },
    activeCategoryTitle: { type: String, default: '' },
    college: { type: Object, default: null },
    program: { type: Object, default: null },
    programsCount: { type: Number, default: 0 },
    collegesCount: { type: Number, default: 0 },
});

const totalColleges = computed(() => props.collegesCount || 17);
const totalPrograms = computed(() => props.programsCount || props.college?.programs_count || 0);
</script>

<template>
    <!-- Tier 1: Program Accreditation Overview -->
    <div
        v-if="currentTier === 1"
        class="card card-border bg-base-100 border-slate-200 p-3 rounded-2xl shadow-xs flex flex-row items-center justify-between gap-2.5 min-h-24"
    >
        <!-- Left Side: Logo & Program Identity -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="avatar shrink-0">
                <div class="w-14 h-14 mask mask-squircle bg-slate-50 border border-slate-200/80">
                    <img
                        src="/logos/aaccup-logo.png"
                        alt="AACCUP"
                        class="object-contain"
                    />
                </div>
            </div>
            <div class="min-w-0">
                <div class="text-xs font-bold text-slate-900 tracking-tight whitespace-nowrap">
                    AACCUP Accreditation
                </div>
                <div class="text-[11px] font-medium text-slate-500 mt-0.5">
                    AY 2025–2026
                </div>
            </div>
        </div>

        <!-- Right Side: Colleges Stat Counter -->
        <div class="text-right shrink-0 pl-2.5 pr-1.5 border-l border-slate-100">
            <div class="text-lg font-black text-slate-900 tabular-nums leading-none text-center">
                {{ totalColleges }}
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 text-center">
                Colleges
            </div>
        </div>
    </div>

    <!-- Tier 2: Selected College Details -->
    <div
        v-else-if="currentTier === 2 && college"
        class="card card-border bg-base-100 border-slate-200 p-3 rounded-2xl shadow-xs flex flex-row items-center justify-between gap-2.5 min-h-24"
    >
        <!-- Left Side: College Logo & Identity -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="avatar shrink-0">
                <div class="w-14 h-14 mask mask-squircle bg-slate-50 border border-slate-200/80">
                    <img
                        v-if="college.logo_url"
                        :src="college.logo_url"
                        :alt="college.code"
                        class="object-contain"
                    />
                    <GraduationCap v-else class="w-6 h-6 text-bu-blue-700 m-auto" />
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-bold text-slate-900 tracking-tight leading-snug line-clamp-3" :title="college.name">
                    {{ college.name }}
                </div>
            </div>
        </div>

        <!-- Right Side: Programs Stat Counter -->
        <div class="text-right shrink-0 pl-2.5 pr-1.5 border-l border-slate-100">
            <div class="text-lg font-black text-slate-900 tabular-nums leading-none text-center">
                {{ totalPrograms }}
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 text-center">
                {{ totalPrograms === 1 ? 'Program' : 'Programs' }}
            </div>
        </div>
    </div>

    <!-- Tier 3 & Tier 4: Selected Program Details + Document Type (when inside workspace) -->
    <div
        v-else-if="currentTier === 3 && program"
        class="card card-border bg-base-100 border-slate-200 p-3 rounded-2xl shadow-xs flex flex-row items-center gap-2.5 min-h-24"
    >
        <div class="avatar shrink-0">
            <div class="w-14 h-14 mask mask-squircle bg-slate-50 border border-slate-200/80">
                <img
                    v-if="college?.logo_url"
                    :src="college.logo_url"
                    :alt="college.code"
                    class="object-contain"
                />
                <GraduationCap v-else class="w-6 h-6 text-bu-blue-700 m-auto" />
            </div>
        </div>
        <div class="min-w-0 flex-1">
            <div
                class="text-xs font-bold text-slate-900 tracking-tight leading-snug"
                :class="activeCategory !== 'hub' ? 'line-clamp-1' : 'line-clamp-2'"
                :title="program.name"
            >
                {{ program.name }}
            </div>
            <div class="text-[11px] font-medium text-slate-500 mt-0.5 truncate" :title="college?.name">
                {{ college?.name || college?.code }}
            </div>
            <!-- Document Type Indicator (Tier 4) -->
            <div v-if="activeCategory !== 'hub' && activeCategoryTitle" class="mt-1 flex items-center gap-1 min-w-0">
                <span class="badge badge-warning badge-soft text-[10px] font-bold px-2 py-0.5 flex items-center gap-1 truncate max-w-full" :title="activeCategoryTitle">
                    <FolderOpen class="w-3 h-3 shrink-0" />
                    <span class="truncate">{{ activeCategoryTitle }}</span>
                </span>
            </div>
        </div>
    </div>
</template>
