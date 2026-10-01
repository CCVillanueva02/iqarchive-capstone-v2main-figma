<!--
===============================================================================
IQArchive v2 — Institutional Recommendation Card Component
===============================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RecommendationCard.vue
Role: Renders individual compliance recommendation with actions, documents, and input dock.
UI Standard: DaisyUI card, badge, btn (< 175 lines).
===============================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { ChevronUp, Trash2 } from 'lucide-vue-next';
import RecommendationHeader from './RecommendationHeader.vue';
import RecommendationDocItem from './RecommendationDocItem.vue';
import RecommendationInputSection from './RecommendationInputSection.vue';
import { getComplianceStatusConfig } from '../data/institutionalComplianceData.js';

const props = defineProps({
    rec: { type: Object, required: true },
    availableDocuments: { type: Array, default: () => [] },
});

const emit = defineEmits(['previewDoc', 'updateRec']);

const isExpandedDocs = ref(false);

const hasEntries = computed(() => {
    return (props.rec.supporting_documents?.length || 0) > 0 || (props.rec.actions_taken?.length || 0) > 0;
});

const statusConfig = computed(() => {
    return getComplianceStatusConfig(props.rec.status, hasEntries.value);
});

const displayedDocs = computed(() => {
    const list = props.rec.supporting_documents || [];
    if (isExpandedDocs.value || list.length <= 2) return list;
    return list.slice(0, 2);
});

const remainingDocsCount = computed(() => {
    const list = props.rec.supporting_documents || [];
    return Math.max(0, list.length - 2);
});

function toggleCompleted() {
    if (props.rec.status === 'fully_complied') {
        props.rec.status = hasEntries.value ? 'partial' : 'not_started';
    } else {
        props.rec.status = 'fully_complied';
    }
    emit('updateRec', props.rec);
}

function handleAddActionProof(payload) {
    if (payload.action) {
        if (!props.rec.actions_taken) props.rec.actions_taken = [];
        props.rec.actions_taken.push(payload.action);
    }
    if (payload.documents && payload.documents.length > 0) {
        if (!props.rec.supporting_documents) props.rec.supporting_documents = [];
        props.rec.supporting_documents.push(...payload.documents);
    }
    if (props.rec.status !== 'fully_complied') {
        props.rec.status = 'partial';
    }
    emit('updateRec', props.rec);
}

function removeAction(index) {
    props.rec.actions_taken.splice(index, 1);
    checkAndResetStatus();
    emit('updateRec', props.rec);
}

function removeDoc(docId) {
    props.rec.supporting_documents = props.rec.supporting_documents.filter((d) => d.id !== docId);
    checkAndResetStatus();
    emit('updateRec', props.rec);
}

function checkAndResetStatus() {
    if (props.rec.status !== 'fully_complied' && !hasEntries.value) {
        props.rec.status = 'not_started';
    }
}
</script>

<template>
    <div class="card card-border bg-white border-slate-200/90 rounded-2xl shadow-xs p-5 sm:p-6 space-y-4 hover:border-slate-300 transition-all">
        <!-- Top: Number circle, Recommendation Text, and Status / Completion Action -->
        <RecommendationHeader
            :rec="rec"
            :status-config="statusConfig"
            @toggle-completed="toggleCompleted"
        />

        <!-- 2-Column Grid: Actions Taken & Supporting Documents -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-1">
            <!-- Left: Actions Taken -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    ACTIONS TAKEN ({{ rec.actions_taken?.length || 0 }})
                </div>
                <ul v-if="rec.actions_taken && rec.actions_taken.length > 0" class="space-y-1.5 text-xs text-slate-700">
                    <li
                        v-for="(action, idx) in rec.actions_taken"
                        :key="idx"
                        class="group flex items-start justify-between gap-2.5 py-1 px-1.5 -mx-1.5 rounded-lg hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex items-start gap-2 min-w-0">
                            <span class="text-slate-400 font-bold select-none shrink-0 mt-0.5">•</span>
                            <span class="leading-relaxed font-normal text-slate-700">{{ action }}</span>
                        </div>
                        <button
                            type="button"
                            @click="removeAction(idx)"
                            class="opacity-0 group-hover:opacity-100 btn btn-ghost btn-xs text-slate-400 hover:text-rose-600 hover:bg-rose-50 p-1 rounded-md shrink-0 transition-all cursor-pointer"
                            title="Delete this action"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </li>
                </ul>
                <p v-else class="text-xs text-slate-400 italic">No actions recorded yet. Type an action below.</p>
            </div>

            <!-- Right: Supporting Documents -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    SUPPORTING DOCUMENTS ({{ rec.supporting_documents?.length || 0 }})
                </div>

                <div v-if="rec.supporting_documents && rec.supporting_documents.length > 0" class="space-y-2">
                    <RecommendationDocItem
                        v-for="doc in displayedDocs"
                        :key="doc.id"
                        :doc="doc"
                        :allow-remove="true"
                        @preview="emit('previewDoc', $event)"
                        @remove="removeDoc"
                    />

                    <!-- Expand / Collapse More Documents Pill -->
                    <button
                        v-if="remainingDocsCount > 0 && !isExpandedDocs"
                        type="button"
                        @click="isExpandedDocs = true"
                        class="btn btn-xs btn-ghost border border-slate-200 text-slate-600 font-bold hover:bg-slate-100 rounded-lg gap-1 mt-1 cursor-pointer"
                    >
                        <span>+{{ remainingDocsCount }} more</span>
                    </button>
                    <button
                        v-else-if="isExpandedDocs && (rec.supporting_documents?.length || 0) > 2"
                        type="button"
                        @click="isExpandedDocs = false"
                        class="btn btn-xs btn-ghost text-slate-400 font-semibold hover:bg-slate-100 rounded-lg gap-1 mt-1 cursor-pointer"
                    >
                        <ChevronUp class="w-3 h-3" />
                        <span>Show less</span>
                    </button>
                </div>
                <p v-else class="text-xs text-slate-400 italic">No documents attached yet. Upload or select files below.</p>
            </div>
        </div>

        <!-- Always-Visible Input Dock -->
        <RecommendationInputSection
            :rec-id="rec.id"
            :available-documents="availableDocuments"
            @add-action-proof="handleAddActionProof"
        />
    </div>
</template>
