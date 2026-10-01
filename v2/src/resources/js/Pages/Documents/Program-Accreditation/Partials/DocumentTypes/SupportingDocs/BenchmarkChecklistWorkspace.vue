<!--
================================================================================
IQArchive v2 — Benchmark Checklist Workspace Component (Old Design Fidelity)
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/BenchmarkChecklistWorkspace.vue
Role: Renders right pane with Parameter header, stats, section tabs, and criteria cards.
UI Standard: DaisyUI / Original Design (< 160 lines).
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import CriterionCard from './CriterionCard.vue';

const props = defineProps({
    parameter: { type: Object, default: null },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['openAttach', 'preview']);

const activeSection = ref('systems'); // 'systems', 'implementation', 'outcomes', 'bestpractices'

const sectionTypeMap = {
    systems: 'system',
    implementation: 'impl',
    outcomes: 'outcome',
    bestpractices: 'best_practice',
};

const currentCriteria = computed(() => {
    if (!props.parameter) return [];
    const expectedType = sectionTypeMap[activeSection.value];
    return (props.parameter.criteria || []).filter((c) => c.type === expectedType);
});

// Stats computations
const totalDocsCount = computed(() => {
    if (!props.parameter) return 0;
    const critIds = (props.parameter.criteria || []).map((c) => c.id);
    return props.documents.filter((d) => critIds.includes(d.criterion_id)).length;
});

const progressPct = computed(() => {
    if (!props.parameter) return 0;
    const criteria = props.parameter.criteria || [];
    if (criteria.length === 0) return 0;
    const completed = criteria.filter((c) => props.documents.some((d) => d.criterion_id === c.id)).length;
    return Math.round((completed / criteria.length) * 100);
});

const verifiedRatio = computed(() => {
    return `${totalDocsCount.value}/${totalDocsCount.value}`;
});

function getCriteriaDocs(criterionId) {
    return props.documents.filter((d) => d.criterion_id === criterionId);
}
</script>

<template>
    <div class="flex-1 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col gap-6 w-full">
        <!-- Parameter Title & Stats Header Block -->
        <div class="flex flex-col gap-4 pb-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        PARAMETER {{ parameter?.parameter_letter }}
                    </span>
                    <h2 class="text-lg font-extrabold text-slate-900 mt-0.5">
                        Parameter {{ parameter?.parameter_letter }} - {{ parameter?.name }}
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

            <!-- Large progress line at the bottom of header block -->
            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-1">
                <div
                    class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                    :style="'width: ' + progressPct + '%'"
                ></div>
            </div>
        </div>

        <!-- Section Navigation Tabs -->
        <div class="flex border-b border-slate-200 gap-8 text-sm font-bold -mt-2 overflow-x-auto">
            <button
                type="button"
                class="pb-3 border-b-2 transition cursor-pointer whitespace-nowrap"
                :class="activeSection === 'systems' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
                @click="activeSection = 'systems'"
            >
                Systems - Inputs & Processes
            </button>
            <button
                type="button"
                class="pb-3 border-b-2 transition cursor-pointer whitespace-nowrap"
                :class="activeSection === 'implementation' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
                @click="activeSection = 'implementation'"
            >
                Implementation
            </button>
            <button
                type="button"
                class="pb-3 border-b-2 transition cursor-pointer whitespace-nowrap"
                :class="activeSection === 'outcomes' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
                @click="activeSection = 'outcomes'"
            >
                Outcomes
            </button>
            <button
                type="button"
                class="pb-3 border-b-2 transition cursor-pointer whitespace-nowrap"
                :class="activeSection === 'bestpractices' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
                @click="activeSection = 'bestpractices'"
            >
                Best Practices
            </button>
        </div>

        <!-- Criteria Cards Stack -->
        <div class="flex flex-col gap-4">
            <CriterionCard
                v-for="criterion in currentCriteria"
                :key="criterion.id"
                :criterion="criterion"
                :documents="getCriteriaDocs(criterion.id)"
                @open-attach="emit('openAttach', $event)"
                @preview="emit('preview', $event)"
            />

            <!-- Empty Section State -->
            <div v-if="currentCriteria.length === 0" class="text-center py-10 text-slate-400 text-xs">
                No benchmarks recorded under this category.
            </div>
        </div>
    </div>
</template>
