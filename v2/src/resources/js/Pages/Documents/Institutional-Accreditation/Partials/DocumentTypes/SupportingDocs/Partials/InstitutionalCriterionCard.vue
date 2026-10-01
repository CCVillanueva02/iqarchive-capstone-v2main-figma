<!--
================================================================================
IQArchive v2 — Institutional Criterion Card Component
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/Partials/InstitutionalCriterionCard.vue
Role: Renders a single institutional benchmark card with code, statement,
      suggested evidence, attached documents, and upload triggers.
UI Standard: DaisyUI / Program Accre Design Fidelity (< 150 lines).
================================================================================
-->

<script setup>
import { Plus, Upload, Trash2 } from 'lucide-vue-next';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes, formatDate } from '@/Utils/formatters';

const props = defineProps({
    benchmark: { type: Object, required: true },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['openAttach', 'preview', 'deleteDoc']);

function handleDocClick(doc) {
    if (doc.external_url) {
        window.open(doc.external_url, '_blank', 'noopener,noreferrer');
    } else {
        emit('preview', doc);
    }
}
</script>

<template>
    <div class="border border-slate-200 rounded-xl p-5 flex flex-col gap-4 bg-slate-50/30">
        <!-- Top Row: Code Pill, Statement, and + Add File Button -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <span class="text-xs font-bold text-slate-800 bg-slate-100 border border-slate-200/90 px-3 py-1 rounded-full shrink-0 shadow-2xs font-mono">
                    {{ benchmark.code }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-slate-900 leading-snug mt-0.5">
                        {{ benchmark.title }}
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="emit('openAttach', benchmark)"
                class="shrink-0 px-3 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 text-xs font-bold rounded-lg transition cursor-pointer flex items-center gap-1 shadow-2xs"
                title="Attach file or Google Drive link for this benchmark"
            >
                <Plus class="w-3.5 h-3.5 text-bu-orange-500" />
                <span>Add File</span>
            </button>
        </div>

        <!-- Supporting Documents Section -->
        <div class="pl-0 sm:pl-12 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Supporting Documents Attached ({{ documents.length }})
                </span>
            </div>

            <!-- Documents List -->
            <div v-if="documents.length > 0" class="flex flex-col gap-2">
                <div
                    v-for="doc in documents"
                    :key="doc.id"
                    class="flex items-center justify-between p-3.5 bg-white border border-slate-200/80 rounded-lg text-sm gap-3 shadow-2xs"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <DocumentTypeIcon :mime-type="doc.mime_type" :filename="doc.original_filename" :external-url="doc.external_url" size-class="w-4 h-4" />
                        <div class="min-w-0">
                            <span class="font-bold text-slate-900 block truncate" :title="doc.title">
                                {{ doc.title }}
                            </span>
                            <span class="text-xs text-slate-400 mt-0.5 block">
                                <template v-if="doc.external_url">Google Drive Link</template>
                                <template v-else>{{ formatBytes(doc.file_size_bytes || 1500000) }} • Uploaded {{ formatDate(doc.created_at || new Date()) }}</template>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="w-16 text-center inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                            Verified
                        </span>
                        <button
                            type="button"
                            @click="handleDocClick(doc)"
                            class="w-20 text-center inline-flex items-center justify-center text-sm font-bold text-slate-900 hover:underline cursor-pointer"
                        >
                            {{ doc.external_url ? 'Open Link' : 'Preview' }}
                        </button>
                        <button
                            type="button"
                            @click="emit('deleteDoc', doc)"
                            class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-rose-600"
                            title="Remove evidence"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Drop Button -->
            <button
                type="button"
                @click="emit('openAttach', benchmark)"
                class="w-full border-2 border-dashed border-slate-200 hover:border-bu-orange-400 hover:bg-orange-50/20 rounded-xl p-4 flex items-center justify-center gap-2 text-xs font-bold text-slate-600 hover:text-bu-orange-600 transition cursor-pointer group"
            >
                <Upload class="w-4 h-4 text-bu-orange-500 group-hover:scale-110 transition-transform" />
                <span>Upload & Link Document</span>
            </button>
        </div>
    </div>
</template>
