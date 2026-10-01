<!--
================================================================================
IQArchive v2 — Institutional Supporting Documents Master Workstation
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/SupportingDocsView.vue
Role: Area carousel, Parameter sidebar, section tabs, and criterion card stack for IA.
UI Standard: DaisyUI (< 150 lines).
================================================================================
-->

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import InstitutionalAreaCarousel from './Partials/InstitutionalAreaCarousel.vue';
import InstitutionalParametersSidebar from './Partials/InstitutionalParametersSidebar.vue';
import InstitutionalBenchmarkChecklistWorkspace from './Partials/InstitutionalBenchmarkChecklistWorkspace.vue';
import AttachInstitutionalEvidenceModal from './Partials/AttachInstitutionalEvidenceModal.vue';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';
import {
    INSTITUTIONAL_SUPPORTING_AREAS,
    loadSavedSupportingDocs,
    saveSupportingDocs,
} from './data/institutionalSupportingDocsData.js';

const props = defineProps({
    documents: { type: Array, default: () => [] },
    institution: { type: Object, default: () => ({}) },
});

const areas = INSTITUTIONAL_SUPPORTING_AREAS;
const selectedAreaIndex = ref(0);
const selectedParamIndex = ref(0);

const currentArea = computed(() => areas[selectedAreaIndex.value] || null);
const currentParameters = computed(() => currentArea.value?.parameters || []);
const currentParameter = computed(() => currentParameters.value[selectedParamIndex.value] || null);

function selectArea(idx) {
    selectedAreaIndex.value = idx;
    selectedParamIndex.value = 0;
}

// Evidence persistence and attachment mapping
const evidenceMap = ref({});

onMounted(() => {
    const saved = loadSavedSupportingDocs();
    const serverDocs = props.documents || [];
    const sampleDoc = serverDocs.find(d => d.id === 28) || serverDocs.find(d => d.mime_type === 'application/pdf') || serverDocs[0];

    let changed = false;
    // Migrate any legacy 'ia-seed-1' or non-previewable seed
    for (const key in saved) {
        if (Array.isArray(saved[key])) {
            saved[key] = saved[key].map(doc => {
                if (doc.id === 'ia-seed-1') {
                    changed = true;
                    return {
                        id: sampleDoc?.id || 28,
                        title: sampleDoc?.title || "IA 2024 Link to Supporting Documents - Area I",
                        original_filename: sampleDoc?.original_filename || 'IA_2024 Link to Supporting Documents - AREA I.pdf',
                        mime_type: sampleDoc?.mime_type || 'application/pdf',
                        file_size_bytes: sampleDoc?.file_size_bytes || 1450000,
                        created_at: sampleDoc?.created_at || '2026-10-01T00:00:00.000Z',
                    };
                }
                return doc;
            });
        }
    }

    // Seed default authentic mock evidence if empty
    if (Object.keys(saved).length === 0) {
        saved['ia_bm_I_A_S.1'] = [
            {
                id: sampleDoc?.id || 28,
                title: sampleDoc?.title || "IA 2024 Link to Supporting Documents - Area I",
                original_filename: sampleDoc?.original_filename || 'IA_2024 Link to Supporting Documents - AREA I.pdf',
                mime_type: sampleDoc?.mime_type || 'application/pdf',
                file_size_bytes: sampleDoc?.file_size_bytes || 1450000,
                created_at: sampleDoc?.created_at || '2026-10-01T00:00:00.000Z',
            },
        ];
        changed = true;
    }

    if (changed) {
        saveSupportingDocs(saved);
    }
    evidenceMap.value = saved;
});

// Modal and Drawer State
const selectedBenchmarkForAttach = ref(null);
const isAttachModalOpen = ref(false);
const previewDoc = ref(null);
const isPreviewOpen = ref(false);

function handleOpenAttach(benchmark) {
    selectedBenchmarkForAttach.value = benchmark;
    isAttachModalOpen.value = true;
}

function handlePreviewDoc(doc) {
    previewDoc.value = doc;
    isPreviewOpen.value = true;
}

function handleEvidenceAttached(payload) {
    const bmId = payload.benchmarkId;
    if (!bmId) return;

    if (!evidenceMap.value[bmId]) {
        evidenceMap.value[bmId] = [];
    }

    const createdDoc = payload.document;
    evidenceMap.value[bmId].unshift({
        id: createdDoc?.id || ('local_' + Date.now()),
        title: createdDoc?.title || payload.title,
        original_filename: createdDoc?.original_filename || payload.file?.name || (payload.external_url ? 'Google Drive Link' : 'Document'),
        external_url: createdDoc?.external_url || payload.external_url || null,
        mime_type: createdDoc?.mime_type || (payload.external_url ? 'application/x-url' : (payload.file?.type || 'application/pdf')),
        file_size_bytes: createdDoc?.file_size_bytes || payload.file?.size || 0,
        created_at: createdDoc?.created_at || new Date().toISOString(),
    });

    saveSupportingDocs(evidenceMap.value);
}

function handleDeleteDoc(doc) {
    if (doc.id && typeof doc.id === 'number') {
        router.delete(`/documents/institutional-accreditation/documents/${doc.id}`, {
            preserveScroll: true,
        });
    }

    // Also remove from local evidenceMap
    for (const key of Object.keys(evidenceMap.value)) {
        evidenceMap.value[key] = (evidenceMap.value[key] || []).filter((d) => d.id !== doc.id);
    }
    saveSupportingDocs(evidenceMap.value);
}
</script>

<template>
    <div class="flex flex-col gap-5 w-full">
        <!-- Area Selector Horizontal Carousel -->
        <InstitutionalAreaCarousel
            :areas="areas"
            :selected-index="selectedAreaIndex"
            :evidence-map="evidenceMap"
            @select="selectArea"
        />

        <!-- Main Workspace Split Panel -->
        <div class="flex flex-col lg:flex-row gap-5 items-start w-full">
            <!-- Left Pane: Parameters Available -->
            <InstitutionalParametersSidebar
                :parameters="currentParameters"
                :selected-index="selectedParamIndex"
                :evidence-map="evidenceMap"
                @select="selectedParamIndex = $event"
            />

            <!-- Right Pane: Parameters Checklist Workspace -->
            <InstitutionalBenchmarkChecklistWorkspace
                :parameter="currentParameter"
                :evidence-map="evidenceMap"
                @open-attach="handleOpenAttach"
                @preview="handlePreviewDoc"
                @delete-doc="handleDeleteDoc"
            />
        </div>

        <!-- Attach Evidence Modal -->
        <AttachInstitutionalEvidenceModal
            :show="isAttachModalOpen"
            :benchmark="selectedBenchmarkForAttach"
            :parameter="currentParameter"
            @attached="handleEvidenceAttached"
            @close="isAttachModalOpen = false"
        />

        <!-- Global Document Preview Drawer -->
        <DocumentPreviewDrawer
            :show="isPreviewOpen"
            :document="previewDoc"
            @close="isPreviewOpen = false"
        />
    </div>
</template>
