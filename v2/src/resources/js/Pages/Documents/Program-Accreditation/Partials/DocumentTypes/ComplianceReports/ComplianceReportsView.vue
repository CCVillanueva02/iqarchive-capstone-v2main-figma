<!--
===============================================================================
IQArchive v2 — Compliance Reports Master Workspace
===============================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/ComplianceReports/ComplianceReportsView.vue
Role: Renders area-based compliance ledger with actions, documents, and preview.
UI Standard: DaisyUI card, badge, btn (< 140 lines).
===============================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { loadSavedComplianceAreas, saveComplianceAreas } from './data/complianceReportsData';
import ComplianceAreaCarousel from './Partials/ComplianceAreaCarousel.vue';
import RecommendationCard from './Partials/RecommendationCard.vue';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';

const props = defineProps({
    program: { type: Object, required: true },
    college: { type: Object, required: true },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['openUpload', 'backToHub']);

const areas = ref(loadSavedComplianceAreas(props.program?.id));
const selectedAreaId = ref(areas.value[0]?.id || 'comp_area_i');

const currentArea = computed(() => {
    return areas.value.find((a) => a.id === selectedAreaId.value) || areas.value[0];
});

const currentAreaRecommendations = computed(() => {
    return currentArea.value?.recommendations || [];
});

function handleUpdateRec(updatedRec) {
    const rec = currentArea.value?.recommendations?.find((r) => r.id === updatedRec.id);
    if (rec) {
        Object.assign(rec, updatedRec);
        saveComplianceAreas(props.program?.id, areas.value);
    }
}


// Document Preview Drawer State
const previewDoc = ref(null);
const isPreviewOpen = ref(false);

function handlePreviewDoc(docItem) {
    let previewUrl = docItem.file_url || null;
    if (docItem.file_obj && !previewUrl) {
        try {
            previewUrl = URL.createObjectURL(docItem.file_obj);
        } catch (e) {
            console.warn('Could not create object URL for file:', e);
        }
    }

    previewDoc.value = {
        id: docItem.id,
        title: docItem.title || docItem.filename,
        original_filename: docItem.filename,
        file_path: previewUrl || docItem.filename,
        file_url: previewUrl,
        mime_type: 'application/pdf',
        file_size_bytes: 1450000,
        uploader: { name: 'IQA Task Force' },
    };
    isPreviewOpen.value = true;
}

function closePreview() {
    isPreviewOpen.value = false;
    previewDoc.value = null;
}
</script>

<template>
    <div class="space-y-5">
        <!-- 1. Area Carousel Selector -->
        <ComplianceAreaCarousel
            :areas="areas"
            :selected-area-id="selectedAreaId"
            @select-area="selectedAreaId = $event"
        />

        <!-- 2. Recommendations Ledger -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-600">
                    {{ currentArea.code }}: {{ currentArea.title }} Recommendations
                </h3>
            </div>

            <!-- List of Recommendation Cards -->
            <div v-if="currentAreaRecommendations.length > 0" class="space-y-3.5">
                <RecommendationCard
                    v-for="rec in currentAreaRecommendations"
                    :key="rec.id"
                    :rec="rec"
                    :available-documents="documents"
                    @preview-doc="handlePreviewDoc"
                    @update-rec="handleUpdateRec"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="card card-border bg-white border-slate-200 rounded-2xl p-10 text-center space-y-2 shadow-xs">
                <h4 class="text-sm font-bold text-slate-700">No recommendations for this area</h4>
                <p class="text-xs text-slate-500">There are currently no recorded AACCUP recommendations in this area.</p>
            </div>
        </div>

        <!-- Document Preview Drawer -->
        <DocumentPreviewDrawer
            :document="previewDoc"
            :is-open="isPreviewOpen"
            @close="closePreview"
        />
    </div>
</template>
