<!--
================================================================================
IQArchive v2 — Parameters Sidebar Component
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/ParametersSidebar.vue
Role: Left pane matching original design with active left accent bar & progress %.
UI Standard: DaisyUI / Original Design (< 80 lines).
================================================================================
-->

<script setup>
const props = defineProps({
    parameters: { type: Array, default: () => [] },
    selectedIndex: { type: Number, default: 0 },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['select']);

function getParamProgress(param) {
    const criteria = param.criteria || [];
    if (criteria.length === 0) return 0;
    const completed = criteria.filter((crit) => {
        return props.documents.some((d) => d.criterion_id === crit.id);
    }).length;
    return Math.round((completed / criteria.length) * 100);
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
                        PARAMETER {{ param.parameter_letter }}
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
