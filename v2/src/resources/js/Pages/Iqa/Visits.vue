<!--
================================================================================
IQArchive v2 — Accreditation Visits Management
================================================================================
File: resources/js/Pages/Iqa/Visits.vue
Role Scope: IQA Staff / Operations
Design Reference: v2/v1-design-screenshots/08-accreditation-and-monitoring/02-visits-index.png
UI Component Standard: DaisyUI 5 (btn, card, badge, modal, input, select, table)
Architecture: Single-file complete UI with reactive side-drawer timeline orchestration.
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import {
    Calendar,
    CalendarPlus,
    Clock,
    Zap,
    CheckCircle2,
    Search,
    Download,
    X,
    ChevronRight,
    AlertCircle,
    Info,
    History,
    Edit3,
    Check,
    AlertTriangle,
} from 'lucide-vue-next';

// -----------------------------------------------------------------------------
// Reactive State & Initial Data
// -----------------------------------------------------------------------------
const searchQuery = ref('');
const selectedStage = ref('all');

// Initial visits dataset with exact matching record from design screenshot
const visits = ref([
    {
        id: 1,
        college_code: 'CS',
        college_name: 'College of Science',
        program_name: 'Bachelor of Science in Information Technology',
        short_title: 'BS Information Technology',
        program_code: 'BSIT',
        status_level: 'Candidate Status',
        status_badge: 'ACTIVE',
        initiated_date: 'Aug 25, 2026',
        initiated_short: 'Aug 25',
        initiated_relative: '3 weeks ago · IQA Staff',
        target_date: 'Sep 01, 2026',
        is_overdue: true,
        overdue_text: '30 days overdue',
        stage_key: 'preparation',
        stage_label: 'Document Preparation',
        stage_badge_class: 'bg-blue-50 text-blue-700 border-blue-200',
        stage_dot_class: 'bg-blue-500',
        assigned_tf: 'Dr. Jane Manuel (Task Force Chair)',
        timeline: [
            {
                id: 1,
                title: 'Visit record created',
                date_tag: 'Aug 25',
                tag_type: 'success',
                description: 'Initiated by IQA Staff for Candidate Status.',
                status: 'completed',
                dot_color: 'bg-emerald-500',
            },
            {
                id: 2,
                title: 'Document Preparation',
                date_tag: 'CURRENT',
                tag_type: 'current',
                description: 'Accreditation instrument, uploads, and reviews are in progress.',
                status: 'current',
                dot_color: 'bg-blue-600',
            },
            {
                id: 3,
                title: 'Target visit date',
                date_tag: 'OVERDUE',
                tag_type: 'overdue',
                description: 'Sep 01, 2026 · 30 days overdue.',
                status: 'overdue',
                dot_color: 'bg-rose-500',
            },
            {
                id: 4,
                title: 'Submitted & Reviewed',
                date_tag: 'PENDING',
                tag_type: 'pending',
                description: 'Awaiting handover to the IQA Office.',
                status: 'pending',
                dot_color: 'border-2 border-slate-300 bg-white',
            },
        ],
    },
]);

// -----------------------------------------------------------------------------
// Timeline Side Panel State
// -----------------------------------------------------------------------------
// When selected, the right "Visit Timeline" box appears alongside the table
const activeTimelineVisit = ref(null);

function toggleTimeline(visit) {
    if (activeTimelineVisit.value && activeTimelineVisit.value.id === visit.id) {
        activeTimelineVisit.value = null;
    } else {
        activeTimelineVisit.value = visit;
    }
}

function closeTimeline() {
    activeTimelineVisit.value = null;
}

// -----------------------------------------------------------------------------
// Modals & Feedback State
// -----------------------------------------------------------------------------
const isRecordModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const isCancelModalOpen = ref(false);
const selectedVisitForAction = ref(null);

const toast = ref({
    show: false,
    message: '',
    type: 'success',
});

function showToast(message, type = 'success') {
    toast.value = { show: true, message, type };
    setTimeout(() => {
        toast.value.show = false;
    }, 3500);
}

// -----------------------------------------------------------------------------
// New Visit Form State
// -----------------------------------------------------------------------------
const newVisitForm = ref({
    college_code: 'CS',
    college_name: 'College of Science',
    program_name: '',
    short_title: '',
    program_code: '',
    status_level: 'Candidate Status',
    target_date: '',
    assigned_tf: '',
});

