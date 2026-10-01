<!--
================================================================================
IQArchive v2 — Master Colleges & Programs Consolidated Workspace
================================================================================
File: resources/js/Pages/Iqa/Colleges.vue
Role: Desktop workspace for Bicol University colleges, satellites, and programs.
UI Standard: DaisyUI card, table, badge, btn, modal.
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import {
    Landmark,
    GraduationCap,
    Search,
    ChevronRight,
    Plus,
    SlidersHorizontal,
    ImagePlus,
    X,
    Check,
} from 'lucide-vue-next';
import { INITIAL_COLLEGES } from './mockData.js';

// --- State ---
const colleges = ref([...INITIAL_COLLEGES]);
const selectedCollegeId = ref(1); // BU Gubat selected by default

const search = ref('');
const campusFilter = ref('');
const collegeFilter = ref('');
const levelFilter = ref('');

// Modal visibility toggles
const isAddProgramOpen = ref(false);
const isAddCollegeOpen = ref(false);
const isProgramSettingsOpen = ref(false);
const isCollegeSettingsOpen = ref(false);

// Add Program Form
const addProgramForm = ref({
    college_id: '',
    code: '',
    name: '',
    level: 'Candidate Status',
});

// Add College Form
const addCollegeFileInput = ref(null);
const addCollegeLogoPreview = ref(null);
const addCollegeForm = ref({
    campus: 'BU Main Campus (Legazpi)',
    name: '',
    code: '',
});

// Program Settings Form
const programSettingsForm = ref({
    id: null,
    code: '',
    name: '',
    level: 'Candidate Status',
});

// College Settings Form
const collegeSettingsFileInput = ref(null);
const collegeSettingsLogoPreview = ref(null);
const collegeSettingsForm = ref({
    id: null,
    name: '',
    code: '',
    campus: '',
});

// --- Computed Properties ---
const campuses = computed(() => {
    return Array.from(new Set(colleges.value.map(c => c.campus))).filter(Boolean);
});

const totalColleges = computed(() => colleges.value.length);
const totalPrograms = computed(() => {
    return colleges.value.reduce((acc, c) => acc + (c.programs?.length || c.programs_count || 0), 0);
});

const selectedCollege = computed(() => {
    return colleges.value.find(c => c.id === selectedCollegeId.value) || colleges.value[0] || null;
});

const avatarInitials = computed(() => {
    if (!selectedCollege.value) return 'BU';
    if (selectedCollege.value.short_name) return selectedCollege.value.short_name.substring(0, 2).toUpperCase();
    const parts = selectedCollege.value.name.split(/\s+/);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return selectedCollege.value.name.substring(0, 2).toUpperCase();
});

const filteredColleges = computed(() => {
    return colleges.value.filter(col => {
        if (campusFilter.value && col.campus !== campusFilter.value) return false;
        if (collegeFilter.value && col.id !== Number(collegeFilter.value)) return false;
        if (search.value) {
            const q = search.value.toLowerCase();
            const matchesCol = col.name.toLowerCase().includes(q) || col.code.toLowerCase().includes(q) || col.campus.toLowerCase().includes(q);
            const matchesProg = col.programs?.some(p => p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q));
            return matchesCol || matchesProg;
        }
        return true;
    });
});

const groupedColleges = computed(() => {
    const groups = {};
    for (const col of filteredColleges.value) {
        const campusKey = col.campus || 'Other Campuses';
        if (!groups[campusKey]) groups[campusKey] = [];
        groups[campusKey].push(col);
    }
    return groups;
});

const displayedPrograms = computed(() => {
    if (!selectedCollege.value?.programs) return [];
    return selectedCollege.value.programs.filter(prog => {
        if (levelFilter.value && prog.level !== levelFilter.value) return false;
        if (search.value) {
            const q = search.value.toLowerCase();
            const matchesProg = prog.name.toLowerCase().includes(q) || prog.code.toLowerCase().includes(q);
            const matchesCol = selectedCollege.value.name.toLowerCase().includes(q) || selectedCollege.value.code.toLowerCase().includes(q);
            return matchesProg || matchesCol;
        }
        return true;
    });
});

// --- Action Handlers ---
function selectCollege(id) {
    selectedCollegeId.value = id;
}

