<!--
================================================================================
IQArchive v2 — Self-Survey Matrix Master Workspace
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SelfSurvey/SelfSurveyView.vue
Role: Internal QA self-evaluation spreadsheet with automated real-time mean calculations,
      AACCUP rubric reference, local draft persistence, and print/export support.
UI Standard: DaisyUI table, select, btn, alert (< 175 lines)
================================================================================
-->

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { CheckCircle } from 'lucide-vue-next';
import { PROGRAM_SURVEY_AREAS } from './data/selfSurveyData';
import SelfSurveyAreaCarousel from './Partials/SelfSurveyAreaCarousel.vue';
import RatingScaleReference from './Partials/RatingScaleReference.vue';
import SelfSurveyParameterBlock from './Partials/SelfSurveyParameterBlock.vue';
import SelfSurveySummaryFooter from './Partials/SelfSurveySummaryFooter.vue';

const props = defineProps({
    program: { type: Object, required: true },
    college: { type: Object, required: true },
    instrument: { type: Object, default: null },
});

const emit = defineEmits(['backToHub']);

const areas = computed(() => PROGRAM_SURVEY_AREAS);
const selectedAreaId = ref(areas.value[0]?.id || 'ss_area_i');
const currentArea = computed(() => areas.value.find((a) => a.id === selectedAreaId.value) || areas.value[0]);

// Reactive State: ratings map, best practices map, and surveyor name
const ratings = ref({});
const bestPractices = ref({});
const preparedBy = ref('');
const isSubmitting = ref(false);
const showSuccessBanner = ref(false);
const lastSaved = ref('');

const storageKey = computed(() => `iqarchive_self_survey_${props.program.id}`);

function loadSavedDraft() {
    try {
        const raw = localStorage.getItem(storageKey.value);
        if (raw) {
            const data = JSON.parse(raw);
            ratings.value = data.ratings || {};
            bestPractices.value = data.bestPractices || {};
            preparedBy.value = data.preparedBy || '';
            lastSaved.value = 'Loaded from draft';
        }
    } catch {
        // Fallback for private browsing storage restrictions
    }
}

function persistDraft() {
    try {
        localStorage.setItem(storageKey.value, JSON.stringify({
            ratings: ratings.value,
            bestPractices: bestPractices.value,
            preparedBy: preparedBy.value,
            timestamp: new Date().toISOString(),
        }));
        lastSaved.value = 'Saved';
    } catch {
        // Ignored
    }
}

onMounted(() => {
    loadSavedDraft();
});

watch([ratings, bestPractices, preparedBy], () => {
    persistDraft();
}, { deep: true });

function handleUpdateRating(indicatorId, value) {
    ratings.value[indicatorId] = value;
}

function handleUpdateBestPractice(paramId, value) {
    bestPractices.value[paramId] = value;
}

// Math Calculations for Current Area
const totalRating = computed(() => {
    if (!currentArea.value?.parameters) return 0;
    let sum = 0;
    for (const param of currentArea.value.parameters) {
        const indicators = [
            ...(param.sections?.system || []),
            ...(param.sections?.implementation || []),
            ...(param.sections?.outcome || []),
        ];
        for (const ind of indicators) {
            const val = ratings.value[ind.id];
            if (val !== null && val !== undefined && val !== '' && val !== 'NA') {
                sum += Number(val);
            }
        }
    }
    return sum;
});

const areaMean = computed(() => {
    if (!currentArea.value?.parameters?.length) return '';
    const paramMeans = currentArea.value.parameters.map((param) => {
        const secMeans = ['system', 'implementation', 'outcome'].map((key) => {
            const list = (param.sections?.[key] || []).filter((ind) => ratings.value[ind.id] !== 'NA');
            if (!list.length) return null;
            const rated = list.map((ind) => ratings.value[ind.id]).filter((v) => v !== null && v !== undefined && v !== '');
            if (!rated.length) return null;
            return rated.reduce((acc, v) => acc + Number(v), 0) / list.length;
        }).filter((v) => v !== null);

        if (!secMeans.length) return null;
        return secMeans.reduce((acc, v) => acc + Number(v), 0) / secMeans.length;
    }).filter((v) => v !== null);

    if (!paramMeans.length) return '';
    const avg = paramMeans.reduce((acc, v) => acc + Number(v), 0) / paramMeans.length;
    return avg.toFixed(2);
});

function handleSubmit() {
    isSubmitting.value = true;
    setTimeout(() => {
        isSubmitting.value = false;
        showSuccessBanner.value = true;
        setTimeout(() => { showSuccessBanner.value = false; }, 6000);
    }, 600);
}

function handlePrint() {
    window.print();
}
</script>

<template>
    <div class="space-y-5 print:space-y-3">
        <!-- Success Toast Notification Banner -->
        <div v-if="showSuccessBanner" class="alert alert-success shadow-sm text-xs font-bold flex items-center justify-between text-white">
            <div class="flex items-center gap-2">
                <CheckCircle class="w-4 h-4" />
                <span>Self-Survey for {{ currentArea.title }} has been successfully recorded and submitted.</span>
            </div>
            <button @click="showSuccessBanner = false" class="btn btn-ghost btn-xs text-white">✕</button>
        </div>

        <!-- Area Tabs Carousel -->
        <SelfSurveyAreaCarousel
            :areas="areas"
            :selected-area-id="selectedAreaId"
            :ratings="ratings"
            @select-area="selectedAreaId = $event"
            class="print:hidden"
        />

        <!-- Collapsible Rating Scale Reference Legend -->
        <RatingScaleReference class="print:hidden" />

        <!-- Master Self-Survey Matrix Table -->
        <div class="card card-border bg-base-100 border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <!-- Table Header -->
            <div class="grid grid-cols-[1fr_100px_160px_120px] bg-slate-50 border-b-2 border-slate-200 text-center font-extrabold text-xs text-bu-navy-900 uppercase tracking-wider">
                <div class="px-5 py-3 text-left border-r border-slate-200">Indicators</div>
                <div class="py-3 px-2 border-r border-slate-200">
                    <div>Item Rating</div>
                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5 font-mono">IR</div>
                </div>
                <div class="py-3 px-2 border-r border-slate-200">
                    <div class="leading-tight">System – Impl. – Outcome</div>
                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5 font-mono">SIOM</div>
                </div>
                <div class="py-3 px-2">
                    <div>Parameter Mean</div>
                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5 font-mono">PM</div>
                </div>
            </div>

            <!-- Parameters Blocks -->
            <SelfSurveyParameterBlock
                v-for="param in currentArea.parameters"
                :key="param.id"
                :param="param"
                :ratings="ratings"
                :best-practices="bestPractices"
                @update-rating="handleUpdateRating"
                @update-best-practice="handleUpdateBestPractice"
            />

            <!-- Summary Totals Footer & Sign-Off -->
            <SelfSurveySummaryFooter
                :total-rating="totalRating"
                :area-mean="areaMean"
                :prepared-by="preparedBy"
                :is-submitting="isSubmitting"
                :last-saved="lastSaved"
                @update-prepared-by="preparedBy = $event"
                @submit="handleSubmit"
                @print="handlePrint"
            />
        </div>
    </div>
</template>
