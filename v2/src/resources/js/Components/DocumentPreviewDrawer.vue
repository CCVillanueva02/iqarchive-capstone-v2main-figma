<!--
================================================================================
IQArchive v2 — Global Half-Screen Document Preview Drawer
File: resources/js/Components/DocumentPreviewDrawer.vue
Role: Slide-over drawer with multi-format preview (PDF, Image, Drive, Office).
UI Standard: DaisyUI badge, btn (< 190 lines).
================================================================================
-->
<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Download, ExternalLink, X } from 'lucide-vue-next';
import PdfViewer from './PdfViewer.vue';
import ImageViewer from './ImageViewer.vue';
import OfficeDocPreview from './OfficeDocPreview.vue';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes, formatDate, detectDocumentType, getGoogleDrivePreviewUrl } from '@/Utils/formatters';

const props = defineProps({
    show: { type: Boolean, default: false },
    document: { type: Object, default: null },
    program: { type: Object, default: null },
    office: { type: Object, default: null },
    hideStatus: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);
const previewUrl = ref('');
const isLoadingUrl = ref(false);
const fetchError = ref(false);

const resolvedDocType = computed(() => detectDocumentType(props.document));
const isDrive = computed(() => resolvedDocType.value === 'drive');
const drivePreviewUrl = computed(() => getGoogleDrivePreviewUrl(props.document?.external_url || previewUrl.value));

watch(() => props.document, async (newDoc) => {
    previewUrl.value = '';
    fetchError.value = false;
    if (!newDoc?.id) return;
    if (newDoc.external_url) {
        previewUrl.value = newDoc.external_url;
        return;
    }
    isLoadingUrl.value = true;
    try {
        const res = await fetch(`/documents/${newDoc.id}/download`);
        const data = await res.json();
        data.url ? (previewUrl.value = data.url) : (fetchError.value = true);
    } catch {
        fetchError.value = true;
    } finally {
        isLoadingUrl.value = false;
    }
}, { immediate: true });

function getStatusBadge(status) {
    switch (status) {
        case 'iqa_appr': return { label: 'IQA Approved', class: 'badge-success badge-soft' };
        case 'dean_appr':
        case 'dean_endorsed': return { label: 'Dean Endorsed', class: 'badge-info badge-soft' };
        case 'rejected': return { label: 'Returned', class: 'badge-error badge-soft' };
        default: return { label: 'Draft', class: 'badge-warning badge-soft' };
    }
}

function getTypeBadge(type) {
    switch (type) {
        case 'pdf': return { label: 'PDF', class: 'badge-error badge-soft' };
        case 'image': return { label: 'IMG', class: 'badge-secondary badge-soft' };
        case 'word': return { label: 'DOCX', class: 'badge-info badge-soft' };
        case 'excel': return { label: 'XLSX', class: 'badge-success badge-soft' };
        case 'presentation': return { label: 'PPTX', class: 'badge-warning badge-soft' };
        case 'drive': return { label: 'DRIVE', class: 'badge-primary badge-soft' };
        default: return { label: 'DOC', class: 'badge-neutral badge-soft' };
    }
}

function onKeydown(e) {
    if (e.key === 'Escape' && props.show) emit('close');
}
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show && document" class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs" @click="emit('close')"></div>
                <Transition appear enter-active-class="transition-transform duration-250 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                    <div class="fixed inset-y-0 right-0 w-full md:w-[50vw] max-w-3xl bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full">
                        <!-- Header -->
                        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/60 shrink-0">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <DocumentTypeIcon :document="document" size-class="w-6 h-6 mt-0.5" />
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm md:text-base font-bold text-slate-900 leading-snug line-clamp-2" :title="document.title">
                                        {{ document.title }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                                        <span v-if="program" class="font-medium text-slate-600">{{ program.code || program.name }}</span>
                                        <span v-if="program" class="text-slate-300">·</span>
                                        <span>{{ formatBytes(document.file_size_bytes) }}</span>
                                        <span class="text-slate-300">·</span>
                                        <span>Uploaded {{ formatDate(document.created_at) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a v-if="isDrive" :href="document.external_url || previewUrl" target="_blank" class="btn btn-sm bg-sidebar-blue hover:bg-sidebar-blue/90 text-white gap-1.5 shadow-xs border-none">
                                    <ExternalLink class="w-4 h-4" /><span>Open in Drive</span>
                                </a>
                                <a v-else :href="`/documents/${document.id}/file?download=1`" target="_blank" class="btn btn-sm btn-primary gap-1.5 shadow-xs">
                                    <Download class="w-4 h-4" /><span>Download</span>
                                </a>
                                <button type="button" @click="emit('close')" class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700" title="Close preview">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Metadata Strip -->
                        <div class="px-5 py-3 border-b border-slate-100 bg-white shrink-0 text-xs">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2">
                                <div>
                                    <span class="text-slate-400 text-[11px] block">Category</span>
                                    <span class="font-semibold text-slate-700 block truncate" :title="office?.name || document.category?.name || 'General'">
                                        {{ office?.name || document.category?.name || 'General' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[11px] block">Uploaded By</span>
                                    <span class="font-semibold text-slate-700 block truncate" :title="document.uploader?.name || 'Staff'">
                                        {{ document.uploader?.name || 'Staff' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[11px] block">File Name</span>
                                    <span class="font-mono text-slate-700 block truncate text-[11px]" :title="document.original_filename">
                                        {{ document.original_filename || (isDrive ? 'Google Drive Link' : 'document.pdf') }}
                                    </span>
                                </div>
                                <div class="sm:text-right">
                                    <span class="text-slate-400 text-[11px] block">{{ hideStatus ? 'Format' : 'Status' }}</span>
                                    <div class="flex items-center sm:justify-end gap-1 mt-0.5">
                                        <span class="badge badge-xs text-[10px] font-bold uppercase" :class="getTypeBadge(resolvedDocType).class">
                                            {{ getTypeBadge(resolvedDocType).label }}
                                        </span>
                                        <span v-if="!hideStatus" class="badge badge-xs text-[10px] font-medium" :class="getStatusBadge(document.status).class">
                                            {{ getStatusBadge(document.status).label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Area -->
                        <div class="flex-1 min-h-0 bg-slate-100/70 overflow-hidden relative">
                            <div v-if="isLoadingUrl" class="flex flex-col items-center justify-center h-full gap-2 text-slate-400">
                                <span class="loading loading-spinner loading-md text-bu-blue-700"></span>
                                <span class="text-xs font-medium text-slate-500">Generating secure preview…</span>
                            </div>
                            <div v-else-if="fetchError" class="h-full flex items-center justify-center p-6 bg-slate-50/50">
                                <div class="card card-border bg-base-100 border-slate-200/80 p-8 rounded-2xl shadow-xs max-w-md w-full text-center flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center shadow-xs mb-4">
                                        <DocumentTypeIcon :document="document" size-class="w-8 h-8" />
                                    </div>
                                    <span class="badge badge-sm badge-error badge-soft font-semibold mb-2">Preview Unavailable</span>
                                    <h4 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2 max-w-sm">{{ document.title }}</h4>
                                    <p class="text-xs text-slate-500 mt-2 max-w-xs leading-relaxed">
                                        The preview could not be loaded because the document record or binary file was not found on the server.
                                    </p>
                                </div>
                            </div>
                            <PdfViewer v-else-if="resolvedDocType === 'pdf' && previewUrl" :source="previewUrl" :file-name="document.original_filename" />
                            <ImageViewer v-else-if="resolvedDocType === 'image' && previewUrl" :source="previewUrl" :file-name="document.original_filename" />
                            <div v-else-if="resolvedDocType === 'drive' && drivePreviewUrl" class="h-full flex flex-col">
                                <iframe :src="drivePreviewUrl" class="w-full flex-1 border-none bg-white" allow="autoplay"></iframe>
                            </div>
                            <OfficeDocPreview v-else :document="document" :doc-type="resolvedDocType" :download-url="previewUrl" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
