<!--
================================================================================
IQArchive v2 — Evidence Context Banner
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/Partials/EvidenceContextBanner.vue
Role: Displays area, parameter, and criterion benchmark context inside upload modal.
UI Standard: DaisyUI / Old Design Fidelity (< 50 lines).
================================================================================
-->

<script setup>
defineProps({
    parameter: { type: Object, default: null },
    criterion: { type: Object, default: null },
});
</script>

<template>
    <div v-if="criterion" class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mt-3 flex flex-col gap-1 text-xs">
        <div class="flex items-center gap-1.5 font-bold text-slate-600 text-[11px]">
            <span>Parameter {{ parameter?.parameter_letter }} - {{ parameter?.name }}</span>
        </div>
        <div class="flex items-start gap-2 text-slate-700">
            <span class="font-bold text-slate-900 bg-white border border-slate-200 px-2 py-0.5 rounded text-[11px] font-mono shrink-0">
                {{ criterion?.benchmark_code }}
            </span>
            <p class="line-clamp-2 leading-snug">{{ criterion?.title }}</p>
        </div>
    </div>
</template>
