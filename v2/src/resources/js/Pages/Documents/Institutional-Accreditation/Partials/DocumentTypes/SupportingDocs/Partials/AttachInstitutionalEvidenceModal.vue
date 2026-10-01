<!--
================================================================================
IQArchive v2 — Attach Institutional Evidence Modal
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/Partials/AttachInstitutionalEvidenceModal.vue
Role: Allows attaching vault PDFs or Google Drive links to an institutional benchmark.
UI Standard: DaisyUI modal, tabs, fieldset, input-bordered, btn (< 160 lines).
================================================================================
-->

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, UploadCloud, FileText } from 'lucide-vue-next';
import GoogleDriveIcon from '@/Components/Icons/GoogleDriveIcon.vue';
import FileDropzone from '@/Components/FileDropzone.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    benchmark: { type: Object, default: null },
    parameter: { type: Object, default: null },
});

const emit = defineEmits(['close', 'attached']);
const activeTab = ref('file'); // 'file' or 'link'

const form = useForm({
    title: '',
    category_type: 'supporting-docs',
    benchmark_id: '',
    benchmark_code: '',
    file: null,
    external_url: '',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.title = '';
        form.category_type = 'supporting-docs';
        form.benchmark_id = props.benchmark?.id || '';
        form.benchmark_code = props.benchmark?.code || '';
        form.file = null;
        form.external_url = '';
        form.clearErrors();
        activeTab.value = 'file';
    }
});

function handleFileChange(file) {
    form.file = file;
    form.clearErrors('file');
    if (file && !form.title) {
        form.title = file.name.replace(/\.[^/.]+$/, '');
    }
}

function handleClose() {
    form.reset();
    form.clearErrors();
    emit('close');
}

function submit() {
    if (activeTab.value === 'file') {
        form.external_url = '';
    } else {
        form.file = null;
    }

    form.post('/documents/institutional-accreditation/documents', {
        preserveScroll: true,
        onSuccess: (page) => {
            const newDoc = page.props?.documents?.[0] || null;
            emit('attached', {
                benchmarkId: props.benchmark?.id,
                title: form.title,
                file: form.file,
                external_url: form.external_url,
                document: newDoc,
            });
            handleClose();
        },
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
                    <p class="text-xs text-slate-500 mt-0.5">Attach verifiable documentation to the institutional accreditation checklist.</p>
                </div>
                <button type="button" @click="handleClose" class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-slate-600">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Context Banner -->
            <div v-if="benchmark" class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mt-3 flex flex-col gap-1 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-600 text-[11px]">
                    <span>Parameter {{ parameter?.letter }} — {{ parameter?.name }}</span>
                </div>
                <div class="flex items-start gap-2 text-slate-700">
                    <span class="font-bold text-slate-900 bg-white border border-slate-200 px-2 py-0.5 rounded text-[11px] font-mono shrink-0">
                        {{ benchmark?.code }}
                    </span>
                    <p class="line-clamp-2 leading-snug">{{ benchmark?.title }}</p>
                </div>
            </div>

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
                <fieldset class="fieldset">
                    <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                        Evidence Title <span class="text-error">*</span>
                    </legend>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. BOR Resolution No. 42 Approving Strategic Plan"
                        class="input input-bordered w-full input-sm text-sm"
                        required
                    />
                    <span v-if="form.errors.title" class="fieldset-label text-error text-xs">{{ form.errors.title }}</span>
                </fieldset>

                <!-- File Dropzone -->
                <div v-if="activeTab === 'file'">
                    <fieldset class="fieldset">
                        <FileDropzone
                            v-model="form.file"
                            accept=".pdf,.docx,.xlsx,.png,.jpg"
                            :max-size-mb="50"
                            hint="PDF, DOCX, XLSX up to 50MB"
                            @change="handleFileChange"
                        />
                        <span v-if="form.errors.file" class="fieldset-label text-error text-xs">{{ form.errors.file }}</span>
                    </fieldset>
                </div>

                <!-- Google Drive URL -->
                <div v-else>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend font-semibold text-slate-700 text-xs">
                            Google Drive Link <span class="text-error">*</span>
                        </legend>
                        <input
                            v-model="form.external_url"
                            type="url"
                            placeholder="https://drive.google.com/file/d/..."
                            class="input input-bordered w-full input-sm text-sm font-mono"
                            required
                        />
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
