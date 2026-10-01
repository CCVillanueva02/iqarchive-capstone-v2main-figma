<!--
================================================================================
IQArchive v2 — Attach Evidence Modal (Files & Cloud Links)
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/AttachEvidenceModal.vue
Role: Allows attaching vault PDFs or Google Drive links to a benchmark criterion.
UI Standard: DaisyUI modal, tabs, fieldset, input-bordered, btn (< 160 lines).
================================================================================
-->

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, UploadCloud, FileText } from 'lucide-vue-next';
import GoogleDriveIcon from '@/Components/Icons/GoogleDriveIcon.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import EvidenceContextBanner from './Partials/EvidenceContextBanner.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    program: { type: Object, default: null },
    criterion: { type: Object, default: null },
    parameter: { type: Object, default: null },
});
const emit = defineEmits(['close']);
const activeTab = ref('file'); // 'file' or 'link'

const form = useForm({
    program_id: '',
    category_type: 'supporting-documents',
    criterion_id: null,
    title: '',
    file: null,
    external_url: '',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.program_id = props.program?.id || '';
        form.criterion_id = props.criterion?.id || null;
        form.category_type = 'supporting-documents';
        form.title = '';
        form.file = null;
        form.external_url = '';
        form.clearErrors();
        activeTab.value = 'file';
    }
});

function handleFileChange(file) {
    form.file = file;
    form.clearErrors('file');
    if (file && !form.title) form.title = file.name.replace(/\.[^/.]+$/, '');
}

function handleClose() {
    form.reset();
    form.clearErrors();
    emit('close');
}

function submit() {
    if (activeTab.value === 'file') form.external_url = '';
    else form.file = null;

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
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Upload & Link Evidence</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Attach verifiable documentation to the accreditation checklist.</p>
                </div>
                <button type="button" @click="handleClose" class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-slate-600">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Target Context Banner -->
            <EvidenceContextBanner :parameter="parameter" :criterion="criterion" />

            <!-- Tab Switcher (File vs Google Drive) -->
            <div class="flex items-center gap-1 border-b border-slate-200 mt-3 pb-2">
                <button
                    type="button"
                    @click="activeTab = 'file'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
                    :class="activeTab === 'file' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    <FileText class="w-3.5 h-3.5" />
                    <span>Upload Document</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'link'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
                    :class="activeTab === 'link' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
                >
                    <GoogleDriveIcon class="w-3.5 h-3.5" />
                    <span>Google Drive</span>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="mt-3 space-y-3">
                <!-- Title Field -->
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Evidence Title <span class="text-error">*</span>
                    </legend>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. Board Resolution Approving VMGO Revision 2026"
                        class="input input-bordered w-full input-sm text-sm"
                        required
                    />
                    <span v-if="form.errors.title" class="fieldset-label text-error text-xs">{{ form.errors.title }}</span>
                </fieldset>

                <!-- File Dropzone (Tab: file) -->
                <div v-if="activeTab === 'file'">
                    <fieldset class="fieldset">
                        <FileDropzone
                            v-model="form.file"
                            accept=".pdf,.docx,.xlsx,.png,.jpg"
                            :max-size="52428800"
                            hint="PDF, DOCX, XLSX up to 50MB"
                            @change="handleFileChange"
                        />
                        <span v-if="form.errors.file" class="fieldset-label text-error text-xs">{{ form.errors.file }}</span>
                    </fieldset>
                </div>

                <!-- Google Drive URL (Tab: link) -->
                <div v-else>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                            Google Drive Link <span class="text-error">*</span>
                        </legend>
                        <input
                            v-model="form.external_url"
                            type="url"
                            placeholder="https://drive.google.com/..."
                            class="input input-bordered w-full input-sm text-sm font-mono"
                            required
                        />
                        <p class="text-[11px] text-slate-500 mt-1">
                            Paste the shareable Google Drive link.
                        </p>
                        <span v-if="form.errors.external_url" class="fieldset-label text-error text-xs">{{ form.errors.external_url }}</span>
                    </fieldset>
                </div>

                <!-- Modal Actions -->
                <div class="modal-action border-t border-slate-100 pt-3 mt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="handleClose" class="btn btn-sm btn-ghost text-slate-600">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing || (!form.file && !form.external_url)"
                        class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5"
                    >
                        <UploadCloud v-if="!form.processing" class="w-4 h-4" />
                        <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
                        <span>{{ form.processing ? 'Attaching...' : 'Attach Evidence' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</template>
