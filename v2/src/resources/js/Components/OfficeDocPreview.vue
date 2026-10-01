<!--
================================================================================
IQArchive v2 — Office & External Document Preview Card
================================================================================
File: resources/js/Components/OfficeDocPreview.vue
Role: Renders a structured informational card and direct launcher actions for
      non-previewable formats (Word, Excel, PowerPoint, Archives, Google Drive).
UI Standard: DaisyUI card, badge, btn (< 120 lines).
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { Download, ExternalLink } from 'lucide-vue-next';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes } from '@/Utils/formatters';

const props = defineProps({
    document: { type: Object, required: true },
    docType: { type: String, default: 'generic' },
    downloadUrl: { type: String, default: '' },
});

const typeLabel = computed(() => {
    switch (props.docType) {
        case 'word': return 'Microsoft Word Document';
        case 'excel': return 'Microsoft Excel Spreadsheet';
        case 'presentation': return 'PowerPoint Presentation';
        case 'archive': return 'Compressed Archive';
        case 'drive': return 'Google Drive Document';
        default: return 'Attached Document';
    }
});

const isDrive = computed(() => props.docType === 'drive' || Boolean(props.document?.external_url));
const actionUrl = computed(() => {
    if (isDrive.value) {
        return props.document?.external_url || props.downloadUrl;
    }
    return `/documents/${props.document.id}/file?download=1`;
});
</script>

<template>
    <div class="h-full flex items-center justify-center p-6 bg-slate-50/50">
        <div class="card card-border bg-base-100 border-slate-200/80 p-8 rounded-2xl shadow-xs max-w-md w-full text-center flex flex-col items-center">
            <!-- Large Document Glyph -->
            <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-center shadow-xs mb-4">
                <DocumentTypeIcon :document="document" size-class="w-8 h-8" />
            </div>

            <!-- Type Pill -->
            <span class="badge badge-sm badge-soft font-semibold text-slate-600 mb-2">
                {{ typeLabel }}
            </span>

            <!-- Document Title -->
            <h4 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2 max-w-sm" :title="document.title">
                {{ document.title }}
            </h4>

            <!-- File Name & Size -->
            <p class="text-xs text-slate-400 mt-1 font-mono truncate max-w-xs" :title="document.original_filename">
                {{ document.original_filename }}
                <span v-if="document.file_size_bytes"> · {{ formatBytes(document.file_size_bytes) }}</span>
            </p>

            <!-- Explanatory Context -->
            <p class="text-xs text-slate-500 mt-3 max-w-xs leading-relaxed">
                <template v-if="isDrive">
                    This file is hosted in Google Drive. Click below to view the live cloud document with full formatting.
                </template>
                <template v-else>
                    This format cannot be rendered in the browser. Download the file to view it.
                </template>
            </p>

            <!-- Action Button -->
            <div class="mt-6 flex items-center gap-2">
                <a
                    :href="actionUrl"
                    target="_blank"
                    class="btn btn-sm text-white gap-2 shadow-xs"
                    :class="isDrive ? 'bg-sidebar-blue hover:bg-sidebar-blue/90 border-none' : 'btn-primary'"
                >
                    <ExternalLink v-if="isDrive" class="w-4 h-4" />
                    <Download v-else class="w-4 h-4" />
                    <span>{{ isDrive ? 'Open in Google Drive' : 'Download Document' }}</span>
                </a>
            </div>
        </div>
    </div>
</template>
