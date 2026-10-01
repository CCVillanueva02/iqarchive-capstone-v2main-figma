<!--
================================================================================
IQArchive v2 — Rating Scale Reference Rubric
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SelfSurvey/Partials/RatingScaleReference.vue
Role: Collapsible DaisyUI accordion displaying official AACCUP 1-5 scoring criteria
UI Standard: DaisyUI collapse, table (< 60 lines)
================================================================================
-->

<script setup>
import { RATING_SCALE_RUBRIC } from '../data/institutionalSurveyData';
</script>

<template>
    <div class="collapse collapse-arrow bg-base-100 border border-slate-200 rounded-2xl shadow-xs">
        <input type="checkbox" id="ia-rating-scale-toggle" aria-label="Toggle Rating Scale Reference" />
        <div class="collapse-title text-xs font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2 py-3 px-5">
            <span>Rating Scale Reference</span>
            <span class="badge badge-sm badge-ghost text-[10px] font-normal lowercase">click to expand</span>
        </div>

        <div class="collapse-content px-0 pb-0 border-t border-slate-100">
            <div class="overflow-x-auto">
                <table class="table table-xs w-full text-center border-collapse">
                    <thead class="divide-x divide-slate-200">
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                            <th v-for="item in RATING_SCALE_RUBRIC" :key="item.code" class="py-2.5 px-3 font-extrabold" :class="{ 'text-emerald-700': item.code === '5', 'text-bu-blue-600': ['3', '4'].includes(item.code) }">
                                <span class="text-sm font-mono">{{ item.code }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-white font-bold">
                            <td v-for="item in RATING_SCALE_RUBRIC" :key="item.code" class="py-2 px-3 text-slate-700 text-xs border-r border-slate-100">
                                {{ item.label }}
                            </td>
                        </tr>
                        <tr class="bg-slate-50/50">
                            <td v-for="item in RATING_SCALE_RUBRIC" :key="item.code" class="py-3 px-3 text-slate-500 text-[11px] leading-relaxed align-top max-w-35 text-left border-r border-slate-100">
                                {{ item.description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
