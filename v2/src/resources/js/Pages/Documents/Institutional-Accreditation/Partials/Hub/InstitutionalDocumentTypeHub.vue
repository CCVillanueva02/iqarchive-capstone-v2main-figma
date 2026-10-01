<!--
================================================================================
IQArchive v2 — Institutional Document Type Hub
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/Hub/InstitutionalDocumentTypeHub.vue
Role: Displays the 4 official Institutional Accreditation dossier category cards
      with vibrant, color-coded action buttons and live evidence repository.
UI Standard: DaisyUI card, badge, btn (< 140 lines).
================================================================================
-->

<script setup>
import { 
    Files, 
    ClipboardCheck, 
    FileCheck2, 
    BookOpen,
    ArrowRight
} from 'lucide-vue-next';
import InstitutionalRecentEvidenceTable from './InstitutionalRecentEvidenceTable.vue';

const props = defineProps({
    documentTypes: { type: Array, required: true },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['selectCategory']);

function getCategoryIcon(id) {
    switch (id) {
        case 'compliance-reports': return FileCheck2;
        case 'self-survey': return ClipboardCheck;
        case 'supporting-docs': return Files;
        case 'portfolio': return BookOpen;
        default: return Files;
    }
}

function getIconContainerClass(color) {
    switch (color) {
        case 'emerald': return 'bg-emerald-50 text-emerald-600 border-emerald-200/80';
        case 'orange': return 'bg-orange-50 text-bu-orange-500 border-orange-200/80';
        case 'blue':
        case 'navy': return 'bg-blue-50 text-bu-blue-700 border-blue-200/80';
        case 'violet': return 'bg-purple-50 text-purple-600 border-purple-200/80';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}

function getActionLabel(id) {
    switch (id) {
        case 'supporting-docs': return 'Open Supporting Docs';
        case 'self-survey': return 'Open Self-Survey';
        case 'compliance-reports': return 'Open Reports';
        case 'portfolio': return 'Open Narrative Profile';
        default: return 'Open Workspace';
    }
}

function getButtonClass(color) {
    switch (color) {
        case 'orange': return 'bg-bu-orange-500 hover:bg-bu-orange-600 active:bg-bu-orange-700 text-white';
        case 'emerald': return 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white';
        case 'blue':
        case 'navy': return 'bg-[#193254] hover:bg-[#12243d] active:bg-[#0c1829] text-white';
        case 'violet': return 'bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white';
        default: return 'btn-primary text-white';
    }
}
</script>

<template>
    <div class="space-y-5">
        <!-- Top Section: 4 Category Dossier Cards with Balanced Proportions & Tight Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                v-for="type in documentTypes"
                :key="type.id"
                tabindex="0"
                role="button"
                @click="emit('selectCategory', type.id)"
                @keydown.enter.prevent="emit('selectCategory', type.id)"
                @keydown.space.prevent="emit('selectCategory', type.id)"
                class="card card-border bg-base-100 border-slate-200/90 hover:border-slate-300 hover:shadow-md hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-bu-blue-500 focus-visible:outline-offset-2 transition-all duration-200 cursor-pointer rounded-2xl flex flex-col p-5 group select-none"
            >
                <div class="flex-1 flex flex-col gap-3">
                    <!-- Top Bar: Category Icon in rounded squircle -->
                    <div
                        class="w-11 h-11 rounded-xl border flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-105"
                        :class="getIconContainerClass(type.color)"
                    >
                        <component :is="getCategoryIcon(type.id)" class="w-5 h-5" />
                    </div>

                    <!-- Category Title & Description -->
                    <div class="flex flex-col gap-1.5 flex-1">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-bu-blue-700 transition-colors leading-snug min-h-10 flex items-center">
                            {{ type.title }}
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-3 min-h-12">
                            {{ type.description }}
                        </p>
                    </div>
                </div>

                <!-- Footer: Prominent Full-Width Colored Action Button -->
                <div class="mt-4 pt-1">
                    <button
                        type="button"
                        class="btn btn-sm w-full font-bold rounded-xl border-none shadow-xs h-8 gap-2 transition-all duration-200"
                        :class="getButtonClass(type.color)"
                    >
                        <span>{{ getActionLabel(type.id) }}</span>
                        <ArrowRight class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Evidence Activity Table -->
        <InstitutionalRecentEvidenceTable
            :documents="documents"
        />
    </div>
</template>
