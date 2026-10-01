<!--
================================================================================
IQArchive v2 — Program Accreditation Document Type Hub (Tier 3 Hub)
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/Hub/DocumentTypeHub.vue
Role: Displays official accreditation category dossier cards with vibrant action buttons
      and live evidence table.
UI Standard: DaisyUI card, badge, btn (< 140 lines).
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { 
    Files, 
    ClipboardCheck, 
    FileCheck2, 
    BarChart3, 
    BookOpen, 
    ArrowRight
} from 'lucide-vue-next';
import RecentEvidenceTable from './RecentEvidenceTable.vue';

const props = defineProps({
    college: { type: Object, required: true },
    program: { type: Object, required: true },
    documentTypes: { type: Array, required: true },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['selectCategory', 'switchProgram']);

function getCategoryIcon(id) {
    switch (id) {
        case 'supporting-documents': return Files;
        case 'self-survey': return ClipboardCheck;
        case 'compliance-reports': return FileCheck2;
        case 'ppp': return BarChart3;
        case 'narrative-profile': return BookOpen;
        default: return Files;
    }
}

function getIconContainerClass(color) {
    switch (color) {
        case 'navy':
        case 'blue': return 'bg-blue-50 text-bu-blue-700 border-blue-200/80';
        case 'orange': return 'bg-orange-50 text-bu-orange-500 border-orange-200/80';
        case 'emerald': return 'bg-emerald-50 text-emerald-600 border-emerald-200/80';
        case 'violet': return 'bg-purple-50 text-purple-600 border-purple-200/80';
        case 'teal': return 'bg-teal-50 text-teal-600 border-teal-200/80';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
}

function getActionLabel(id) {
    switch (id) {
        case 'supporting-documents': return 'Open Supporting Docs';
        case 'self-survey': return 'Open Self-Survey';
        case 'compliance-reports': return 'Open Reports';
        case 'ppp': return 'Open PPP';
        case 'narrative-profile': return 'Open Narrative Profile';
        default: return 'Open Workspace';
    }
}

function getButtonClass(color) {
    switch (color) {
        case 'orange': return 'bg-bu-orange-500 hover:bg-bu-orange-600 active:bg-bu-orange-700 text-white';
        case 'emerald': return 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white';
        case 'navy':
        case 'blue': return 'bg-[#193254] hover:bg-[#12243d] active:bg-[#0c1829] text-white';
        case 'violet': return 'bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white';
        case 'teal': return 'bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white';
        default: return 'btn-primary text-white';
    }
}

/**
 * Responsive grid container:
 * - 4 cards: 4 columns across on desktop (lg:grid-cols-4), compact and well-proportioned
 * - 3 cards: 3 columns across on desktop (md:grid-cols-3)
 */
const gridContainerClass = computed(() => {
    return props.documentTypes.length === 4
        ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'
        : 'grid-cols-1 md:grid-cols-3';
});
</script>

<template>
    <div class="space-y-5">
        <!-- Top Section: Category Dossier Cards with Balanced Proportions & Tight Action Buttons -->
        <div class="grid gap-4" :class="gridContainerClass">
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

        <!-- Bottom Section: Program Evidence Repository Table -->
        <RecentEvidenceTable 
            :documents="documents" 
            :program="program" 
            @select-category="emit('selectCategory', $event)" 
        />
    </div>
</template>