// Add Program
function openAddProgram() {
    addProgramForm.value = {
        college_id: selectedCollege.value?.id || colleges.value[0]?.id || '',
        code: '',
        name: '',
        level: 'Candidate Status',
    };
    isAddProgramOpen.value = true;
}

function saveAddProgram() {
    if (!addProgramForm.value.code || !addProgramForm.value.name) return;
    const target = colleges.value.find(c => c.id === Number(addProgramForm.value.college_id));
    if (target) {
        if (!target.programs) target.programs = [];
        target.programs.push({
            id: Date.now(),
            code: addProgramForm.value.code,
            name: addProgramForm.value.name,
            level: addProgramForm.value.level,
        });
        target.programs_count = target.programs.length;
    }
    isAddProgramOpen.value = false;
}

// Add College
function openAddCollege() {
    addCollegeForm.value = {
        campus: 'BU Main Campus (Legazpi)',
        name: '',
        code: '',
    };
    addCollegeLogoPreview.value = null;
    isAddCollegeOpen.value = true;
}

function handleAddCollegeLogoChange(event) {
    const file = event.target.files?.[0];
    if (file) {
        addCollegeLogoPreview.value = URL.createObjectURL(file);
    }
}

function saveAddCollege() {
    if (!addCollegeForm.value.name || !addCollegeForm.value.code) return;
    const newId = Date.now();
    const newCollege = {
        id: newId,
        name: addCollegeForm.value.name,
        code: addCollegeForm.value.code,
        short_name: addCollegeForm.value.code,
        avatar_text: addCollegeForm.value.code,
        avatar_bg: 'bg-blue-100 text-blue-700 border-blue-200',
        logo: addCollegeLogoPreview.value || '/bulogo.png',
        campus: addCollegeForm.value.campus,
        programs_count: 0,
        programs: [],
    };
    colleges.value.push(newCollege);
    selectedCollegeId.value = newId;
    isAddCollegeOpen.value = false;
}

// Program Settings
function openProgramSettings(prog) {
    programSettingsForm.value = {
        id: prog.id,
        code: prog.code || '',
        name: prog.name || '',
        level: prog.level || 'Candidate Status',
    };
    isProgramSettingsOpen.value = true;
}

function saveProgramSettings() {
    if (!programSettingsForm.value.code || !programSettingsForm.value.name) return;
    if (selectedCollege.value?.programs) {
        const idx = selectedCollege.value.programs.findIndex(p => p.id === programSettingsForm.value.id);
        if (idx !== -1) {
            selectedCollege.value.programs[idx] = {
                ...selectedCollege.value.programs[idx],
                code: programSettingsForm.value.code,
                name: programSettingsForm.value.name,
                level: programSettingsForm.value.level,
            };
        }
    }
    isProgramSettingsOpen.value = false;
}

// College Settings
function openCollegeSettings(col) {
    collegeSettingsForm.value = {
        id: col.id,
        name: col.name || '',
        code: col.code || '',
        campus: col.campus || '',
    };
    collegeSettingsLogoPreview.value = col.logo || null;
    isCollegeSettingsOpen.value = true;
}

function handleCollegeSettingsLogoChange(event) {
    const file = event.target.files?.[0];
    if (file) {
        collegeSettingsLogoPreview.value = URL.createObjectURL(file);
    }
}

function saveCollegeSettings() {
    if (!collegeSettingsForm.value.name || !collegeSettingsForm.value.code) return;
    const idx = colleges.value.findIndex(c => c.id === collegeSettingsForm.value.id);
    if (idx !== -1) {
        colleges.value[idx] = {
            ...colleges.value[idx],
            name: collegeSettingsForm.value.name,
            code: collegeSettingsForm.value.code,
            short_name: collegeSettingsForm.value.code,
            campus: collegeSettingsForm.value.campus,
            logo: collegeSettingsLogoPreview.value || colleges.value[idx].logo,
        };
    }
    isCollegeSettingsOpen.value = false;
}
</script>

