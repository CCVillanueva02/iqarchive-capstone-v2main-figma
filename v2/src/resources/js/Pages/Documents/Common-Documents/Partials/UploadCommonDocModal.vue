<!--
================================================================================
IQArchive v2 — Upload Common Document Modal
================================================================================
File: resources/js/Pages/Documents/Common-Documents/Partials/UploadCommonDocModal.vue
Role: Modal dialog for uploading university-wide common documents.
UI Standard: DaisyUI modal, fieldset, input, select, textarea, btn.
Line count target: < 150 lines.
================================================================================
-->

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { UploadCloud, X, AlertCircle } from 'lucide-vue-next';
import FileDropzone from '@/Components/FileDropzone.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    offices: { type: Array, required: true },
    initialOfficeId: { type: [Number, String], default: null },
});

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    office_id: props.initialOfficeId || (props.offices[0]?.id ?? ''),
    file: null,
    description: '',
});

watch(() => props.initialOfficeId, (id) => {
    if (id) form.office_id = id;
});

function handleFileChange(file) {
    form.file = file;
    form.clearErrors('file');
    if (file && !form.title.trim()) {
        const name = file.name.replace(/\.[^/.]+$/, '').replace(/[_-]+/g, ' ').trim();
        form.title = name.charAt(0).toUpperCase() + name.slice(1);
    }
}

function closeModal() {
    form.clearErrors();
    form.reset();
    emit('close');
}

function submit() {
    form.post('/documents/common', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}
</script>

<template>
    <div
        class="modal"
        :class="{ 'modal-open': show }"
        role="dialog"
        aria-modal="true"
        aria-labelledby="upload-modal-title"
        @keydown.esc="closeModal"
    >
        <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-bu-blue-50 text-sidebar-blue border border-bu-blue-100 flex items-center justify-center shrink-0">
                        <UploadCloud class="w-5 h-5" />
                    </div>
                    <h3 id="upload-modal-title" class="font-bold text-base text-slate-800">
                        Upload Common Document
                    </h3>
                </div>
                <button
                    type="button"
                    @click="closeModal"
                    class="btn btn-sm btn-ghost btn-circle text-slate-400 hover:text-slate-600"
                    aria-label="Close modal"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-3.5">
                <!-- Originating Office -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">Originating Office</legend>
                    <select v-model="form.office_id" class="select select-sm select-bordered w-full text-xs" :class="{ 'select-error': form.errors.office_id }">
                        <option v-for="off in offices" :key="off.id" :value="off.id">{{ off.name }}</option>
                    </select>
                    <span v-if="form.errors.office_id" class="text-xs text-error mt-1 flex items-center gap-1">
                        <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.office_id }}
                    </span>
                </fieldset>

                <!-- Document Title -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">Document Title</legend>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. BU Faculty Merit Promotion System 2024"
                        class="input input-sm input-bordered w-full text-xs"
                        :class="{ 'input-error': form.errors.title }"
                        required
                    />
                    <span v-if="form.errors.title" class="text-xs text-error mt-1 flex items-center gap-1">
                        <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.title }}
                    </span>
                </fieldset>

                <!-- Shared File Dropzone -->
                <FileDropzone
                    v-model="form.file"
                    accept=".pdf,.docx,.doc,.xlsx,.xls,.csv,.pptx,.ppt,.png,.jpg,.jpeg,.webp,.zip"
                    :max-size-mb="50"
                    label="Document Attachment"
                    hint="PDF, Word, Excel, PowerPoint, Images, Zip (Max 50MB)"
                    :error="form.errors.file"
                    color="blue"
                    @change="handleFileChange"
                />

                <!-- Remarks / Notes -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Notes / Remarks <span class="text-slate-400 font-normal">(Optional)</span>
                    </legend>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="Add context or applicability details..."
                        class="textarea textarea-bordered textarea-sm w-full text-xs"
                        maxlength="1000"
                    ></textarea>
                </fieldset>

                <!-- Actions -->
                <div class="modal-action pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="btn btn-sm btn-ghost text-slate-600" :disabled="form.processing">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.file"
                        class="btn btn-sm bg-sidebar-blue hover:bg-sidebar-blue-hover text-white border-none gap-2 shadow-xs"
                    >
                        <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                        <span>Upload Document</span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="button" @click="closeModal">close</button>
        </form>
    </div>
</template>
