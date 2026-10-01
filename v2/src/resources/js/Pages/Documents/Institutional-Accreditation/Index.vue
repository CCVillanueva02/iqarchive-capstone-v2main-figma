<!--
================================================================================
IQArchive v2 — Institutional Accreditation Master Workspace
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Index.vue
Role: Top-level orchestrator for university-wide Institutional Accreditation (IA)
UI Standard: DaisyUI + DESIGN.md tokens (< 160 lines)
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import InstitutionalContextCard from './Partials/Navigation/InstitutionalContextCard.vue';
import InstitutionalDocumentTypeHub from './Partials/Hub/InstitutionalDocumentTypeHub.vue';
import SupportingDocsView from './Partials/DocumentTypes/SupportingDocs/SupportingDocsView.vue';
import SelfSurveyView from './Partials/DocumentTypes/SelfSurvey/SelfSurveyView.vue';
import ComplianceReportsView from './Partials/DocumentTypes/ComplianceReports/ComplianceReportsView.vue';
import { ChevronRight, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    activeCategory: { type: String, default: 'hub' },
    documentTypes: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    institution: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({ search: '' }) },
});

function handleSelectCategory(cat) {
    router.get('/documents/institutional-accreditation', {
        category: cat,
    }, { preserveScroll: true });
}

function handleBackToHub() {
    handleSelectCategory('hub');
}

const activeCategoryTitle = computed(() => {
    const item = props.documentTypes.find((t) => t.id === props.activeCategory);
    return item ? item.title : props.activeCategory;
});
</script>

<template>
    <Head title="Institutional Accreditation — IQArchive" />

    <AppShell :hide-topbar="true" :hide-breadcrumbs="true">
        <div class="h-[calc(100vh-3rem)] lg:h-[calc(100vh-4rem)] flex flex-col space-y-4 max-w-7xl mx-auto">
            <!-- Header bar with breadcrumbs & upper right Institutional Context card -->
            <div class="pb-3 border-b border-slate-200 shrink-0 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-3 flex-1 min-w-0">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                        Institutional Accreditation Documents
                    </h1>

                    <!-- Breadcrumb Navigation Bar -->
                    <div class="py-2 px-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 text-xs">
                        <button
                            v-if="activeCategory !== 'hub'"
                            type="button"
                            @click="handleBackToHub"
                            class="btn btn-ghost btn-xs btn-circle text-slate-500"
                            title="Back to Hub"
                        >
                            <ArrowLeft class="w-3.5 h-3.5" />
                        </button>

                        <span class="text-slate-500 font-medium">Documents</span>
                        <ChevronRight class="w-3.5 h-3.5 text-slate-400" />

                        <button
                            type="button"
                            @click="handleBackToHub"
                            class="font-medium hover:underline text-slate-600"
                            :class="{ 'text-slate-900 font-bold': activeCategory === 'hub' }"
                        >
                            Institutional Accreditation
                        </button>

                        <template v-if="activeCategory !== 'hub'">
                            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                            <span class="font-bold text-slate-900">{{ activeCategoryTitle }}</span>
                        </template>
                    </div>
                </div>

                <!-- Upper Right: Institutional Context Card -->
                <div class="shrink-0 w-full md:w-80">
                    <InstitutionalContextCard
                        :active-category="activeCategory"
                        :active-category-title="activeCategoryTitle"
                        :institution="institution"
                    />
                </div>
            </div>

            <!-- Content Area (Scrollable) -->
            <div class="flex-1 min-h-0 overflow-y-auto pr-1">
                <!-- Master Hub -->
                <InstitutionalDocumentTypeHub
                    v-if="activeCategory === 'hub'"
                    :document-types="documentTypes"
                    :documents="documents"
                    @select-category="handleSelectCategory"
                />

                <!-- Document Type 1: Supporting Documents to Instrument -->
                <SupportingDocsView
                    v-else-if="activeCategory === 'supporting-docs'"
                    :documents="documents"
                    :institution="institution"
                />

                <!-- Document Type 2: Self-Survey Documents -->
                <SelfSurveyView
                    v-else-if="activeCategory === 'self-survey'"
                    :documents="documents"
                    :institution="institution"
                />

                <!-- Document Type 3: Compliance Reports -->
                <ComplianceReportsView
                    v-else-if="activeCategory === 'compliance-reports'"
                    :documents="documents"
                    :institution="institution"
                />

                <!-- Future Document Types (to be integrated step-by-step) -->
                <div
                    v-else
                    class="card card-border bg-base-100 border-slate-200 rounded-2xl p-8 text-center space-y-3"
                >
                    <h3 class="text-base font-bold text-slate-800">
                        {{ activeCategoryTitle }}
                    </h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        This workspace will load the {{ activeCategoryTitle }} records and matrices.
                    </p>
                    <div>
                        <button
                            type="button"
                            @click="handleBackToHub"
                            class="btn btn-sm btn-outline gap-1"
                        >
                            <ArrowLeft class="w-3.5 h-3.5" />
                            <span>Return to Hub</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
