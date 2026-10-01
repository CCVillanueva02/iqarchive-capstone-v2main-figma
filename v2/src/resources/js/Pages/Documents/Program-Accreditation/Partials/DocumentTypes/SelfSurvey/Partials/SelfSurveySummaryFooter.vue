<!--
================================================================================
IQArchive v2 — Self-Survey Summary Footer & Sign-Off
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SelfSurvey/Partials/SelfSurveySummaryFooter.vue
Role: Renders Total Rating, vibrant emerald Area Mean banner, surveyor sign-off,
      and submission action button.
UI Standard: DaisyUI input, btn, badge (< 110 lines)
================================================================================
-->

<script setup>
import { FileCheck, CheckCircle2, Printer } from 'lucide-vue-next';

defineProps({
    totalRating: { type: [Number, String], default: 0 },
    areaMean: { type: [Number, String], default: '' },
    preparedBy: { type: String, default: '' },
    isComplete: { type: Boolean, default: false },
    isSubmitting: { type: Boolean, default: false },
    lastSaved: { type: String, default: '' },
});

const emit = defineEmits(['updatePreparedBy', 'submit', 'print']);
</script>

<template>
    <div class="space-y-4">
        <!-- Totals Summary Table Footer -->
        <div class="border-t-2 border-slate-300 overflow-hidden rounded-b-2xl shadow-xs">
            <!-- Total Rating Row -->
            <div class="grid grid-cols-[1fr_100px_160px_120px] items-center bg-slate-100 py-3 px-5 border-b border-slate-200">
                <div class="text-right font-extrabold text-xs text-bu-navy-900 uppercase tracking-wider pr-5">
                    Total Rating <span class="text-slate-400 font-normal lowercase">(sum of all IR)</span>
                </div>
                <div class="text-center font-mono font-extrabold text-base text-bu-navy-900 tabular-nums">
                    {{ totalRating || 0 }}
                </div>
                <div></div>
                <div></div>
            </div>

            <!-- Area Mean Vibrant Green Banner -->
            <div class="bg-emerald-600 text-white px-5 py-3.5 flex items-center justify-between shadow-xs">
                <div class="text-xs font-extrabold uppercase tracking-wider flex items-center gap-2">
                    <span>AREA MEAN</span>
                    <span class="text-emerald-100 font-normal text-[11px] lowercase">(mean of all parameter means)</span>
                </div>
                <div class="font-mono font-black text-xl tabular-nums tracking-tight">
                    {{ areaMean || '—' }}
                </div>
            </div>
        </div>

        <!-- Prepared By & Submit Section -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 p-4 bg-white border border-slate-200 rounded-2xl shadow-xs">
            <!-- Left: Prepared By -->
            <div class="flex items-center gap-3">
                <span class="text-xs font-extrabold text-slate-600 uppercase tracking-wider shrink-0">
                    PREPARED BY:
                </span>
                <input
                    :value="preparedBy"
                    @input="emit('updatePreparedBy', $event.target.value)"
                    type="text"
                    placeholder="Enter your name..."
                    class="input input-sm input-bordered w-full sm:w-72 text-xs font-semibold"
                />
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2.5 print:hidden">
                <button
                    type="button"
                    @click="emit('print')"
                    class="btn btn-sm btn-outline border-slate-300 text-slate-700 hover:bg-slate-900 hover:text-white gap-2 font-bold shadow-xs cursor-pointer"
                >
                    <Printer class="w-3.5 h-3.5" />
                    <span>Print / Export</span>
                </button>
                <button
                    type="button"
                    @click="emit('submit')"
                    :disabled="!preparedBy.trim() || isSubmitting"
                    class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white border-none gap-2 font-bold px-5 shadow-xs disabled:opacity-50 cursor-pointer"
                >
                    <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
                    <FileCheck v-else class="w-4 h-4" />
                    <span>Submit Self-Survey</span>
                </button>
            </div>
        </div>
    </div>
</template>