const availableColleges = [
    { code: 'CS', name: 'College of Science' },
    { code: 'ENG', name: 'College of Engineering' },
    { code: 'CAL', name: 'College of Arts and Letters' },
    { code: 'CBEM', name: 'College of Business, Economics and Management' },
    { code: 'CE', name: 'College of Education' },
    { code: 'CM', name: 'College of Medicine' },
    { code: 'CIT', name: 'College of Industrial Technology' },
];

const availablePrograms = [
    { code: 'BSIT', name: 'Bachelor of Science in Information Technology', short_title: 'BS Information Technology', college: 'CS' },
    { code: 'BSCS', name: 'Bachelor of Science in Computer Science', short_title: 'BS Computer Science', college: 'CS' },
    { code: 'BSBIO', name: 'Bachelor of Science in Biology', short_title: 'BS Biology', college: 'CS' },
    { code: 'BSCE', name: 'Bachelor of Science in Civil Engineering', short_title: 'BS Civil Engineering', college: 'ENG' },
    { code: 'BSEE', name: 'Bachelor of Science in Electrical Engineering', short_title: 'BS Electrical Engineering', college: 'ENG' },
    { code: 'BACOMM', name: 'Bachelor of Arts in Communication', short_title: 'BA Communication', college: 'CAL' },
    { code: 'BSBA', name: 'Bachelor of Science in Business Administration', short_title: 'BS Business Administration', college: 'CBEM' },
];

function onCollegeChange() {
    const matched = availablePrograms.find((p) => p.college === newVisitForm.value.college_code);
    if (matched) {
        newVisitForm.value.program_name = matched.name;
        newVisitForm.value.short_title = matched.short_title;
        newVisitForm.value.program_code = matched.code;
    }
}

function onProgramChange(event) {
    const code = event.target.value;
    const prog = availablePrograms.find((p) => p.code === code);
    if (prog) {
        newVisitForm.value.program_name = prog.name;
        newVisitForm.value.short_title = prog.short_title;
        newVisitForm.value.program_code = prog.code;
    }
}

function openRecordModal() {
    newVisitForm.value = {
        college_code: 'CS',
        college_name: 'College of Science',
        program_name: 'Bachelor of Science in Computer Science',
        short_title: 'BS Computer Science',
        program_code: 'BSCS',
        status_level: 'Candidate Status',
        target_date: '2026-11-15',
        assigned_tf: 'Dr. Maria Santos',
    };
    isRecordModalOpen.value = true;
}

function handleSaveNewVisit() {
    if (!newVisitForm.value.program_name || !newVisitForm.value.target_date) {
        showToast('Please specify an academic program and target visit date.', 'error');
        return;
    }

    const collegeObj = availableColleges.find((c) => c.code === newVisitForm.value.college_code);
    const dateFormatted = new Date(newVisitForm.value.target_date).toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
    });

    const newEntry = {
        id: Date.now(),
        college_code: newVisitForm.value.college_code,
        college_name: collegeObj ? collegeObj.name : newVisitForm.value.college_code,
        program_name: newVisitForm.value.program_name,
        short_title: newVisitForm.value.short_title || newVisitForm.value.program_name,
        program_code: newVisitForm.value.program_code || 'PROG',
        status_level: newVisitForm.value.status_level,
        status_badge: 'ACTIVE',
        initiated_date: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
        initiated_short: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit' }),
        initiated_relative: 'Just now · IQA Staff',
        target_date: dateFormatted,
        is_overdue: false,
        overdue_text: 'Scheduled on time',
        stage_key: 'scheduled',
        stage_label: 'Scheduled / Awaiting TF',
        stage_badge_class: 'bg-amber-50 text-amber-700 border-amber-200',
        stage_dot_class: 'bg-amber-500',
        assigned_tf: newVisitForm.value.assigned_tf || 'To be designated',
        timeline: [
            {
                id: 1,
                title: 'Visit record created',
                date_tag: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit' }),
                tag_type: 'success',
                description: `Initiated by IQA Staff for ${newVisitForm.value.status_level}.`,
                status: 'completed',
                dot_color: 'bg-emerald-500',
            },
            {
                id: 2,
                title: 'Task Force Formation',
                date_tag: 'CURRENT',
                tag_type: 'current',
                description: 'Pending committee designation and dean endorsement.',
                status: 'current',
                dot_color: 'bg-blue-600',
            },
            {
                id: 3,
                title: 'Target visit date',
                date_tag: dateFormatted,
                tag_type: 'pending',
                description: `Target survey date: ${dateFormatted}.`,
                status: 'pending',
                dot_color: 'border-2 border-slate-300 bg-white',
            },
            {
                id: 4,
                title: 'Submitted & Reviewed',
                date_tag: 'PENDING',
                tag_type: 'pending',
                description: 'Awaiting handover to the IQA Office.',
                status: 'pending',
                dot_color: 'border-2 border-slate-300 bg-white',
            },
        ],
    };

    visits.value.unshift(newEntry);
    isRecordModalOpen.value = false;
    showToast(`Accreditation visit for ${newEntry.program_code} recorded successfully!`, 'success');
}

