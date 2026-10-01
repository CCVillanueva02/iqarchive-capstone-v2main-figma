<!--
===============================================================================
IQArchive v2 — Recommendation Card Component
===============================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/ComplianceReports/Partials/RecommendationCard.vue
Role: Renders individual compliance recommendation with actions, documents, and input dock.
UI Standard: DaisyUI card, badge, btn (< 180 lines).
===============================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { ChevronUp, Trash2, Check, RotateCcw } from 'lucide-vue-next';
import RecommendationDocItem from './RecommendationDocItem.vue';
import RecommendationInputSection from './RecommendationInputSection.vue';

const props = defineProps({
    rec: { type: Object, required: true },
    availableDocuments: { type: Array, default: () => [] },
});

const emit = defineEmits(['previewDoc', 'updateRec']);

const isExpandedDocs = ref(false);

// Ensure status reflects entry presence if not manually completed
const currentEffectiveStatus = computed(() => {
    if (props.rec.status === 'fully_complied') return 'fully_complied';
    const hasDocs = (props.rec.supporting_documents?.length || 0) > 0;
    const hasActions = (props.rec.actions_taken?.length || 0) > 0;
    if (hasDocs || hasActions) return 'partial';
    return 'not_started';
});

const statusConfig = computed(() => {
    switch (currentEffectiveStatus.value) {
        case 'fully_complied':
            return {
                label: 'Completed',
                badgeClass: 'badge-success badge-soft text-emerald-700 border-emerald-200',
                numClass: 'bg-emerald-100/80 text-emerald-800 border-emerald-300',
            };
        case 'partial':
            return {
                label: 'Partial',
                badgeClass: 'badge-warning badge-soft text-amber-700 border-amber-200',
                numClass: 'bg-amber-100/80 text-amber-800 border-amber-300',
            };
        default:
            return {
                label: 'Not started',
                badgeClass: 'badge-error badge-soft text-rose-700 border-rose-200',
                numClass: 'bg-rose-100/80 text-rose-800 border-rose-300',
            };
    }
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
        const hasEntries = (props.rec.supporting_documents?.length || 0) > 0 || (props.rec.actions_taken?.length || 0) > 0;
        props.rec.status = hasEntries ? 'partial' : 'not_started';
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
    // When a document/action is added, update status to partial if not already completed
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
    if (props.rec.status !== 'fully_complied') {
        const hasEntries = (props.rec.supporting_documents?.length || 0) > 0 || (props.rec.actions_taken?.length || 0) > 0;
        if (!hasEntries) {
            props.rec.status = 'not_started';
        }
    }
}
</script>

<template>
    <div class="card card-border bg-white border-slate-200/90 rounded-2xl shadow-xs p-5 sm:p-6 space-y-4 hover:border-slate-300 transition-all">
        <!-- Top: Number circle, Recommendation Text, and Status / Completion Action -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                <div
                    class="w-7 h-7 rounded-full flex items-center justify-center font-extrabold text-xs shrink-0 border"
                    :class="statusConfig.numClass"
                >
                    {{ rec.number }}
                </div>
                <h3 class="text-sm font-bold text-slate-900 leading-snug pt-0.5">
                    {{ rec.recommendation }}
                </h3>
            </div>

            <!-- Status Indicator & Mark Completed Action -->
            <div class="flex items-center gap-2 shrink-0">
                <span
                    class="badge font-bold text-xs py-2 px-3 border select-none transition-all"
                    :class="statusConfig.badgeClass"
                >
                    {{ statusConfig.label }}
                </span>

                <button
                    v-if="rec.status !== 'fully_complied'"
                    type="button"
                    @click="toggleCompleted"
                    class="btn btn-xs btn-outline btn-success gap-1 font-bold text-emerald-700 border-emerald-300 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-2xs cursor-pointer"
                    title="Mark this recommendation as completed"
                >
                    <Check class="w-3.5 h-3.5" />
                    <span>Mark as Completed</span>
                </button>
                <button
                    v-else
                    type="button"
                    @click="toggleCompleted"
                    class="btn btn-xs btn-ghost text-slate-400 hover:text-slate-600 hover:bg-slate-100 gap-1 text-[11px] cursor-pointer"
                    title="Reopen recommendation"
                >
                    <RotateCcw class="w-3 h-3" />
                    <span>Reopen</span>
                </button>
            </div>
        </div>

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
