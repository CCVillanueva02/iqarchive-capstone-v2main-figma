<!--
===============================================================================
IQArchive v2 — Repository Document Picker Modal
===============================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RepositoryDocPickerModal.vue
Role: Allows users to search and select existing repository files as compliance evidence.
UI Standard: DaisyUI modal, input, badge, btn (< 120 lines).
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
    { id: 'rep_1', title: 'Curriculum_Review_Minutes_2024.pdf', original_filename: 'Curriculum_Review_Minutes_2024.pdf', size: '2.1 MB' },
    { id: 'rep_2', title: 'Faculty_PRC_License_Registry.pdf', original_filename: 'Faculty_PRC_License_Registry.pdf', size: '1.4 MB' },
    { id: 'rep_3', title: 'Stakeholder_Consultation_Minutes.pdf', original_filename: 'Stakeholder_Consultation_Minutes.pdf', size: '3.2 MB' },
    { id: 'rep_4', title: 'BOR_Resolution_RDE_Agenda.pdf', original_filename: 'BOR_Resolution_RDE_Agenda.pdf', size: '1.8 MB' },
    { id: 'rep_5', title: 'Community_Extension_MOA_2024.pdf', original_filename: 'Community_Extension_MOA_2024.pdf', size: '2.5 MB' },
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
        id: `cdoc_rep_${d.id}_${Date.now()}`,
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
                    <h3 class="font-bold text-base text-slate-900">Select Evidence from Repository</h3>
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
                    placeholder="Search repository documents..."
                    class="input input-sm input-bordered w-full pl-9 text-xs"
                />
            </div>

            <!-- Documents List -->
            <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100">
                <div
                    v-for="item in availableDocs"
                    :key="item.id"
                    @click="toggleSelect(item.id)"
                    class="flex items-center justify-between p-2.5 rounded-xl cursor-pointer transition-colors"
                    :class="selectedIds.has(item.id) ? 'bg-bu-blue-50/80 border border-bu-blue-200' : 'hover:bg-slate-50 border border-transparent'"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 border border-rose-100">
                            <FileText class="w-3.5 h-3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ item.title || item.original_filename }}</p>
                            <span class="text-[10px] text-slate-400">{{ item.size || 'PDF' }}</span>
                        </div>
                    </div>

                    <div
                        class="w-5 h-5 rounded-md flex items-center justify-center border transition-all"
                        :class="selectedIds.has(item.id) ? 'bg-bu-blue-600 border-bu-blue-600 text-white' : 'border-slate-300 bg-white'"
                    >
                        <Check v-if="selectedIds.has(item.id)" class="w-3.5 h-3.5 stroke-3" />
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="modal-action pt-2 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    {{ selectedIds.size }} document{{ selectedIds.size === 1 ? '' : 's' }} selected
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="handleCancel" class="btn btn-sm btn-ghost text-xs">
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="handleConfirm"
                        :disabled="selectedIds.size === 0"
                        class="btn btn-sm bg-bu-blue-600 hover:bg-bu-blue-700 text-white font-bold text-xs"
                    >
                        Attach Selected ({{ selectedIds.size }})
                    </button>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @click="handleCancel">
            <button>close</button>
        </form>
    </dialog>
</template>
