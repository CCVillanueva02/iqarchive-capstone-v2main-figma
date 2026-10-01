<!--
================================================================================
IQArchive v2 — Evidence Item Pill Component
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/EvidenceItemPill.vue
Role: Renders a single attached evidence document or Google Drive link pill.
UI Standard: DaisyUI badge, btn, tooltip (< 100 lines).
================================================================================
-->

<script setup>
import { router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes } from '@/Utils/formatters';

const props = defineProps({
    document: { type: Object, required: true },
    canDelete: { type: Boolean, default: true },
});

const emit = defineEmits(['preview']);

const isExternal = Boolean(props.document.external_url);

function handleClick() {
    if (isExternal) {
        window.open(props.document.external_url, '_blank', 'noopener,noreferrer');
    } else {
        emit('preview', props.document);
    }
}

function handleDelete() {
    if (confirm(`Remove evidence "${props.document.title}"?`)) {
        router.delete(`/documents/program-accreditation/documents/${props.document.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div
        class="group/item flex items-center justify-between gap-2 p-2 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-150 text-xs"
    >
        <button
            type="button"
            @click="handleClick"
            class="flex items-center gap-2 min-w-0 flex-1 text-left"
            :title="isExternal ? 'Open Google Drive: ' + document.external_url : 'Preview: ' + document.title"
        >
            <div
                class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 bg-slate-50"
            >
                <DocumentTypeIcon :document="document" size-class="w-3.5 h-3.5" />
            </div>

            <div class="min-w-0 flex-1">
                <p class="font-medium text-slate-800 truncate group-hover/item:text-bu-blue-600 transition-colors">
                    {{ document.title }}
                </p>
                <div class="flex items-center gap-1.5 text-[10px] text-slate-600">
                    <span v-if="isExternal" class="font-semibold text-slate-700">Google Drive</span>
                    <span v-else class="tabular-nums">{{ formatBytes(document.file_size_bytes) }}</span>
                    <span>•</span>
                    <span class="badge badge-success badge-soft text-[9px] px-1.5 py-0 font-medium">Verified</span>
                </div>
            </div>
        </button>

        <button
            v-if="canDelete"
            type="button"
            @click.stop="handleDelete"
            class="btn btn-ghost btn-xs btn-circle text-slate-500 hover:text-error hover:bg-red-50 opacity-0 group-hover/item:opacity-100 transition-opacity"
            title="Remove evidence link"
        >
            <Trash2 class="w-3.5 h-3.5" />
        </button>
    </div>
</template>
