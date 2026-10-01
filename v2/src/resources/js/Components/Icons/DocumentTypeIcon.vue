<!--
================================================================================
IQArchive v2 — Semantic Document Type Icon Component
================================================================================
File: resources/js/Components/Icons/DocumentTypeIcon.vue
Role: Renders distinctive semantic icon colors and glyphs per file type:
      - PDF: Red FileText icon (#F43F5E / text-rose-500)
      - Word / Docs: Blue FileText icon (#2563EB / text-blue-600)
      - Excel / Spreadsheet: Green FileSpreadsheet icon (#059669 / text-emerald-600)
      - Google Drive: Multi-color Google Drive brand logo
      - Images: Violet FileImage icon (#7C3AED / text-violet-600)
      - Archives: Amber FileArchive icon (#D97706 / text-amber-600)
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { FileText, FileSpreadsheet, FileImage, FileArchive, Presentation } from 'lucide-vue-next';
import GoogleDriveIcon from './GoogleDriveIcon.vue';

const props = defineProps({
    document: { type: [Object, String], default: null },
    sizeClass: { type: String, default: 'w-4 h-4' },
});

const docType = computed(() => {
    if (!props.document) return 'generic';

    if (typeof props.document === 'object' && props.document.external_url) {
        return 'drive';
    }

    const name = typeof props.document === 'string'
        ? props.document
        : (props.document.original_filename || props.document.title || props.document.name || props.document.file_path || '');

    const mime = typeof props.document === 'object'
        ? (props.document.mime_type || props.document.type || '')
        : '';

    const lowerName = name.toLowerCase();
    const lowerMime = mime.toLowerCase();

    if (lowerMime.includes('pdf') || lowerName.endsWith('.pdf')) {
        return 'pdf';
    }
    if (
        lowerMime.includes('word') ||
        lowerMime.includes('officedocument.wordprocessingml') ||
        lowerName.endsWith('.doc') ||
        lowerName.endsWith('.docx')
    ) {
        return 'word';
    }
    if (
        lowerMime.includes('spreadsheet') ||
        lowerMime.includes('excel') ||
        lowerMime.includes('csv') ||
        lowerName.endsWith('.xls') ||
        lowerName.endsWith('.xlsx') ||
        lowerName.endsWith('.csv')
    ) {
        return 'excel';
    }
    if (
        lowerMime.includes('presentation') ||
        lowerMime.includes('powerpoint') ||
        lowerName.endsWith('.ppt') ||
        lowerName.endsWith('.pptx')
    ) {
        return 'presentation';
    }
    if (
        lowerMime.startsWith('image/') ||
        lowerName.endsWith('.jpg') ||
        lowerName.endsWith('.jpeg') ||
        lowerName.endsWith('.png') ||
        lowerName.endsWith('.webp') ||
        lowerName.endsWith('.gif') ||
        lowerName.endsWith('.svg')
    ) {
        return 'image';
    }
    if (
        lowerMime.includes('zip') ||
        lowerMime.includes('compressed') ||
        lowerMime.includes('tar') ||
        lowerName.endsWith('.zip') ||
        lowerName.endsWith('.rar') ||
        lowerName.endsWith('.7z') ||
        lowerName.endsWith('.tar.gz')
    ) {
        return 'archive';
    }

    return 'generic';
});
</script>

<template>
    <GoogleDriveIcon v-if="docType === 'drive'" :class="[sizeClass, 'shrink-0']" />
    <FileText v-else-if="docType === 'pdf'" :class="[sizeClass, 'shrink-0 text-rose-500']" />
    <FileText v-else-if="docType === 'word'" :class="[sizeClass, 'shrink-0 text-blue-600']" />
    <FileSpreadsheet v-else-if="docType === 'excel'" :class="[sizeClass, 'shrink-0 text-emerald-600']" />
    <Presentation v-else-if="docType === 'presentation'" :class="[sizeClass, 'shrink-0 text-amber-500']" />
    <FileImage v-else-if="docType === 'image'" :class="[sizeClass, 'shrink-0 text-violet-600']" />
    <FileArchive v-else-if="docType === 'archive'" :class="[sizeClass, 'shrink-0 text-amber-600']" />
    <FileText v-else :class="[sizeClass, 'shrink-0 text-slate-500']" />
</template>
