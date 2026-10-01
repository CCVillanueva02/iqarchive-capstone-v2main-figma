<!--
================================================================================
IQArchive v2 — Institutional Benchmark Checklist Workspace Component
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/Partials/InstitutionalBenchmarkChecklistWorkspace.vue
Role: Renders right pane with Parameter header, stats, section tabs, and criteria cards.
UI Standard: DaisyUI / Program Accre Design Fidelity (< 160 lines).
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import InstitutionalCriterionCard from './InstitutionalCriterionCard.vue';

const props = defineProps({
    parameter: { type: Object, default: null },
    evidenceMap: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['openAttach', 'preview', 'deleteDoc']);

const activeSection = ref('systems');

const sectionTypeMap = {
    systems: 'system',
    implementation: 'impl',
    outcomes: 'outcome',
    bestpractices: 'best_practice',
};

const currentBenchmarks = computed(() => {
    if (!props.parameter) return [];
    const expectedType = sectionTypeMap[activeSection.value];
    return (props.parameter.benchmarks || []).filter((b) => b.type === expectedType);
});

// Stats computations
const totalDocsCount = computed(() => {
    if (!props.parameter) return 0;
    const bmIds = (props.parameter.benchmarks || []).map((b) => b.id);
    return bmIds.reduce((sum, id) => sum + (props.evidenceMap[id] || []).length, 0);
});

const progressPct = computed(() => {
    if (!props.parameter) return 0;
    const benchmarks = props.parameter.benchmarks || [];
    if (benchmarks.length === 0) return 0;
    const completed = benchmarks.filter((b) => (props.evidenceMap[b.id] || []).length > 0).length;
    return Math.round((completed / benchmarks.length) * 100);
});

const verifiedRatio = computed(() => {
    return `${totalDocsCount.value}/${totalDocsCount.value}`;
});

function getBenchmarkDocs(benchmarkId) {
    return props.evidenceMap[benchmarkId] || [];
}
</script>

<template>
    <div class="flex-1 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col gap-6 w-full">
        <!-- Parameter Title & Stats Header Block -->
        <div class="flex flex-col gap-4 pb-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        PARAMETER {{ parameter?.letter }}
                    </span>
                    <h2 class="text-lg font-extrabold text-slate-900 mt-0.5">
                        Parameter {{ parameter?.letter }} - {{ parameter?.name }}
                    </h2>
                </div>

                <!-- Stats Grid -->
                <div class="flex items-center gap-6 shrink-0 text-right">
                    <div>
                        <div class="text-base font-extrabold text-slate-900 tabular-nums">
                            {{ totalDocsCount }}
                        </div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-0.5">
                            Documents
                        </div>
                    </div>

                    <div class="border-l border-slate-200 h-8"></div>

                    <div>
                        <div class="text-base font-extrabold text-slate-900 tabular-nums">
                            {{ verifiedRatio }}
                        </div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-0.5">
                            Verified
                        </div>
                    </div>

                    <div class="border-l border-slate-200 h-8"></div>

                    <div>
                        <div class="text-base font-extrabold text-emerald-600 tabular-nums">
                            {{ progressPct }}%
                        </div>
                        <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-0.5">
                            Progress
                        </div>
                    </div>
                </div>
            </div>

            <!-- Big Horizontal Progress Bar -->
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div
                    class="bg-emerald-500 h-full rounded-full transition-all duration-300"
                    :style="'width: ' + progressPct + '%'"
                ></div>
            </div>
        </div>

        <!-- Category Navigation Tabs -->
        <div class="flex items-center gap-8 border-b border-slate-200 text-sm font-semibold select-none overflow-x-auto">
            <button
                type="button"
                @click="activeSection = 'systems'"
                class="pb-3 border-b-2 transition whitespace-nowrap cursor-pointer"
                :class="activeSection === 'systems' ? 'border-bu-orange-500 text-slate-900 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600'"
            >
                Systems - Inputs & Processes
            </button>
            <button
                type="button"
                @click="activeSection = 'implementation'"
                class="pb-3 border-b-2 transition whitespace-nowrap cursor-pointer"
                :class="activeSection === 'implementation' ? 'border-bu-orange-500 text-slate-900 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600'"
            >
                Implementation
            </button>
            <button
                type="button"
                @click="activeSection = 'outcomes'"
                class="pb-3 border-b-2 transition whitespace-nowrap cursor-pointer"
                :class="activeSection === 'outcomes' ? 'border-bu-orange-500 text-slate-900 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600'"
            >
                Outcomes
            </button>
            <button
                type="button"
                @click="activeSection = 'bestpractices'"
                class="pb-3 border-b-2 transition whitespace-nowrap cursor-pointer"
                :class="activeSection === 'bestpractices' ? 'border-bu-orange-500 text-slate-900 font-bold' : 'border-transparent text-slate-400 hover:text-slate-600'"
            >
                Best Practices
            </button>
        </div>

        <!-- Benchmarks Checklist Cards Stack -->
        <div class="flex flex-col gap-4">
            <InstitutionalCriterionCard
                v-for="benchmark in currentBenchmarks"
                :key="benchmark.id"
                :benchmark="benchmark"
                :documents="getBenchmarkDocs(benchmark.id)"
                @open-attach="emit('openAttach', $event)"
                @preview="emit('preview', $event)"
                @delete-doc="emit('deleteDoc', $event)"
            />

            <!-- Empty State for Section -->
            <div v-if="currentBenchmarks.length === 0" class="p-8 text-center text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-slate-200/60">
                No benchmark criteria defined for this section.
            </div>
        </div>
    </div>
</template>
