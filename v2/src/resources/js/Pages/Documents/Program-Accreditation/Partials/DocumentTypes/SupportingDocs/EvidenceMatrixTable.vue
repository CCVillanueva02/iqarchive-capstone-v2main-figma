<!--
================================================================================
IQArchive v2 — Evidence Matrix Table Component
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/EvidenceMatrixTable.vue
Role: Renders the 3-column AACCUP/IA evidence matrix table.
UI Standard: DaisyUI table, table-zebra, table-pin-rows, badge, btn (< 170 lines).
================================================================================
-->

<script setup>
import { Plus, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import EvidenceItemPill from './EvidenceItemPill.vue';

const props = defineProps({
    criteria: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    parameter: { type: Object, default: null },
});

const emit = defineEmits(['openAttach', 'preview']);

function getAttachedDocs(criterionId) {
    return props.documents.filter((d) => d.criterion_id === criterionId);
}

function getBenchmarkTypeBadge(type) {
    switch (type) {
        case 'system': return { label: 'System (Inputs)', class: 'badge-info badge-soft' };
        case 'impl': return { label: 'Implementation', class: 'badge-warning badge-soft' };
        case 'outcome': return { label: 'Outcome', class: 'badge-success badge-soft' };
        case 'best_practice': return { label: 'Best Practice', class: 'badge-primary badge-soft' };
        default: return { label: type, class: 'badge-neutral badge-soft' };
    }
}
</script>

<template>
    <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-2xs">
        <table class="table table-zebra table-pin-rows w-full text-xs">
            <thead class="bg-slate-50 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="w-[38%] py-3.5 pl-4">Benchmark Indicator & Standard</th>
                    <th class="w-[32%] py-3.5">Expected Supporting Documents</th>
                    <th class="w-[30%] py-3.5 pr-4">Attached Evidence & Links</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-for="criterion in criteria" :key="criterion.id" class="hover:bg-blue-50/20 transition-colors align-top">
                    <!-- Column 1: Benchmark Code & Statement -->
                    <td class="py-4 pl-4 pr-3">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-neutral text-xs font-mono font-bold px-2 py-0.5 shrink-0">
                                    {{ criterion.benchmark_code }}
                                </span>
                                <span class="badge text-[10px] font-medium" :class="getBenchmarkTypeBadge(criterion.type).class">
                                    {{ getBenchmarkTypeBadge(criterion.type).label }}
                                </span>
                            </div>

                            <p class="text-xs font-medium text-slate-900 leading-relaxed">
                                {{ criterion.title }}
                            </p>

                            <!-- Status indicator -->
                            <div class="pt-1 flex items-center gap-1.5 text-[11px]">
                                <template v-if="getAttachedDocs(criterion.id).length > 0">
                                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
                                    <span class="text-emerald-700 font-semibold tabular-nums">
                                        {{ getAttachedDocs(criterion.id).length }} Document(s) Linked
                                    </span>
                                </template>
                                <template v-else>
                                    <AlertCircle class="w-3.5 h-3.5 text-amber-500" />
                                    <span class="text-amber-600 font-medium">Pending Evidence</span>
                                </template>
                            </div>
                        </div>
                    </td>

                    <!-- Column 2: Expected Supporting Documents (Checklist) -->
                    <td class="py-4 px-3">
                        <div v-if="criterion.suggested_evidence && criterion.suggested_evidence.length > 0" class="space-y-1.5">
                            <div
                                v-for="(item, idx) in criterion.suggested_evidence"
                                :key="idx"
                                class="flex items-start gap-1.5 text-[11px] text-slate-700 leading-snug"
                            >
                                <span class="font-bold font-mono text-slate-400 shrink-0">{{ idx + 1 }}.</span>
                                <span>{{ item }}</span>
                            </div>
                        </div>
                        <div v-else class="text-slate-600 text-[11px] italic">
                            Standard institutional accreditation evidence and approved documentation.
                        </div>
                    </td>

                    <!-- Column 3: Attached Evidence & Cloud Links -->
                    <td class="py-4 pl-3 pr-4">
                        <div class="space-y-2.5">
                            <!-- Attached Files/Links List -->
                            <div v-if="getAttachedDocs(criterion.id).length > 0" class="space-y-1.5">
                                <EvidenceItemPill
                                    v-for="doc in getAttachedDocs(criterion.id)"
                                    :key="doc.id"
                                    :document="doc"
                                    @preview="emit('preview', $event)"
                                />
                            </div>

                            <div v-else class="text-[11px] text-slate-600 italic py-1">
                                No evidence attached yet.
                            </div>

                            <!-- Attach Button -->
                            <button
                                type="button"
                                @click="emit('openAttach', criterion)"
                                class="btn btn-xs gap-1 font-semibold transition-all"
                                :class="getAttachedDocs(criterion.id).length > 0
                                    ? 'btn-ghost text-sidebar-blue hover:bg-blue-50 border border-slate-200'
                                    : 'bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none shadow-2xs'"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>{{ getAttachedDocs(criterion.id).length > 0 ? '+ Add More' : 'Attach Evidence' }}</span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Empty search state -->
                <tr v-if="criteria.length === 0">
                    <td colspan="3" class="py-12 text-center text-slate-400 text-xs">
                        No benchmarks match the active filter or search query.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
