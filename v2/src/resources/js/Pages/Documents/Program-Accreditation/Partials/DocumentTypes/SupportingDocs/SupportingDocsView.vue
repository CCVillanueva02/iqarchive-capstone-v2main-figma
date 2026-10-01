<!--
================================================================================
IQArchive v2 — Supporting Documents to Instrument Master Workstation
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/SupportingDocsView.vue
Role: Area carousel, Parameter sidebar, section tabs, and criterion card stack.
Fidelity: Exact reproduction of original IQArchive design.
UI Standard: DaisyUI (< 130 lines).
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import AreaCarousel from './AreaCarousel.vue';
import ParametersSidebar from './ParametersSidebar.vue';
import BenchmarkChecklistWorkspace from './BenchmarkChecklistWorkspace.vue';
import AttachEvidenceModal from './AttachEvidenceModal.vue';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';

const props = defineProps({
    program: { type: Object, required: true },
    college: { type: Object, required: true },
    instrument: { type: Object, default: null },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['openUpload', 'backToHub']);

const areas = computed(() => props.instrument?.areas || []);
const selectedAreaIndex = ref(0);
const selectedParamIndex = ref(0);

const currentArea = computed(() => areas.value[selectedAreaIndex.value] || null);
const currentParameters = computed(() => currentArea.value?.parameters || []);
const currentParameter = computed(() => currentParameters.value[selectedParamIndex.value] || null);

function selectArea(idx) {
    selectedAreaIndex.value = idx;
    selectedParamIndex.value = 0;
}

// Modal and Drawer State
const selectedCriterionForAttach = ref(null);
const isAttachModalOpen = ref(false);
const previewDoc = ref(null);
const isPreviewOpen = ref(false);

function handleOpenAttach(criterion) {
    selectedCriterionForAttach.value = criterion;
    isAttachModalOpen.value = true;
}

function handlePreviewDoc(doc) {
    previewDoc.value = doc;
    isPreviewOpen.value = true;
}
</script>

<template>
    <div class="flex flex-col gap-5 w-full">
        <!-- Area Selector Horizontal Tablist / Carousel -->
        <AreaCarousel
            :areas="areas"
            :selected-index="selectedAreaIndex"
            :documents="documents"
            @select="selectArea"
        />

        <!-- Main Workspace Split Panel -->
        <div class="flex flex-col lg:flex-row gap-5 items-start w-full">
            <!-- Left Pane: Parameters Available -->
            <ParametersSidebar
                :parameters="currentParameters"
                :selected-index="selectedParamIndex"
                :documents="documents"
                @select="selectedParamIndex = $event"
            />

            <!-- Right Pane: Parameters Checklist Workspace -->
            <BenchmarkChecklistWorkspace
                :parameter="currentParameter"
                :documents="documents"
                @open-attach="handleOpenAttach"
                @preview="handlePreviewDoc"
            />
        </div>

        <!-- Attach Evidence Modal (Upload File or Paste Google Drive Link) -->
        <AttachEvidenceModal
            :show="isAttachModalOpen"
            :program="program"
            :criterion="selectedCriterionForAttach"
            :parameter="currentParameter"
            @close="isAttachModalOpen = false"
        />

        <!-- Global Document Preview Drawer -->
        <DocumentPreviewDrawer
            :show="isPreviewOpen"
            :document="previewDoc"
            :program="program"
            @close="isPreviewOpen = false"
        />
    </div>
</template>
