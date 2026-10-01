<!--
================================================================================
IQArchive v2 — Institutional Parameters Sidebar Component
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/Partials/InstitutionalParametersSidebar.vue
Role: Left pane matching Program Accreditation design with active left accent bar & progress %.
UI Standard: DaisyUI / Program Accre Design Fidelity (< 80 lines).
================================================================================
-->

<script setup>
const props = defineProps({
    parameters: { type: Array, default: () => [] },
    selectedIndex: { type: Number, default: 0 },
    evidenceMap: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['select']);

function getParamProgress(param) {
    const benchmarks = param.benchmarks || [];
    if (benchmarks.length === 0) return 0;
    const completed = benchmarks.filter((b) => (props.evidenceMap[b.id] || []).length > 0).length;
    return Math.round((completed / benchmarks.length) * 100);
}
</script>

<template>
    <div class="w-full lg:w-72 shrink-0 flex flex-col gap-3 bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <span class="text-sm font-bold text-slate-900 tracking-wide px-1">Parameters Available</span>
        <div class="flex flex-col gap-1.5">
            <button
                v-for="(param, idx) in parameters"
                :key="param.id"
                type="button"
                @click="emit('select', idx)"
                class="w-full text-left p-3 rounded-xl text-sm font-semibold flex flex-col gap-1 transition cursor-pointer relative overflow-hidden"
                :class="selectedIndex === idx
                    ? 'bg-slate-50 text-slate-900 border-l-4 border-slate-900 pl-2.5 shadow-2xs'
                    : 'text-slate-500 hover:bg-slate-50/50 hover:text-slate-900 pl-3.5 border-l-4 border-transparent'"
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs uppercase tracking-wide"
                        :class="selectedIndex === idx ? 'font-bold text-slate-900' : 'font-bold text-slate-500'"
                    >
                        PARAMETER {{ param.letter }}
                    </span>
                    <span class="text-xs font-bold text-emerald-600 tabular-nums">
                        {{ getParamProgress(param) }}%
                    </span>
                </div>
                <span
                    class="text-xs font-bold leading-snug mt-1 line-clamp-2"
                    :class="selectedIndex === idx ? 'text-slate-900' : 'text-slate-700'"
                >
                    {{ param.name }}
                </span>
            </button>
        </div>
    </div>
</template>
