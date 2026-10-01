<!--
===============================================================================
IQArchive v2 — Institutional Recommendation Header Component
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RecommendationHeader.vue
Role: Renders the top recommendation number, title text, status badge, and toggle action.
UI Standard: DaisyUI badge, btn (< 90 lines).
===============================================================================
-->

<script setup>
import { Check, RotateCcw } from 'lucide-vue-next';

defineProps({
    rec: { type: Object, required: true },
    statusConfig: { type: Object, required: true },
});

const emit = defineEmits(['toggleCompleted']);
</script>

<template>
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3.5 flex-1 min-w-0">
            <div
                class="w-7 h-7 rounded-full flex items-center justify-center font-extrabold text-xs shrink-0 border"
                :class="statusConfig.numClass"
            >
                {{ rec.number }}
            </div>
            <h3 class="text-sm font-bold text-slate-900 leading-snug pt-0.5">
                {{ rec.recommendation }}
            </h3>
        </div>

        <!-- Status Indicator & Mark Completed Action -->
        <div class="flex items-center gap-2 shrink-0">
            <span
                class="badge font-bold text-xs py-2 px-3 border select-none transition-all"
                :class="statusConfig.badgeClass"
            >
                {{ statusConfig.label }}
            </span>

            <button
                v-if="rec.status !== 'fully_complied'"
                type="button"
                @click="emit('toggleCompleted')"
                class="btn btn-xs btn-outline btn-success gap-1 font-bold text-emerald-700 border-emerald-300 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-2xs cursor-pointer"
                title="Mark this recommendation as completed"
            >
                <Check class="w-3.5 h-3.5" />
                <span>Mark as Completed</span>
            </button>
            <button
                v-else
                type="button"
                @click="emit('toggleCompleted')"
                class="btn btn-xs btn-ghost text-slate-400 hover:text-slate-600 hover:bg-slate-100 gap-1 text-[11px] cursor-pointer"
                title="Reopen recommendation"
            >
                <RotateCcw class="w-3 h-3" />
                <span>Reopen</span>
            </button>
        </div>
    </div>
</template>
