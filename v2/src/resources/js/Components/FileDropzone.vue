<!--
================================================================================
IQArchive v2 — Shared File Dropzone Component
================================================================================
File: resources/js/Components/FileDropzone.vue
Role: Reusable drag-and-drop file upload zone with file pill preview and removal.
UI Standard: DaisyUI fieldset, legend, btn, border-dashed.
Line count target: < 150 lines.
================================================================================
-->

<script setup>
import { ref } from 'vue';
import { UploadCloud, X, AlertCircle } from 'lucide-vue-next';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes } from '@/Utils/formatters';

const props = defineProps({
    modelValue: { type: Object, default: null },
    accept: { type: String, default: 'application/pdf,.pdf' },
    maxSizeMb: { type: Number, default: 20 },
    label: { type: String, default: 'File Attachment' },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    color: { type: String, default: 'blue' }, // 'blue' | 'orange'
});

const emit = defineEmits(['update:modelValue', 'change']);

const fileInputRef = ref(null);
const isDragging = ref(false);
const localError = ref('');

function validateAndAssign(file) {
    localError.value = '';
    if (!file) return;

    if (file.size > props.maxSizeMb * 1024 * 1024) {
        localError.value = `File exceeds the maximum size limit of ${props.maxSizeMb}MB.`;
        return;
    }

    emit('update:modelValue', file);
    emit('change', file);
}

function handleFileChange(e) {
    if (e.target.files && e.target.files[0]) {
        validateAndAssign(e.target.files[0]);
    }
}

function handleDrop(e) {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) {
        validateAndAssign(file);
    }
}

function removeFile() {
    localError.value = '';
    if (fileInputRef.value) fileInputRef.value.value = '';
    emit('update:modelValue', null);
    emit('change', null);
}
</script>

<template>
    <fieldset class="fieldset">
        <legend v-if="label" class="fieldset-legend font-semibold text-slate-700 text-xs">
            {{ label }} <span class="text-slate-400 font-normal">(max {{ maxSizeMb }}MB)</span>
        </legend>

        <!-- Empty Dropzone -->
        <div
            v-if="!modelValue"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="fileInputRef?.click()"
            class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-150 flex flex-col items-center justify-center gap-1.5"
            :class="[
                isDragging
                    ? color === 'orange'
                        ? 'border-bu-orange-400 bg-orange-50/60 scale-[0.99]'
                        : 'border-sidebar-blue bg-bu-blue-50/60 scale-[0.99]'
                    : color === 'orange'
                        ? 'border-slate-200 hover:border-bu-orange-400/60 hover:bg-orange-50/30 bg-slate-50/30'
                        : 'border-slate-200 hover:border-sidebar-blue/50 hover:bg-slate-50/80 bg-slate-50/30'
            ]"
        >
            <div
                class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                :class="color === 'orange' ? 'bg-orange-50 text-bu-orange-500' : 'bg-bu-blue-50 text-sidebar-blue'"
            >
                <UploadCloud class="w-4 h-4" />
            </div>
            <div class="text-xs font-semibold text-slate-700">
                <span :class="color === 'orange' ? 'text-bu-orange-600 hover:underline' : 'text-bu-blue-700 hover:underline'">
                    Click to upload
                </span>
                or drag and drop
            </div>
            <p class="text-[11px] text-slate-400 font-normal">
                {{ hint || `Allowed files up to ${maxSizeMb}MB` }}
            </p>
            <input
                ref="fileInputRef"
                type="file"
                :accept="accept"
                @change="handleFileChange"
                class="hidden"
            />
        </div>

        <!-- Selected File Preview Card -->
        <div
            v-else
            class="flex items-center justify-between p-3 rounded-xl border"
            :class="color === 'orange' ? 'border-orange-200 bg-orange-50/40' : 'border-bu-blue-100 bg-bu-blue-50/40'"
        >
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center shrink-0 border border-slate-200">
                    <DocumentTypeIcon :document="modelValue" size-class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-800 truncate max-w-65">
                        {{ modelValue.name }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono tabular-nums">
                        {{ formatBytes(modelValue.size) }} &bull; Ready to upload
                    </div>
                </div>
            </div>
            <button
                type="button"
                @click="removeFile"
                class="btn btn-xs btn-ghost btn-circle text-slate-400 hover:text-red-500 hover:bg-red-50"
                title="Remove file"
                aria-label="Remove file"
            >
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Errors -->
        <span v-if="localError || error" class="text-xs text-error mt-1 flex items-center gap-1">
            <AlertCircle class="w-3.5 h-3.5 shrink-0" />
            <span>{{ localError || error }}</span>
        </span>
    </fieldset>
</template>