// -----------------------------------------------------------------------------
// Update Visit Modal Actions
// -----------------------------------------------------------------------------
const updateForm = ref({
    stage_key: 'preparation',
    target_date: '',
    notes: '',
});

function openUpdateVisitModal() {
    if (!activeTimelineVisit.value) return;
    updateForm.value = {
        stage_key: activeTimelineVisit.value.stage_key,
        target_date: activeTimelineVisit.value.target_date,
        notes: '',
    };
    isUpdateModalOpen.value = true;
}

function handleSaveUpdate() {
    if (!activeTimelineVisit.value) return;

    if (updateForm.value.stage_key === 'submitted') {
        activeTimelineVisit.value.stage_key = 'submitted';
        activeTimelineVisit.value.stage_label = 'Submitted & Reviewed';
        activeTimelineVisit.value.stage_badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        activeTimelineVisit.value.stage_dot_class = 'bg-emerald-500';
    } else if (updateForm.value.stage_key === 'scheduled') {
        activeTimelineVisit.value.stage_key = 'scheduled';
        activeTimelineVisit.value.stage_label = 'Scheduled / Awaiting TF';
        activeTimelineVisit.value.stage_badge_class = 'bg-amber-50 text-amber-700 border-amber-200';
        activeTimelineVisit.value.stage_dot_class = 'bg-amber-500';
    } else {
        activeTimelineVisit.value.stage_key = 'preparation';
        activeTimelineVisit.value.stage_label = 'Document Preparation';
        activeTimelineVisit.value.stage_badge_class = 'bg-blue-50 text-blue-700 border-blue-200';
        activeTimelineVisit.value.stage_dot_class = 'bg-blue-500';
    }

    isUpdateModalOpen.value = false;
    showToast(`Visit milestones for ${activeTimelineVisit.value.program_code} updated!`, 'success');
}

// -----------------------------------------------------------------------------
// Cancel / Delete Actions
// -----------------------------------------------------------------------------
function openCancelModal(visit) {
    selectedVisitForAction.value = visit;
    isCancelModalOpen.value = true;
}

function handleConfirmCancel() {
    if (!selectedVisitForAction.value) return;
    const deletedProg = selectedVisitForAction.value.program_code;
    if (activeTimelineVisit.value && activeTimelineVisit.value.id === selectedVisitForAction.value.id) {
        activeTimelineVisit.value = null;
    }
    visits.value = visits.value.filter((v) => v.id !== selectedVisitForAction.value.id);
    isCancelModalOpen.value = false;
    selectedVisitForAction.value = null;
    showToast(`Visit record for ${deletedProg} removed from active operations.`, 'info');
}

// -----------------------------------------------------------------------------
// Export Simulation
// -----------------------------------------------------------------------------
function handleExport() {
    showToast('Exporting accreditation visits schedule (CSV format)...', 'success');
}

// -----------------------------------------------------------------------------
// Computed Metrics and Filtered Records
// -----------------------------------------------------------------------------
const totalVisitsCount = computed(() => visits.value.length);

const scheduledCount = computed(() => {
    return visits.value.filter((v) => v.stage_key === 'scheduled').length;
});

const inPrepCount = computed(() => {
    return visits.value.filter((v) => v.stage_key === 'preparation').length;
});

const submittedCount = computed(() => {
    return visits.value.filter((v) => v.stage_key === 'submitted').length;
});

