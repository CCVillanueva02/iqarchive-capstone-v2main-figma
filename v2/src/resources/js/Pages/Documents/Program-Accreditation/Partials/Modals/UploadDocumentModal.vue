<!--
================================================================================
IQArchive v2 — Upload Accreditation Document Modal
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/Modals/UploadDocumentModal.vue
Role: Modal dialog for uploading evidence files linked to programs or criteria.
UI Standard: DaisyUI modal, fieldset, input-bordered, btn (< 150 lines).
================================================================================
-->

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, UploadCloud } from 'lucide-vue-next';
import FileDropzone from '@/Components/FileDropzone.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    program: { type: Object, default: null },
    initialCategory: { type: String, default: 'supporting-documents' },
    initialCriterionId: { type: [Number, String], default: null },
});

const emit = defineEmits(['close']);

const form = useForm({
    program_id: '',
    title: '',
    category_type: 'supporting-documents',
    criterion_id: null,
    file: null,
});

watch(() => props.show, (newVal) => {
    if (newVal && props.program) {
        form.program_id = props.program.id;
        form.category_type = props.initialCategory || 'supporting-documents';
        form.criterion_id = props.initialCriterionId || null;
    }
});

function handleClose() {
    form.reset();
    form.clearErrors();
    emit('close');
}

function handleFileChange(file) {
    form.file = file;
    form.clearErrors('file');
    if (file && !form.title) {
        form.title = file.name.replace(/\.[^/.]+$/, '');
    }
}

function submit() {
    if (props.program) {
        form.program_id = props.program.id;
    }
    form.post('/documents/program-accreditation/documents', {
        preserveScroll: true,
        onSuccess: () => handleClose(),
    });
}
</script>

<template>
    <dialog v-if="show" class="modal modal-open bg-slate-900/60 backdrop-blur-xs z-50">
        <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl border border-slate-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-bu-orange-500">
                        <UploadCloud class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Upload Accreditation Document</h3>
                        <p class="text-xs text-slate-500">{{ program?.name }} ({{ program?.code }})</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="handleClose"
                    class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700"
                    aria-label="Close modal"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4 pt-4">
                <!-- Document Title -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Document Title <span class="text-error">*</span>
                    </legend>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. Syllabi Verification Report 2026"
                        class="input input-bordered w-full input-sm text-sm"
                        :class="{ 'input-error': form.errors.title }"
                        required
                    />
                    <span v-if="form.errors.title" class="text-error text-xs mt-1">{{ form.errors.title }}</span>
                </fieldset>

                <!-- Category Type -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Category Type <span class="text-error">*</span>
                    </legend>
                    <select
                        v-model="form.category_type"
                        class="select select-bordered w-full select-sm text-sm"
                        required
                    >
                        <option value="supporting-documents">Supporting Documents to Instrument</option>
                        <option value="self-survey">Self-Survey Documents</option>
                        <option value="compliance-reports">Compliance Reports</option>
                        <option value="ppp">Program Performance Profile (PPP)</option>
                        <option value="narrative-profile">Narrative Profile</option>
                    </select>
                </fieldset>

                <!-- Shared File Dropzone -->
                <FileDropzone
                    v-model="form.file"
                    accept=".pdf,.docx,.xlsx,.png,.jpg"
                    :max-size-mb="50"
                    label="Select File"
                    hint="Allowed: PDF, DOCX, XLSX, PNG, JPG (Max 50MB)"
                    :error="form.errors.file"
                    color="orange"
                    @change="handleFileChange"
                />

                <!-- Actions -->
                <div class="modal-action pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="handleClose"
                        class="btn btn-sm btn-ghost text-slate-600 hover:bg-slate-100"
                        :disabled="form.processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5"
                        :disabled="form.processing || !form.file"
                    >
                        <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                        <UploadCloud v-else class="w-4 h-4" />
                        <span>Upload File</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
