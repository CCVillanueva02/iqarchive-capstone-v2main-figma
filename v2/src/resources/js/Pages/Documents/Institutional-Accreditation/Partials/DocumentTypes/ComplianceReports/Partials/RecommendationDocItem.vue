<!--
===============================================================================
IQArchive v2 — Recommendation Document Pill Item
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RecommendationDocItem.vue
Role: Renders supporting document proof pill with preview and delete action.
UI Standard: DaisyUI btn, badge (< 60 lines).
===============================================================================
-->

<script setup>
import { FileText, ExternalLink, Trash2 } from 'lucide-vue-next';

defineProps({
    doc: { type: Object, required: true },
    allowRemove: { type: Boolean, default: true },
});

const emit = defineEmits(['preview', 'remove']);
</script>

<template>
    <div
        @click="emit('preview', doc)"
        class="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-slate-200/80 bg-white hover:bg-slate-50/80 hover:border-slate-300 transition-all cursor-pointer group shadow-2xs"
    >
        <div class="flex items-center gap-2.5 min-w-0">
            <div
                class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border"
                :class="doc.external_url ? 'bg-blue-50 text-bu-blue-600 border-blue-100' : 'bg-rose-50 text-rose-500 border-rose-100'"
            >
                <ExternalLink v-if="doc.external_url" class="w-3.5 h-3.5" />
                <FileText v-else class="w-3.5 h-3.5" />
            </div>
            <span class="text-xs font-semibold text-slate-800 truncate group-hover:text-bu-blue-600 transition-colors">
                {{ doc.title || doc.filename }}
            </span>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11px] text-slate-400 font-medium tabular-nums">
                {{ doc.size || (doc.external_url ? 'Cloud Link' : 'PDF') }}
            </span>

            <button
                v-if="allowRemove"
                type="button"
                @click.stop="emit('remove', doc.id)"
                class="btn btn-ghost btn-xs text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg p-1.5 transition-colors"
                title="Remove attached proof"
            >
                <Trash2 class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>
</template>
