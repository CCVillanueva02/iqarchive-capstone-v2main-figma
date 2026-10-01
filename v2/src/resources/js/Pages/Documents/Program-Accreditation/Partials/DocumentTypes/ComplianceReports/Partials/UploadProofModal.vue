<!--
===============================================================================
IQArchive v2 — Upload Proof Modal Component
===============================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/UploadProofModal.vue
Role: Controlled upload dialog to select, name, and stage evidence files deliberately.
UI Standard: DaisyUI modal, input, btn (< 140 lines).
===============================================================================
-->

<script setup>
import { ref } from 'vue';
import { UploadCloud, FileText, X, Check } from 'lucide-vue-next';

const props = defineProps({
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
        id: `cdoc_local_${Date.now()}`,
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
                class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all"
                :class="isDragging ? 'border-bu-blue-500 bg-bu-blue-50/60' : 'border-slate-300 hover:border-bu-blue-400 bg-slate-50/50 hover:bg-slate-50'"
            >
                <div class="w-10 h-10 rounded-full bg-bu-blue-50 text-bu-blue-600 mx-auto flex items-center justify-center mb-2">
                    <UploadCloud class="w-5 h-5" />
                </div>
                <p class="text-xs font-semibold text-slate-700">Click to browse or drag and drop file here</p>
                <p class="text-[11px] text-slate-400 mt-1">PDF, Word, Excel, or scanned images up to 25 MB</p>
            </div>

            <!-- Staged File Card -->
            <div v-if="selectedFile" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <FileText class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span class="text-xs font-bold text-emerald-900 truncate">{{ selectedFile.name }}</span>
                    </div>
                    <span class="text-[10px] font-semibold text-emerald-700 tabular-nums shrink-0">{{ formatFileSize(selectedFile.size) }}</span>
                </div>

                <!-- Custom Document Title Input -->
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">
                        Document Display Title
                    </label>
                    <input
                        v-model="customTitle"
                        type="text"
                        placeholder="e.g., Office Memorandum on VMGO Compliance"
                        class="input input-xs input-bordered w-full bg-white text-xs"
                    />
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="modal-action pt-2 flex items-center justify-end gap-2">
                <button type="button" @click="handleClose" class="btn btn-sm btn-ghost text-xs">
                    Cancel
                </button>
                <button
                    type="button"
                    @click="handleConfirm"
                    :disabled="!selectedFile"
                    class="btn btn-sm bg-bu-blue-600 hover:bg-bu-blue-700 text-white font-bold text-xs gap-1.5"
                >
                    <Check class="w-3.5 h-3.5" />
                    <span>Attach File Proof</span>
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @click="handleClose">
            <button>close</button>
        </form>
    </dialog>
</template>