const filteredVisits = computed(() => {
    return visits.value.filter((item) => {
        if (selectedStage.value !== 'all' && item.stage_key !== selectedStage.value) {
            return false;
        }

        if (!searchQuery.value.trim()) return true;
        const q = searchQuery.value.toLowerCase();
        return (
            item.program_name.toLowerCase().includes(q) ||
            item.program_code.toLowerCase().includes(q) ||
            item.college_name.toLowerCase().includes(q) ||
            item.college_code.toLowerCase().includes(q)
        );
    });
});
</script>

<template>
    <Head title="Accreditation Visits — IQArchive" />

    <AppShell :hide-topbar="true" :hide-breadcrumbs="true">
        <!-- Toast Feedback Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="toast.show" class="fixed top-5 right-5 z-50 max-w-sm">
                <div
                    class="alert shadow-lg text-xs font-semibold py-2.5 px-4 flex items-center gap-2"
                    :class="{
                        'alert-success text-white': toast.type === 'success',
                        'alert-error text-white': toast.type === 'error',
                        'alert-info text-white': toast.type === 'info',
                    }"
                >
                    <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 shrink-0" />
                    <AlertCircle v-else-if="toast.type === 'error'" class="w-4 h-4 shrink-0" />
                    <Info v-else class="w-4 h-4 shrink-0" />
                    <span>{{ toast.message }}</span>
                </div>
            </div>
        </transition>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- =============================================================== -->
            <!-- 1. BREADCRUMBS & TOP LEVEL PAGE HEADER                          -->
            <!-- =============================================================== -->
            <div>
                <!-- Institutional Breadcrumbs -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1.5">
                    <span class="hover:text-slate-700 cursor-pointer">Operations</span>
                    <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                    <span class="font-bold text-slate-900">Accreditation Visits</span>
                </div>

                <!-- Main Header Title & Actions Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                            Accreditation Visits
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">
                            Record, schedule, and track end-to-end accreditation visit preparation and milestones.
                        </p>
                    </div>

                    <!-- Header Action Buttons -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Export Button -->
                        <button
                            type="button"
                            @click="handleExport"
                            class="btn btn-sm btn-outline border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 text-slate-700 font-bold text-xs rounded-lg gap-2 h-9 px-3.5 shadow-2xs transition-all"
                        >
                            <Download class="w-3.5 h-3.5 text-slate-600" />
                            <span>Export visit log</span>
                        </button>

                        <!-- Record New Visit Button (BU Orange #F26522) -->
                        <button
                            type="button"
                            @click="openRecordModal"
                            class="btn btn-sm bg-[#F26522] hover:bg-[#D95316] text-white border-none font-bold text-xs rounded-lg gap-2 h-9 px-4 shadow-xs transition-all"
                        >
                            <CalendarPlus class="w-3.5 h-3.5" />
                            <span>Record New Visit</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- 2. FOUR ACCREDITATION METRICS CARDS                             -->
            <!-- =============================================================== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: TOTAL RECORDED VISITS -->
                <div class="card bg-white border border-slate-200/90 rounded-xl p-4 shadow-2xs hover:shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <span class="text-[11px] font-bold tracking-wider text-slate-600 uppercase">
                            TOTAL RECORDED VISITS
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                            <Calendar class="w-3.5 h-3.5" />
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ totalVisitsCount }}
                    </div>
                    <p class="text-xs text-slate-400 font-normal mt-1">
                        Across all academic programs
                    </p>
                </div>

                <!-- Card 2: SCHEDULED / AWAITING TF -->
                <div class="card bg-white border border-slate-200/90 rounded-xl p-4 shadow-2xs hover:shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <span class="text-[11px] font-bold tracking-wider text-amber-600 uppercase">
                            SCHEDULED / AWAITING TF
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <Clock class="w-3.5 h-3.5" />
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ scheduledCount }}
                    </div>
                    <p class="text-xs text-slate-400 font-normal mt-1">
                        Pending task force formation
                    </p>
                </div>

                <!-- Card 3: IN ACTIVE PREPARATION -->
                <div class="card bg-white border border-slate-200/90 rounded-xl p-4 shadow-2xs hover:shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <span class="text-[11px] font-bold tracking-wider text-orange-600 uppercase">
                            IN ACTIVE PREPARATION
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                            <Zap class="w-3.5 h-3.5" />
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ inPrepCount }}
                    </div>
                    <p class="text-xs text-slate-400 font-normal mt-1">
                        Instrument, uploads & reviews
                    </p>
                </div>

                <!-- Card 4: SUBMITTED & REVIEWED -->
                <div class="card bg-white border border-slate-200/90 rounded-xl p-4 shadow-2xs hover:shadow-xs transition-all">
                    <div class="flex items-start justify-between">
                        <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase">
                            SUBMITTED & REVIEWED
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <CheckCircle2 class="w-3.5 h-3.5" />
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-2">
                        {{ submittedCount }}
                    </div>
                    <p class="text-xs text-slate-400 font-normal mt-1">
                        Handed over to IQA Office
                    </p>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- 3. SEARCH & STAGE FILTER BAR                                    -->
            <!-- =============================================================== -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <!-- Search Input with Search Icon -->
                <div class="relative flex-1 w-full max-w-xl">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by program name, code, or college..."
                        class="input input-sm w-full pl-9 bg-slate-50/70 border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-[#F26522] focus:outline-hidden rounded-lg transition-all"
                    />
                </div>

                <!-- Stage Dropdown & Results Counter -->
                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold tracking-wider uppercase text-slate-500">Stage:</span>
                        <select
                            v-model="selectedStage"
                            class="select select-sm select-bordered text-xs font-semibold text-slate-700 bg-white border-slate-200 rounded-lg min-w-[130px]"
                        >
                            <option value="all">All Stages</option>
                            <option value="preparation">Document Preparation</option>
                            <option value="scheduled">Scheduled / Awaiting TF</option>
                            <option value="submitted">Submitted & Reviewed</option>
                        </select>
                    </div>

                    <!-- Dot Counter -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-600 font-semibold pl-3 border-l border-slate-200 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>{{ filteredVisits.length }} result{{ filteredVisits.length === 1 ? '' : 's' }}</span>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- 4. MAIN WORKSPACE: VISIT RECORDS + TIMELINE SIDE PANEL          -->
            <!-- =============================================================== -->
            <div class="grid grid-cols-1 gap-6 items-start" :class="{ 'lg:grid-cols-12': activeTimelineVisit }">
                <!-- ----------------------------------------------------------- -->
                <!-- LEFT COLUMN: VISIT RECORDS TABLE                            -->
                <!-- ----------------------------------------------------------- -->
                <div
                    class="card bg-white border border-slate-200/90 shadow-2xs rounded-xl overflow-hidden transition-all"
                    :class="activeTimelineVisit ? 'lg:col-span-8' : 'w-full'"
                >
                    <!-- Card Header -->
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Visit Records
                            </h2>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ filteredVisits.length === 1 ? 'One visit matches the current stage and search filters.' : `${filteredVisits.length} visits match the current stage and search filters.` }}
                            </p>
                        </div>

                        <!-- Results Pill -->
                        <span class="badge badge-sm bg-blue-50 text-blue-700 border border-blue-200/70 font-semibold px-2.5 py-0.5 rounded-full">
                            {{ filteredVisits.length }} visit{{ filteredVisits.length === 1 ? '' : 's' }}
                        </span>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="table table-pin-rows w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/90 text-slate-500 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-5">COLLEGE & PROGRAM</th>
                                    <th class="py-3 px-5">INITIATED DATE</th>
                                    <th class="py-3 px-5">TARGET VISIT DATE</th>
                                    <th class="py-3 px-5">CURRENT STAGE</th>
                                    <th class="py-3 px-5 text-right">TIMELINE & ACTIONS</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <!-- Empty Filter State -->
                                <tr v-if="filteredVisits.length === 0">
                                    <td colspan="5" class="py-12 text-center">
                                        <div class="max-w-sm mx-auto flex flex-col items-center">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                                <Search class="w-6 h-6" />
                                            </div>
                                            <p class="text-sm font-bold text-slate-800">No matching visits found</p>
                                            <p class="text-xs text-slate-400 mt-1">
                                                Try adjusting your search terms or clearing the stage filter.
                                            </p>
                                            <button
                                                type="button"
                                                @click="searchQuery = ''; selectedStage = 'all';"
                                                class="btn btn-xs btn-outline border-slate-200 mt-3 text-slate-600 font-semibold"
                                            >
                                                Reset Filters
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Active Visit Rows -->
                                <tr
                                    v-for="visit in filteredVisits"
                                    :key="visit.id"
                                    class="transition-colors group"
                                    :class="activeTimelineVisit && activeTimelineVisit.id === visit.id ? 'bg-blue-50/40' : 'hover:bg-slate-50/70'"
                                >
                                    <!-- 1. COLLEGE & PROGRAM -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3.5">
                                            <!-- College Initials Box (CS in soft blue box) -->
                                            <div class="w-9 h-9 rounded-lg bg-blue-100/70 text-blue-700 border border-blue-200/50 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                {{ visit.college_code }}
                                            </div>

                                            <!-- Program Name & Status -->
                                            <div class="min-w-0">
                                                <div
                                                    @click="toggleTimeline(visit)"
                                                    class="font-bold text-sm text-slate-900 group-hover:text-[#F26522] transition-colors cursor-pointer leading-snug"
                                                >
                                                    {{ visit.program_name }}
                                                </div>
                                                <div class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-1.5">
                                                    <span>{{ visit.program_code }}</span>
                                                    <span class="text-slate-300">•</span>
                                                    <span>{{ visit.status_level }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 2. INITIATED DATE -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                            <Calendar class="w-3.5 h-3.5 text-slate-400" />
                                            <span>{{ visit.initiated_date }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                                            {{ visit.initiated_relative }}
                                        </div>
                                    </td>

                                    <!-- 3. TARGET VISIT DATE -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800">
                                            <Clock class="w-3.5 h-3.5 text-amber-500" />
                                            <span>{{ visit.target_date }}</span>
                                        </div>
                                        <div
                                            v-if="visit.is_overdue"
                                            class="text-[11px] text-rose-600 font-bold mt-0.5"
                                        >
                                            {{ visit.overdue_text }}
                                        </div>
                                        <div
                                            v-else
                                            class="text-[11px] text-emerald-600 font-semibold mt-0.5"
                                        >
                                            {{ visit.overdue_text }}
                                        </div>
                                    </td>

                                    <!-- 4. CURRENT STAGE -->
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border"
                                            :class="visit.stage_badge_class"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="visit.stage_dot_class"></span>
                                            {{ visit.stage_label }}
                                        </span>
                                    </td>

                                    <!-- 5. TIMELINE & ACTIONS -->
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Timeline View Button -->
                                            <button
                                                type="button"
                                                @click="toggleTimeline(visit)"
                                                class="btn btn-sm text-white border-none text-xs font-semibold rounded-lg px-3.5 h-8 shadow-2xs transition-all"
                                                :class="activeTimelineVisit && activeTimelineVisit.id === visit.id ? 'bg-[#F26522] hover:bg-[#D95316]' : 'bg-[#0B1E38] hover:bg-[#162d4e]'"
                                            >
                                                Timeline View
                                            </button>

                                            <!-- Cancel / Remove Action Button -->
                                            <button
                                                type="button"
                                                @click="openCancelModal(visit)"
                                                class="btn btn-sm btn-ghost border border-slate-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-400 rounded-lg w-8 h-8 p-0 flex items-center justify-center transition-colors"
                                                title="Cancel or remove visit record"
                                            >
                                                <X class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ----------------------------------------------------------- -->
                <!-- RIGHT COLUMN: VISIT TIMELINE PANEL (OPENS ON CLICK)         -->
                <!-- ----------------------------------------------------------- -->
                <div
                    v-if="activeTimelineVisit"
                    class="lg:col-span-4 card bg-white border border-slate-200/90 shadow-2xs rounded-xl p-5 flex flex-col justify-between space-y-4"
                >
                    <div>
                        <!-- Header: Visit Timeline -->
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-none">
                                    Visit Timeline
                                </h3>
                                <p class="text-xs text-slate-400 font-medium mt-1">
                                    Milestones and activity for the selected visit.
                                </p>
                            </div>
                            <!-- History Icon in Top Right -->
                            <div class="text-slate-400 hover:text-slate-600 transition-colors">
                                <History class="w-4 h-4" />
                            </div>
                        </div>

                        <!-- Selected Visit Information Pill Container -->
                        <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3 flex items-center justify-between mt-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- CS Avatar -->
                                <div class="w-8 h-8 rounded-lg bg-blue-100/70 text-blue-700 border border-blue-200/50 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    {{ activeTimelineVisit.college_code }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-slate-900 truncate">
                                        {{ activeTimelineVisit.short_title }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 font-medium truncate mt-0.5">
                                        {{ activeTimelineVisit.program_code }} · {{ activeTimelineVisit.status_level }}
                                    </p>
                                </div>
                            </div>

                            <!-- ACTIVE Pill -->
                            <span class="badge badge-xs bg-blue-50 text-blue-700 border border-blue-200 font-bold tracking-wider text-[10px] px-2 py-0.5 rounded shrink-0">
                                {{ activeTimelineVisit.status_badge }}
                            </span>
                        </div>

                        <!-- Vertical Timeline List -->
                        <div class="relative pl-6 space-y-5 my-5 before:absolute before:inset-0 before:left-[7px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200">
                            <!-- Timeline Entry Item -->
                            <div
                                v-for="item in activeTimelineVisit.timeline"
                                :key="item.id"
                                class="relative"
                            >
                                <!-- Timeline Bullet / Dot Node -->
                                <span
                                    class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full ring-4 ring-white"
                                    :class="item.dot_color"
                                ></span>

                                <!-- Node Content Row -->
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 leading-snug">
                                            {{ item.title }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 font-normal mt-0.5 leading-relaxed">
                                            {{ item.description }}
                                        </p>
                                    </div>

                                    <!-- Node Right Label / Status -->
                                    <span
                                        class="text-[11px] font-bold tracking-wider shrink-0"
                                        :class="{
                                            'text-emerald-600': item.tag_type === 'success',
                                            'text-blue-600': item.tag_type === 'current',
                                            'text-rose-600': item.tag_type === 'overdue',
                                            'text-slate-400': item.tag_type === 'pending',
                                        }"
                                    >
                                        {{ item.date_tag }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Panel Action Buttons at Bottom -->
                    <div class="pt-4 border-t border-slate-100 flex items-center gap-2">
                        <!-- Update Visit Button (Deep Navy #0B1E38) -->
                        <button
                            type="button"
                            @click="openUpdateVisitModal"
                            class="btn btn-sm bg-[#0B1E38] hover:bg-[#162d4e] text-white border-none font-semibold text-xs rounded-lg gap-2 flex-1 h-9 shadow-2xs transition-all"
                        >
                            <Edit3 class="w-3.5 h-3.5" />
                            <span>Update Visit</span>
                        </button>

                        <!-- Close Timeline Button -->
                        <button
                            type="button"
                            @click="closeTimeline"
                            class="btn btn-sm btn-ghost border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-xs rounded-lg px-4 h-9 transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- 5. MODAL: RECORD NEW ACCREDITATION VISIT                            -->
        <!-- =================================================================== -->
        <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': isRecordModalOpen }">
            <div class="modal-box max-w-xl bg-white rounded-2xl p-6 text-slate-900 border border-slate-200 shadow-xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#F26522] flex items-center justify-center">
                            <CalendarPlus class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Record New Visit</h3>
                            <p class="text-xs text-slate-500">Schedule survey visit milestones for an academic program</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isRecordModalOpen = false"
                        class="btn btn-sm btn-ghost btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form @submit.prevent="handleSaveNewVisit" class="space-y-4 py-4">
                    <!-- College Selection -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Academic College</label>
                        <select
                            v-model="newVisitForm.college_code"
                            @change="onCollegeChange"
                            class="select select-bordered select-sm w-full text-xs font-semibold bg-white"
                        >
                            <option v-for="col in availableColleges" :key="col.code" :value="col.code">
                                [{{ col.code }}] {{ col.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Program Selection -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Program</label>
                        <select
                            :value="newVisitForm.program_code"
                            @change="onProgramChange"
                            class="select select-bordered select-sm w-full text-xs font-semibold bg-white"
                        >
                            <option
                                v-for="prog in availablePrograms.filter((p) => p.college === newVisitForm.college_code)"
                                :key="prog.code"
                                :value="prog.code"
                            >
                                {{ prog.name }} ({{ prog.code }})
                            </option>
                        </select>
                    </div>

                    <!-- Target Level & Target Date in 2 columns -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Target Accreditation Level</label>
                            <select
                                v-model="newVisitForm.status_level"
                                class="select select-bordered select-sm w-full text-xs font-semibold bg-white"
                            >
                                <option value="Candidate Status">Candidate Status</option>
                                <option value="Level I">Level I Accredited</option>
                                <option value="Level II">Level II Re-accredited</option>
                                <option value="Level III">Level III Qualifying</option>
                                <option value="Level IV">Level IV Institutional</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Target Visit Date</label>
                            <input
                                v-model="newVisitForm.target_date"
                                type="date"
                                class="input input-bordered input-sm w-full text-xs font-semibold bg-white"
                                required
                            />
                        </div>
                    </div>

                    <!-- Task Force Lead -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Assigned Task Force Chair</label>
                        <input
                            v-model="newVisitForm.assigned_tf"
                            type="text"
                            placeholder="e.g. Dr. Jane Manuel (Department Head)"
                            class="input input-bordered input-sm w-full text-xs bg-white"
                        />
                    </div>
                </form>

                <!-- Modal Actions -->
                <div class="modal-action pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="isRecordModalOpen = false"
                        class="btn btn-sm btn-ghost text-slate-600 text-xs font-semibold"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="handleSaveNewVisit"
                        class="btn btn-sm bg-[#F26522] hover:bg-[#D95316] text-white border-none font-bold text-xs px-4"
                    >
                        Schedule & Record Visit
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="isRecordModalOpen = false">close</button>
            </form>
        </dialog>

        <!-- =================================================================== -->
        <!-- 6. MODAL: UPDATE VISIT MILESTONES & STAGE                           -->
        <!-- =================================================================== -->
        <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': isUpdateModalOpen }">
            <div v-if="activeTimelineVisit" class="modal-box max-w-md bg-white rounded-2xl p-6 text-slate-900 border border-slate-200 shadow-xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <Edit3 class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Update Visit Milestones</h3>
                            <p class="text-xs text-slate-500">{{ activeTimelineVisit.program_code }} · {{ activeTimelineVisit.status_level }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isUpdateModalOpen = false"
                        class="btn btn-sm btn-ghost btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Update Form -->
                <form @submit.prevent="handleSaveUpdate" class="space-y-4 py-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Current Operational Stage</label>
                        <select
                            v-model="updateForm.stage_key"
                            class="select select-bordered select-sm w-full text-xs font-semibold bg-white"
                        >
                            <option value="preparation">Document Preparation</option>
                            <option value="scheduled">Scheduled / Awaiting TF</option>
                            <option value="submitted">Submitted & Reviewed</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Target Visit Date</label>
                        <input
                            v-model="updateForm.target_date"
                            type="text"
                            class="input input-bordered input-sm w-full text-xs font-semibold bg-white"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">Update Log Note</label>
                        <textarea
                            v-model="updateForm.notes"
                            placeholder="Add brief activity note for the visit milestone..."
                            class="textarea textarea-bordered textarea-sm w-full text-xs bg-white h-20"
                        ></textarea>
                    </div>
                </form>

                <!-- Actions -->
                <div class="modal-action pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="isUpdateModalOpen = false"
                        class="btn btn-sm btn-ghost text-slate-600 text-xs font-semibold"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="handleSaveUpdate"
                        class="btn btn-sm bg-[#0B1E38] hover:bg-[#162d4e] text-white border-none font-bold text-xs px-4"
                    >
                        Save Milestones
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="isUpdateModalOpen = false">close</button>
            </form>
        </dialog>

        <!-- =================================================================== -->
        <!-- 7. MODAL: CANCEL / DELETE CONFIRMATION                              -->
        <!-- =================================================================== -->
        <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': isCancelModalOpen }">
            <div v-if="selectedVisitForAction" class="modal-box max-w-sm bg-white rounded-2xl p-6 text-slate-900 border border-slate-200 shadow-xl">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <AlertTriangle class="w-5 h-5" />
                </div>
                <h3 class="text-base font-bold text-slate-900 text-center">Remove Visit Record?</h3>
                <p class="text-xs text-slate-500 text-center mt-1">
                    Are you sure you want to cancel the scheduled accreditation visit for
                    <span class="font-bold text-slate-800">{{ selectedVisitForAction.program_code }}</span>?
                </p>

                <div class="modal-action grid grid-cols-2 gap-2 pt-4">
                    <button
                        type="button"
                        @click="isCancelModalOpen = false"
                        class="btn btn-sm btn-ghost border border-slate-200 text-xs font-semibold"
                    >
                        Keep Visit
                    </button>
                    <button
                        type="button"
                        @click="handleConfirmCancel"
                        class="btn btn-sm btn-error text-white text-xs font-bold"
                    >
                        Yes, Remove
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button @click="isCancelModalOpen = false">close</button>
            </form>
        </dialog>
    </AppShell>
</template>
