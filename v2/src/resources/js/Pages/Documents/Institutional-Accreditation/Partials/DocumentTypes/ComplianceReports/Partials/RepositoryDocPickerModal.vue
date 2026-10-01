<!--
===============================================================================
IQArchive v2 — Repository Document Picker Modal
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RepositoryDocPickerModal.vue
Role: Allows users to search and select existing repository files as compliance evidence.
UI Standard: DaisyUI modal, input, badge, btn (< 130 lines).
===============================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { Search, FileText, Check, X } from 'lucide-vue-next';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'attachDocs']);

const searchQuery = ref('');
const selectedIds = ref(new Set());

// Fallback documents if none are loaded in repository yet
const defaultDocs = [
    { id: 25, title: 'IA 2024 Compliance to the Recommendations - Area I (Governance and Management)', original_filename: 'IA_2024 Compliance to the Recommendations - AREA I.pdf', size: '2.4 MB' },
    { id: 26, title: 'IA 2024 Compliance to the Recommendations - Area II (Teaching and Learning)', original_filename: 'IA_2024 Compliance to the Recommendations - AREA II.pdf', size: '3.1 MB' },
    { id: 28, title: 'IA 2024 Link to Supporting Documents - Area I', original_filename: 'IA_2024 Link to Supporting Documents - AREA I.pdf', size: '1.8 MB' },
    { id: 29, title: 'IA 2024 Link to Supporting Documents - Area II', original_filename: 'IA_2024 Link to Supporting Documents - AREA II.pdf', size: '2.5 MB' },
];

const availableDocs = computed(() => {
    const source = (props.documents && props.documents.length > 0) ? props.documents : defaultDocs;
    if (!searchQuery.value.trim()) return source;
    const q = searchQuery.value.toLowerCase();
    return source.filter((d) =>
        (d.title || d.original_filename || '').toLowerCase().includes(q)
    );
});

function toggleSelect(id) {
    if (selectedIds.value.has(id)) {
        selectedIds.value.delete(id);
    } else {
        selectedIds.value.add(id);
    }
}

function handleConfirm() {
    const source = (props.documents && props.documents.length > 0) ? props.documents : defaultDocs;
    const selected = source.filter((d) => selectedIds.value.has(d.id)).map((d) => ({
        id: `ia_cdoc_rep_${d.id}_${Date.now()}`,
        document_id: d.id,
        title: d.title || d.original_filename,
        filename: d.original_filename || d.title,
        status: 'verified',
        type: 'pdf',
        size: d.size || '1.5 MB',
    }));
    emit('attachDocs', selected);
    selectedIds.value.clear();
    searchQuery.value = '';
    emit('close');
}

function handleCancel() {
    selectedIds.value.clear();
    searchQuery.value = '';
    emit('close');
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': isOpen }">
        <div class="modal-box max-w-xl p-6 rounded-2xl bg-white border border-slate-200 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Select Evidence from Institutional Repository</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Choose uploaded documents to attach as proof for this recommendation.</p>
                </div>
                <button type="button" @click="handleCancel" class="btn btn-xs btn-ghost btn-circle text-slate-400 hover:text-slate-600">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Search input -->
            <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search institutional documents..."
                    class="input input-sm input-bordered w-full pl-9 text-xs"
                />
            </div>

            <!-- Documents List -->
            <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100">
                <div
                    v-for="doc in availableDocs"
                    :key="doc.id"
                    @click="toggleSelect(doc.id)"
                    class="flex items-center justify-between p-2.5 rounded-xl cursor-pointer transition-all"
                    :class="selectedIds.has(doc.id) ? 'bg-blue-50 border border-bu-blue-200' : 'hover:bg-slate-50 border border-transparent'"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <input
                            type="checkbox"
                            :checked="selectedIds.has(doc.id)"
                            class="checkbox checkbox-xs checkbox-primary"
                        />
                        <div class="min-w-0">
                            <span class="text-xs font-semibold text-slate-800 truncate block">{{ doc.title || doc.original_filename }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">{{ doc.original_filename }}</span>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium shrink-0 ml-2">
                        {{ doc.size || '1.5 MB' }}
                    </span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="modal-action pt-2 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">
                    {{ selectedIds.size }} document(s) selected
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="handleCancel" class="btn btn-sm btn-ghost text-slate-600">
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="handleConfirm"
                        :disabled="selectedIds.size === 0"
                        class="btn btn-sm btn-primary gap-1.5 shadow-xs"
                    >
                        <Check class="w-4 h-4" />
                        <span>Attach Selected</span>
                    </button>
                </div>
            </div>
        </div>
    </dialog>
</template>
