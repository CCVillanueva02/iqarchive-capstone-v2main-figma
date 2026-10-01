<!--
================================================================================
IQArchive v2 — Institutional Self-Survey Parameter Block
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SelfSurvey/Partials/SelfSurveyParameterBlock.vue
Role: Renders single parameter matrix with System, Implementation, Outcome sections,
      interactive IR select dropdowns, computed SIOM means, PM mean, and Best Practices.
UI Standard: DaisyUI table, select, textarea (< 170 lines)
================================================================================
-->

<script setup>
import { computed } from 'vue';

const props = defineProps({
    param: { type: Object, required: true },
    ratings: { type: Object, required: true },
    bestPractices: { type: Object, required: true },
});

const emit = defineEmits(['updateRating', 'updateBestPractice']);

function calculateSectionMean(indicators) {
    if (!indicators || !indicators.length) return null;
    const validIndicators = indicators.filter((ind) => props.ratings[ind.id] !== 'NA');
    if (!validIndicators.length) return null;

    const ratedValues = validIndicators
        .map((ind) => props.ratings[ind.id])
        .filter((v) => v !== null && v !== undefined && v !== '');

    if (!ratedValues.length) return null;
    const sum = ratedValues.reduce((acc, v) => acc + Number(v), 0);
    return (sum / validIndicators.length).toFixed(2);
}

const systemMean = computed(() => calculateSectionMean(props.param?.sections?.system || []));
const implMean = computed(() => calculateSectionMean(props.param?.sections?.implementation || []));
const outcomeMean = computed(() => calculateSectionMean(props.param?.sections?.outcome || []));

const parameterMean = computed(() => {
    const means = [systemMean.value, implMean.value, outcomeMean.value].filter((v) => v !== null);
    if (!means.length) return null;
    const sum = means.reduce((acc, v) => acc + Number(v), 0);
    return (sum / means.length).toFixed(2);
});

function handleRatingChange(indicatorId, event) {
    const raw = event.target.value;
    const val = raw === '' ? null : (raw === 'NA' ? 'NA' : Number(raw));
    emit('updateRating', indicatorId, val);
}

const sections = computed(() => [
    { key: 'system', title: 'SYSTEM – INPUTS AND PROCESSES', label: 'System mean', mean: systemMean.value, list: props.param?.sections?.system || [] },
    { key: 'implementation', title: 'IMPLEMENTATION', label: 'Implementation mean', mean: implMean.value, list: props.param?.sections?.implementation || [] },
    { key: 'outcome', title: 'OUTCOME/S', label: 'Outcome mean', mean: outcomeMean.value, list: props.param?.sections?.outcome || [] },
].filter((s) => s.list.length > 0));
</script>

<template>
    <div class="border-b-2 border-slate-300">
        <!-- Parameter Navy Banner Header -->
        <div class="bg-slate-900 text-white px-5 py-2.5 text-xs font-extrabold uppercase tracking-wide flex items-center justify-between">
            <span>PARAMETER {{ param.code }}: {{ param.title }}</span>
            <span v-if="parameterMean" class="text-emerald-400 font-mono text-xs tabular-nums">
                PM: {{ parameterMean }}
            </span>
        </div>

        <!-- Indicator Sections -->
        <template v-for="section in sections" :key="section.key">
            <!-- Section Subheader -->
            <div class="bg-slate-100/90 border-b border-slate-200 px-5 py-2 text-[11px] font-extrabold text-slate-600 uppercase tracking-wider">
                {{ section.title }}
            </div>

            <!-- Indicator Rows -->
            <div
                v-for="ind in section.list"
                :key="ind.id"
                class="grid grid-cols-[1fr_100px_160px_120px] items-center border-b border-slate-100 hover:bg-slate-50/70 transition-colors text-xs"
            >
                <div class="px-5 py-2.5 border-r border-slate-200 text-slate-800 flex items-start gap-2.5">
                    <span class="font-bold shrink-0 text-slate-500 font-mono">
                        {{ ind.code.endsWith('.') ? ind.code : ind.code + '.' }}
                    </span>
                    <div class="space-y-1">
                        <p class="leading-relaxed">{{ ind.statement }}</p>
                    </div>
                </div>
                <div class="border-r border-slate-200 text-center p-2 flex justify-center">
                    <select
                        :value="ratings[ind.id] ?? ''"
                        @change="handleRatingChange(ind.id, $event)"
                        class="select select-bordered select-xs w-16 bg-white font-mono font-bold text-center text-slate-900 focus:outline-bu-blue-500"
                    >
                        <option value=""></option>
                        <option value="NA">N/A</option>
                        <option v-for="n in [0, 1, 2, 3, 4, 5]" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
                <div class="border-r border-slate-200 p-2"></div>
                <div class="p-2"></div>
            </div>

            <!-- Section Mean Row -->
            <div class="grid grid-cols-[1fr_100px_160px_120px] items-center bg-blue-50/40 border-b border-slate-200 text-xs font-semibold">
                <div class="px-5 py-2 text-right text-slate-400 italic border-r border-slate-200">
                    {{ section.label }}
                </div>
                <div class="border-r border-slate-200 p-1"></div>
                <div class="border-r border-slate-200 text-center py-2 px-3 font-mono font-extrabold text-bu-blue-700 tabular-nums">
                    {{ section.mean ?? '' }}
                </div>
                <div class="p-1"></div>
            </div>
        </template>

        <!-- Parameter Mean Row -->
        <div class="grid grid-cols-[1fr_100px_160px_120px] items-center bg-emerald-50/70 border-b border-slate-200 text-xs">
            <div class="px-5 py-2.5 text-right font-medium text-slate-600 border-r border-slate-200">
                <span class="italic">Parameter Mean — </span>
                <span class="font-bold text-slate-900 not-italic">Parameter {{ param.code }}: {{ param.title }}</span>
            </div>
            <div class="border-r border-slate-200 p-1"></div>
            <div class="border-r border-slate-200 p-1"></div>
            <div class="text-center py-2.5 px-3 font-mono font-extrabold text-emerald-700 text-sm tabular-nums">
                {{ parameterMean ?? '' }}
            </div>
        </div>

        <!-- Best Practices Textarea -->
        <div class="p-5 bg-amber-50/20 border-b border-slate-200">
            <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">
                Best Practices:
            </label>
            <textarea
                :value="bestPractices[param.id] ?? ''"
                @input="emit('updateBestPractice', param.id, $event.target.value)"
                rows="3"
                placeholder="No best practices recorded for this parameter."
                class="textarea textarea-bordered w-full text-xs bg-white focus:outline-bu-blue-500 rounded-xl leading-relaxed"
            ></textarea>
        </div>
    </div>
</template>
