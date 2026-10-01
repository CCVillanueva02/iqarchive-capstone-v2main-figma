<!--
===============================================================================
IQArchive v2 — Recommendation Input Section Component
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RecommendationInputSection.vue
Role: Always-visible dock on each recommendation card to type actions and attach proofs.
UI Standard: DaisyUI input, textarea, btn, badge (< 145 lines).
===============================================================================
-->

<script setup>
import { ref } from 'vue';
import { UploadCloud, FolderArchive, X, FileText } from 'lucide-vue-next';
import RepositoryDocPickerModal from './RepositoryDocPickerModal.vue';
import UploadProofModal from './UploadProofModal.vue';

defineProps({
    recId: { type: String, required: true },
    availableDocuments: { type: Array, default: () => [] },
});

const emit = defineEmits(['addActionProof']);

const actionText = ref('');
const stagedDocs = ref([]);
const isUploadModalOpen = ref(false);
const isRepoModalOpen = ref(false);

function handleFileAttached(fileItem) {
    stagedDocs.value.push(fileItem);
}

function handleRepoDocsAttached(docs) {
    stagedDocs.value.push(...docs);
}

function removeStagedDoc(index) {
    stagedDocs.value.splice(index, 1);
}

function handleSubmit() {
    if (!actionText.value.trim() && stagedDocs.value.length === 0) return;

    emit('addActionProof', {
        action: actionText.value.trim(),
        documents: [...stagedDocs.value],
    });

    actionText.value = '';
    stagedDocs.value = [];
}
</script>

<template>
    <div class="mt-4 pt-4 border-t border-dashed border-slate-200">
        <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span>Add Action Taken & Supporting Evidence</span>
                </span>
            </div>

            <!-- Action Taken Input -->
            <div>
                <textarea
                    v-model="actionText"
                    rows="2"
                    placeholder="Type the specific institutional action or compliance measure taken..."
                    class="textarea textarea-bordered textarea-xs w-full bg-white text-xs placeholder:text-slate-400 focus:outline-bu-blue-500"
                ></textarea>
            </div>

            <!-- Supporting Documents Bar & Action Trigger -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                <div class="flex items-center gap-2">
                    <!-- Upload File Proof (Opens Modal) -->
                    <button
                        type="button"
                        @click="isUploadModalOpen = true"
                        class="btn btn-xs btn-outline border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-semibold gap-1.5 cursor-pointer shadow-2xs"
                    >
                        <UploadCloud class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Upload File Proof</span>
                    </button>

                    <!-- Select from Repository -->
                    <button
                        type="button"
                        @click="isRepoModalOpen = true"
                        class="btn btn-xs btn-outline border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-semibold gap-1.5 cursor-pointer shadow-2xs"
                    >
                        <FolderArchive class="w-3.5 h-3.5 text-bu-blue-600" />
                        <span>From Repository</span>
                    </button>
                </div>

                <!-- Submit Button -->
                <button
                    type="button"
                    @click="handleSubmit"
                    :disabled="!actionText.trim() && stagedDocs.length === 0"
                    class="btn btn-xs bg-bu-blue-600 hover:bg-bu-blue-700 text-white disabled:opacity-50 font-bold gap-1.5 shrink-0 shadow-2xs cursor-pointer"
                >
                    <span>+ Add Action & Proof</span>
                </button>
            </div>

            <!-- Staged Proofs Chips Preview -->
            <div v-if="stagedDocs.length > 0" class="flex flex-wrap gap-2 pt-1">
                <div
                    v-for="(sDoc, sIdx) in stagedDocs"
                    :key="sDoc.id"
                    class="badge badge-sm badge-neutral gap-1.5 py-2.5 px-2.5 text-[11px] font-medium"
                >
                    <FileText class="w-3 h-3 text-rose-400" />
                    <span class="max-w-36 truncate">{{ sDoc.title || sDoc.filename }}</span>
                    <span class="text-[9px] text-slate-400">({{ sDoc.size }})</span>
                    <button
                        type="button"
                        @click="removeStagedDoc(sIdx)"
                        class="hover:text-rose-300 ml-1 text-slate-300 transition-colors"
                        title="Remove staged proof"
                    >
                        <X class="w-3 h-3" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload File Proof Controlled Modal -->
        <UploadProofModal
            :is-open="isUploadModalOpen"
            @close="isUploadModalOpen = false"
            @attach-file="handleFileAttached"
        />

        <!-- Repository Document Picker Modal -->
        <RepositoryDocPickerModal
            :is-open="isRepoModalOpen"
            :documents="availableDocuments"
            @close="isRepoModalOpen = false"
            @attach-docs="handleRepoDocsAttached"
        />
    </div>
</template>
