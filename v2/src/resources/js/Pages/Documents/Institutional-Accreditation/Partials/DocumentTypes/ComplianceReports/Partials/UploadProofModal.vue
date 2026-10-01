<!--
===============================================================================
IQArchive v2 — Upload Proof Modal Component
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/UploadProofModal.vue
Role: Controlled upload dialog to select, name, and stage evidence files deliberately.
UI Standard: DaisyUI modal, input, btn (< 140 lines).
===============================================================================
-->

<script setup>
import { ref } from 'vue';
import { UploadCloud, FileText, X, Check } from 'lucide-vue-next';

defineProps({
    isOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'attachFile']);

const fileInputRef = ref(null);
const selectedFile = ref(null);
const customTitle = ref('');
const isDragging = ref(false);

function triggerFileInput() {
    fileInputRef.value?.click();
}

function handleFileChange(event) {
    const files = event.target.files;
    if (files && files.length > 0) {
        setFile(files[0]);
    }
}

function handleDrop(event) {
    isDragging.value = false;
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        setFile(files[0]);
    }
}

function setFile(file) {
    selectedFile.value = file;
    if (!customTitle.value) {
        customTitle.value = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ');
    }
}

function formatFileSize(bytes) {
    if (!bytes) return '1.2 MB';
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function handleConfirm() {
    if (!selectedFile.value) return;

    emit('attachFile', {
        id: `ia_cdoc_local_${Date.now()}`,
        title: customTitle.value.trim() || selectedFile.value.name,
        filename: selectedFile.value.name,
        type: 'pdf',
        size: formatFileSize(selectedFile.value.size),
        file_obj: selectedFile.value,
    });

    handleClose();
}

function handleClose() {
    selectedFile.value = null;
    customTitle.value = '';
    isDragging.value = false;
    emit('close');
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': isOpen }">
        <div class="modal-box max-w-lg p-6 rounded-2xl bg-white border border-slate-200 shadow-xl space-y-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Upload Supporting Evidence Proof</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Select a document or report to attach as proof of compliance.</p>
                </div>
                <button type="button" @click="handleClose" class="btn btn-xs btn-ghost btn-circle text-slate-400 hover:text-slate-600">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Drag and Drop Area -->
            <input
                ref="fileInputRef"
                type="file"
                accept=".pdf,.docx,.xlsx,.png,.jpg"
                class="hidden"
                @change="handleFileChange"
            />

            <div
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                @click="triggerFileInput"
                class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center gap-2"
                :class="isDragging ? 'border-bu-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60'"
            >
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                    <UploadCloud class="w-5 h-5 text-bu-blue-600" />
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-700">
                        <span class="text-bu-blue-600 hover:underline">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">PDF, Word, Excel, or PNG up to 50MB</p>
                </div>
            </div>

            <!-- File Selected Info -->
            <div v-if="selectedFile" class="space-y-3 pt-1">
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <FileText class="w-4 h-4 text-rose-500 shrink-0" />
                        <div class="min-w-0">
                            <span class="font-semibold text-slate-800 truncate block">{{ selectedFile.name }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ formatFileSize(selectedFile.size) }}</span>
                        </div>
                    </div>
                    <span class="badge badge-xs badge-success gap-1 text-[10px]">
                        <Check class="w-3 h-3" /> Ready
                    </span>
                </div>

                <!-- Custom Document Label -->
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Document Title</label>
                    <input
                        v-model="customTitle"
                        type="text"
                        placeholder="e.g. BOR Resolution No. 045 s. 2024"
                        class="input input-sm input-bordered w-full text-xs"
                    />
                </div>
            </div>

            <!-- Actions -->
            <div class="modal-action pt-2 flex items-center justify-end gap-2">
                <button type="button" @click="handleClose" class="btn btn-sm btn-ghost text-slate-600">
                    Cancel
                </button>
                <button
                    type="button"
                    @click="handleConfirm"
                    :disabled="!selectedFile"
                    class="btn btn-sm btn-primary gap-1.5 shadow-xs"
                >
                    <Check class="w-4 h-4" />
                    <span>Attach Proof</span>
                </button>
            </div>
        </div>
    </dialog>
</template>