<template>
    <Head title="Colleges & Programs — IQArchive" />

    <AppShell :hide-breadcrumbs="true">
        <div class="max-w-7xl mx-auto pb-12">
            <!-- 1. Header Section -->
            <div class="space-y-2 mb-6">
                <!-- Breadcrumbs -->
                <div class="breadcrumbs text-xs text-slate-500 py-0">
                    <ul>
                        <li><span class="hover:text-slate-800 cursor-pointer">Administration</span></li>
                        <li><span class="text-slate-900 font-semibold">Colleges & Programs</span></li>
                    </ul>
                </div>

                <!-- Title & Header Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900">
                            Colleges & Programs
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Manage Bicol University colleges, campuses, and their degree program directories.
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <button
                            type="button"
                            @click="openAddProgram"
                            class="btn btn-sm btn-outline border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 font-semibold gap-1.5 shadow-2xs rounded-lg"
                        >
                            <Plus class="w-4 h-4 text-slate-600" />
                            <span>Add Program</span>
                        </button>

                        <button
                            type="button"
                            @click="openAddCollege"
                            class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none font-semibold gap-1.5 shadow-2xs rounded-lg"
                        >
                            <Plus class="w-4 h-4 text-white" />
                            <span>Add College</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Top Summary Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-5">
                <!-- Card 1: Colleges & Satellites -->
                <div class="md:col-span-3 card bg-base-100 card-border shadow-2xs rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700">
                            <Landmark class="w-3.5 h-3.5" />
                        </div>
                        <span class="text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                            Colleges & Satellites
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ totalColleges }}
                        </span>
                    </div>
                </div>

                <!-- Card 2: Degree Programs -->
                <div class="md:col-span-3 card bg-base-100 card-border shadow-2xs rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-200/80 flex items-center justify-center text-bu-orange-500">
                            <GraduationCap class="w-4 h-4" />
                        </div>
                        <span class="text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                            Degree Programs
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ totalPrograms }}
                        </span>
                    </div>
                </div>

                <!-- Card 3: Accreditation Progress -->
                <div class="md:col-span-6 card bg-base-100 card-border shadow-2xs rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                            Accreditation Progress
                        </span>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ totalPrograms }} programs total
                        </span>
                    </div>

                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden flex my-2 border border-slate-200/60">
                        <div class="bg-slate-400 h-full rounded-full transition-all duration-300" style="width: 100%"></div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span>Candidate <strong class="text-slate-800">({{ totalPrograms }})</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            <span>Level I <strong class="text-slate-800">(0)</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Level II <strong class="text-slate-800">(0)</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Level III <strong class="text-slate-800">(0)</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                            <span>Level IV <strong class="text-slate-800">(0)</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Integrated Search & Filter Controls -->
            <div class="card bg-base-100 card-border shadow-2xs rounded-xl p-2.5 mb-5">
                <div class="flex flex-col lg:flex-row items-center gap-3">
                    <!-- Search Input with Border -->
                    <div class="relative flex-1 w-full">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search college, program, or campus..."
                            class="input input-sm input-bordered w-full pl-9 pr-3 text-xs text-slate-800 placeholder:text-slate-400 bg-white border-slate-200 rounded-lg focus:border-slate-400"
                        />
                    </div>

                    <!-- Filters Group -->
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full lg:w-auto shrink-0">
                        <select
                            v-model="campusFilter"
                            class="select select-bordered select-sm text-xs rounded-lg text-slate-700 bg-white border-slate-200 font-medium w-full sm:w-auto"
                        >
                            <option value="">All Campuses</option>
                            <option v-for="c in campuses" :key="c" :value="c">
                                {{ c }}
                            </option>
                        </select>

                        <select
                            v-model="collegeFilter"
                            class="select select-bordered select-sm text-xs rounded-lg text-slate-700 bg-white border-slate-200 font-medium w-full sm:w-auto"
                        >
                            <option value="">All Colleges</option>
                            <option v-for="col in colleges" :key="col.id" :value="col.id">
                                {{ col.short_name || col.code }}
                            </option>
                        </select>

                        <select
                            v-model="levelFilter"
                            class="select select-bordered select-sm text-xs rounded-lg text-slate-700 bg-white border-slate-200 font-medium w-full sm:w-auto"
                        >
                            <option value="">All Accreditation Levels</option>
                            <option value="Candidate Status">Candidate Status</option>
                            <option value="Level I">Level I</option>
                            <option value="Level II">Level II</option>
                            <option value="Level III">Level III</option>
                            <option value="Level IV">Level IV</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 4. Main Workspace Grid (Left: Directory, Right: Matrix) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                <!-- Left Column: Colleges Directory -->
                <div class="lg:col-span-4 xl:col-span-4">
                    <div class="card bg-base-100 card-border shadow-2xs rounded-xl overflow-hidden flex flex-col h-full">
                        <div class="p-3.5 pb-2.5 border-b border-slate-100 bg-base-100 shrink-0">
                            <h2 class="text-[11px] font-bold text-slate-400 tracking-wider uppercase">
                                Colleges Directory
                            </h2>
                        </div>

                        <div class="overflow-y-auto max-h-[calc(100vh-21rem)] min-h-[38rem] divide-y divide-slate-100/60">
                            <div v-for="(items, campusName) in groupedColleges" :key="campusName" class="py-1">
                                <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 tracking-wider uppercase bg-slate-50/70">
                                    {{ campusName }} · {{ items.length }}
                                </div>

                                <div class="px-2 py-1 space-y-1">
                                    <button
                                        v-for="col in items"
                                        :key="col.id"
                                        type="button"
                                        @click="selectCollege(col.id)"
                                        class="w-full text-left p-2 rounded-lg flex items-center justify-between gap-2.5 transition-all text-xs"
                                        :class="[
                                            selectedCollegeId === col.id
                                                ? 'bg-slate-100/90 text-slate-900 font-semibold ring-1 ring-slate-200'
                                                : 'hover:bg-slate-50 text-slate-700'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <img
                                                v-if="col.logo"
                                                :src="col.logo"
                                                :alt="col.name"
                                                class="w-8 h-8 rounded-lg object-contain p-0.5 border border-slate-200 bg-white shrink-0 shadow-2xs"
                                            />
                                            <div
                                                v-else
                                                class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-[10px] shrink-0 border"
                                                :class="col.avatar_bg || 'bg-slate-100 text-slate-700 border-slate-200'"
                                            >
                                                {{ col.avatar_text || col.code }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="truncate text-xs font-bold text-slate-800">
                                                    {{ col.name }}
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-medium">
                                                    {{ col.code }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="badge badge-sm bg-slate-900 text-white font-bold rounded-full px-1.5 text-[11px] h-5 min-w-[1.25rem]">
                                                {{ col.programs_count || col.programs?.length || 0 }}
                                            </span>
                                            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div v-if="!filteredColleges.length" class="p-8 text-center text-xs text-slate-400">
                                No colleges match your filter.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Selected College & Degree Programs Matrix -->
                <div class="lg:col-span-8 xl:col-span-8 space-y-4">
                    <!-- Selected College Header Card -->
                    <div v-if="selectedCollege" class="card bg-base-100 card-border shadow-2xs rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3.5">
                            <img
                                v-if="selectedCollege.logo"
                                :src="selectedCollege.logo"
                                :alt="selectedCollege.name"
                                class="w-12 h-12 rounded-xl object-contain p-1 border border-slate-200 bg-white shrink-0 shadow-2xs"
                            />
                            <div
                                v-else
                                class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center font-bold text-blue-700 text-base shrink-0 shadow-2xs"
                            >
                                {{ avatarInitials }}
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 leading-tight">
                                    {{ selectedCollege.name }}
                                </h3>
                                <div class="mt-0.5">
                                    <span class="badge badge-sm badge-soft bg-slate-100 text-slate-600 font-semibold px-2.5 py-0.5 rounded-full text-xs">
                                        {{ selectedCollege.programs?.length || selectedCollege.programs_count || 0 }} Programs
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click="openAddProgram"
                                class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none font-semibold gap-1.5 shadow-2xs rounded-lg"
                            >
                                <Plus class="w-4 h-4 text-white" />
                                <span>Add Program</span>
                            </button>

                            <button
                                type="button"
                                title="College preferences and settings"
                                @click="openCollegeSettings(selectedCollege)"
                                class="btn btn-sm btn-ghost btn-square border border-orange-200 bg-orange-50/50 text-bu-orange-500 hover:bg-orange-100 rounded-lg"
                            >
                                <SlidersHorizontal class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Degree Programs Matrix -->
                    <div>
                        <h4 class="text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                            Degree Programs Matrix
                        </h4>

                        <div class="card bg-base-100 card-border shadow-2xs rounded-xl overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="table w-full text-left">
                                    <thead class="bg-slate-50/70 border-b border-slate-100">
                                        <tr>
                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-36">
                                                Code
                                            </th>
                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                Program Title
                                            </th>
                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-48">
                                                Accreditation Level
                                            </th>
                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-24">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="program in displayedPrograms"
                                            :key="program.id"
                                            class="hover:bg-slate-50/60 transition-colors"
                                        >
                                            <td class="py-3 px-4">
                                                <span class="text-xs font-bold text-blue-600 font-mono tracking-tight">
                                                    {{ program.code }}
                                                </span>
                                            </td>

                                            <td class="py-3 px-4">
                                                <div class="text-xs font-medium text-slate-800 leading-snug">
                                                    {{ program.name }}
                                                </div>
                                            </td>

                                            <td class="py-3 px-4 text-center">
                                                <span class="badge badge-outline border-slate-200 bg-white text-slate-600 text-xs px-3 py-1 font-medium rounded-full shadow-2xs">
                                                    {{ program.level || 'Candidate Status' }}
                                                </span>
                                            </td>

                                            <td class="py-3 px-4 text-center">
                                                <button
                                                    type="button"
                                                    title="Program Settings"
                                                    @click="openProgramSettings(program)"
                                                    class="btn btn-xs btn-ghost btn-square border border-orange-200 bg-orange-50/50 text-bu-orange-500 hover:bg-orange-100 rounded-md"
                                                >
                                                    <SlidersHorizontal class="w-3.5 h-3.5" />
                                                </button>
                                            </td>
                                        </tr>

                                        <tr v-if="!displayedPrograms.length">
                                            <td colspan="4" class="py-12 text-center">
                                                <p class="text-xs text-slate-400 font-medium">
                                                    No degree programs found for this selection.
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Modals (Consolidated) -->

        <!-- Add Degree Program Modal -->
        <dialog v-if="isAddProgramOpen" class="modal modal-open bg-slate-900/60 backdrop-blur-xs z-50">
            <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-bu-orange-500">
                            <GraduationCap class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Add Academic Degree Program</h3>
                            <p class="text-xs text-slate-500">{{ selectedCollege?.name }} ({{ selectedCollege?.code }})</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isAddProgramOpen = false"
                        class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveAddProgram" class="space-y-4 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Target College / Satellite <span class="text-error">*</span>
                        </label>
                        <select
                            v-model="addProgramForm.college_id"
                            class="select select-bordered w-full select-sm text-xs rounded-lg"
                            required
                        >
                            <option v-for="c in colleges" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.code }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Program Code <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="addProgramForm.code"
                            type="text"
                            placeholder="e.g. BUGC-BAT, CS-BSIT, CBEM-BSA"
                            class="input input-bordered w-full input-sm text-xs rounded-lg font-mono"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Full Degree Program Title <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="addProgramForm.name"
                            type="text"
                            placeholder="e.g. Bachelor of Science in Information Technology"
                            class="input input-bordered w-full input-sm text-xs rounded-lg"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Current Accreditation Level <span class="text-error">*</span>
                        </label>
                        <select
                            v-model="addProgramForm.level"
                            class="select select-bordered w-full select-sm text-xs rounded-lg"
                            required
                        >
                            <option value="Candidate Status">Candidate Status</option>
                            <option value="Level I">Level I</option>
                            <option value="Level II">Level II</option>
                            <option value="Level III">Level III</option>
                            <option value="Level IV">Level IV</option>
                        </select>
                    </div>

                    <div class="modal-action pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="isAddProgramOpen = false"
                            class="btn btn-sm btn-ghost text-slate-600 hover:bg-slate-100 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5 rounded-lg"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Save Program</span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- Add College / Satellite Modal -->
        <dialog v-if="isAddCollegeOpen" class="modal modal-open bg-slate-900/60 backdrop-blur-xs z-50">
            <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-bu-orange-500">
                            <Landmark class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Add College or Satellite Campus</h3>
                            <p class="text-xs text-slate-500">Register an academic unit in Bicol University</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isAddCollegeOpen = false"
                        class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveAddCollege" class="space-y-4 pt-3">
                    <!-- Centered Logo Attachment -->
                    <div class="flex flex-col items-center justify-center pb-1">
                        <div
                            @click="addCollegeFileInput?.click()"
                            class="w-20 h-20 rounded-2xl border-2 border-dashed border-slate-300 hover:border-bu-orange-500 bg-slate-50/80 hover:bg-orange-50/30 flex flex-col items-center justify-center cursor-pointer transition-all relative overflow-hidden group shadow-2xs"
                            title="Click to attach college logo"
                        >
                            <img
                                v-if="addCollegeLogoPreview"
                                :src="addCollegeLogoPreview"
                                alt="Attached college logo"
                                class="w-full h-full object-contain p-2"
                            />
                            <div v-else class="flex flex-col items-center justify-center text-slate-400 group-hover:text-bu-orange-500 transition-colors">
                                <ImagePlus class="w-6 h-6 stroke-[1.75]" />
                                <span class="text-[9px] font-semibold mt-1">Logo</span>
                            </div>

                            <div
                                v-if="addCollegeLogoPreview"
                                class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-medium"
                            >
                                Change
                            </div>
                        </div>

                        <input
                            ref="addCollegeFileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleAddCollegeLogoChange"
                        />

                        <div class="flex items-center gap-2 mt-1.5">
                            <button
                                type="button"
                                @click="addCollegeFileInput?.click()"
                                class="text-[11px] font-semibold text-bu-orange-600 hover:underline cursor-pointer"
                            >
                                {{ addCollegeLogoPreview ? 'Change Logo' : 'Attach Logo' }}
                            </button>
                            <button
                                v-if="addCollegeLogoPreview"
                                type="button"
                                @click.stop="addCollegeLogoPreview = null"
                                class="text-[11px] text-slate-400 hover:text-error cursor-pointer"
                            >
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Campus Location -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Campus / Satellite Location <span class="text-error">*</span>
                        </label>
                        <select
                            v-model="addCollegeForm.campus"
                            class="select select-bordered w-full select-sm text-xs rounded-lg"
                            required
                        >
                            <option v-for="c in campuses" :key="c" :value="c">
                                {{ c }}
                            </option>
                        </select>
                    </div>

                    <!-- College Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            College / Academic Unit Name <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="addCollegeForm.name"
                            type="text"
                            placeholder="e.g. College of Science, BU Polangui"
                            class="input input-bordered w-full input-sm text-xs rounded-lg"
                            required
                        />
                    </div>

                    <!-- Official Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Official Code <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="addCollegeForm.code"
                            type="text"
                            placeholder="e.g. CS, BUP, CENG, BUGC"
                            class="input input-bordered w-full input-sm text-xs rounded-lg font-mono uppercase"
                            required
                        />
                    </div>

                    <div class="modal-action pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="isAddCollegeOpen = false"
                            class="btn btn-sm btn-ghost text-slate-600 hover:bg-slate-100 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5 rounded-lg"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Save College</span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- Program Settings Modal -->
        <dialog v-if="isProgramSettingsOpen" class="modal modal-open bg-slate-900/60 backdrop-blur-xs z-50">
            <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-bu-orange-500">
                            <SlidersHorizontal class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Program Settings</h3>
                            <p class="text-xs text-slate-500">Configure program accreditation parameters</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isProgramSettingsOpen = false"
                        class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveProgramSettings" class="space-y-4 pt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Program Code <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="programSettingsForm.code"
                            type="text"
                            class="input input-bordered w-full input-sm text-xs rounded-lg font-mono font-bold text-blue-600"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Degree Program Title <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="programSettingsForm.name"
                            type="text"
                            class="input input-bordered w-full input-sm text-xs rounded-lg"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Current Accreditation Level <span class="text-error">*</span>
                        </label>
                        <select
                            v-model="programSettingsForm.level"
                            class="select select-bordered w-full select-sm text-xs rounded-lg"
                            required
                        >
                            <option value="Candidate Status">Candidate Status</option>
                            <option value="Level I">Level I</option>
                            <option value="Level II">Level II</option>
                            <option value="Level III">Level III</option>
                            <option value="Level IV">Level IV</option>
                        </select>
                    </div>

                    <div class="modal-action pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="isProgramSettingsOpen = false"
                            class="btn btn-sm btn-ghost text-slate-600 hover:bg-slate-100 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5 rounded-lg"
                        >
                            <Check class="w-4 h-4" />
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- College Settings Modal -->
        <dialog v-if="isCollegeSettingsOpen" class="modal modal-open bg-slate-900/60 backdrop-blur-xs z-50">
            <div class="modal-box max-w-lg bg-base-100 p-6 rounded-2xl shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200 flex items-center justify-center text-bu-orange-500">
                            <SlidersHorizontal class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">College Settings</h3>
                            <p class="text-xs text-slate-500">Configure college directory and campus metadata</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isCollegeSettingsOpen = false"
                        class="btn btn-ghost btn-sm btn-circle text-slate-400 hover:text-slate-700"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveCollegeSettings" class="space-y-4 pt-3">
                    <!-- Centered Logo Attachment -->
                    <div class="flex flex-col items-center justify-center pb-1">
                        <div
                            @click="collegeSettingsFileInput?.click()"
                            class="w-20 h-20 rounded-2xl border-2 border-dashed border-slate-300 hover:border-bu-orange-500 bg-slate-50/80 hover:bg-orange-50/30 flex flex-col items-center justify-center cursor-pointer transition-all relative overflow-hidden group shadow-2xs"
                            title="Click to change college logo"
                        >
                            <img
                                v-if="collegeSettingsLogoPreview"
                                :src="collegeSettingsLogoPreview"
                                alt="College logo"
                                class="w-full h-full object-contain p-2"
                            />
                            <div v-else class="flex flex-col items-center justify-center text-slate-400 group-hover:text-bu-orange-500 transition-colors">
                                <ImagePlus class="w-6 h-6 stroke-[1.75]" />
                                <span class="text-[9px] font-semibold mt-1">Logo</span>
                            </div>

                            <div
                                v-if="collegeSettingsLogoPreview"
                                class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-medium"
                            >
                                Change
                            </div>
                        </div>

                        <input
                            ref="collegeSettingsFileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleCollegeSettingsLogoChange"
                        />

                        <div class="flex items-center gap-2 mt-1.5">
                            <button
                                type="button"
                                @click="collegeSettingsFileInput?.click()"
                                class="text-[11px] font-semibold text-bu-orange-600 hover:underline cursor-pointer"
                            >
                                {{ collegeSettingsLogoPreview ? 'Change Logo' : 'Attach Logo' }}
                            </button>
                            <button
                                v-if="collegeSettingsLogoPreview"
                                type="button"
                                @click.stop="collegeSettingsLogoPreview = null"
                                class="text-[11px] text-slate-400 hover:text-error cursor-pointer"
                            >
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- College Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            College / Unit Full Name <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="collegeSettingsForm.name"
                            type="text"
                            class="input input-bordered w-full input-sm text-xs rounded-lg"
                            required
                        />
                    </div>

                    <!-- Official Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Official Code <span class="text-error">*</span>
                        </label>
                        <input
                            v-model="collegeSettingsForm.code"
                            type="text"
                            class="input input-bordered w-full input-sm text-xs rounded-lg font-mono uppercase"
                            required
                        />
                    </div>

                    <!-- Campus Location -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Campus Location <span class="text-error">*</span>
                        </label>
                        <select
                            v-model="collegeSettingsForm.campus"
                            class="select select-bordered w-full select-sm text-xs rounded-lg"
                            required
                        >
                            <option v-for="c in campuses" :key="c" :value="c">
                                {{ c }}
                            </option>
                        </select>
                    </div>

                    <div class="modal-action pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="isCollegeSettingsOpen = false"
                            class="btn btn-sm btn-ghost text-slate-600 hover:bg-slate-100 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-sm bg-bu-orange-500 hover:bg-bu-orange-600 text-white border-none gap-1.5 rounded-lg"
                        >
                            <Check class="w-4 h-4" />
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    </AppShell>
</template>
