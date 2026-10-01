<!--
================================================================================
IQArchive v2 — Accounts Management (User Accounts)
================================================================================
File: resources/js/Pages/Admin/Users/Index.vue
Role Scope: IQA Staff / Administration
Design System Ref: v1 02-accounts.png Reference & DaisyUI 5
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppShell from '@/Layouts/AppShell.vue';
import UserFilterBar from './Partials/UserFilterBar.vue';
import UserTable from './Partials/UserTable.vue';
import PreRegisterModal from './Partials/PreRegisterModal.vue';
import { Users, UserPlus } from 'lucide-vue-next';

const breadcrumbs = [
    { label: 'Administration' },
    { label: 'Accounts Management', active: true },
];

const roleOptions = [
    'Task Force Member',
    'AACCUP Accreditor',
    'BU Executive',
    'College Head',
    'IQA Staff',
];

// Reference accounts conforming 1:1 to v1 02-accounts.png
const mockAccounts = [
    { id: 1, name: 'JANSSEN CARL', email: 'jcmm2023-4700-61428@bicol-u.edu.ph', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSIT', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 2, name: 'Dr. Ramon Bautista', email: 'rbautista@example.com', avatar_url: null, role: 'AACCUP Accreditor', college_code: 'CS', program_code: 'BSCS', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 3, name: 'Maria Santos', email: 'msantos@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSCS', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 4, name: 'Juan Dela Cruz', email: 'jdelacruz@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSIT', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 5, name: 'Prof. Elena Reyes', email: 'ereyes@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSBIO', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 6, name: 'Engr. Sarah Gomez', email: 'sgomez@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CENG', program_code: 'BSCE', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 7, name: 'Engr. Mark Torres', email: 'mtorres@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CENG', program_code: 'BSME', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 8, name: 'BU Executive Admin', email: 'buadmin@example.com', avatar_url: null, role: 'BU Executive', is_institutional: true, college_code: null, program_code: null, status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 9, name: 'Task Force Member', email: 'taskforcemember@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSCS', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 10, name: 'CS Dean Office', email: 'dean@example.com', avatar_url: null, role: 'College Head', college_code: 'CS', program_code: null, status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 11, name: 'Engr. Roberto Diaz', email: 'rdiaz@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CENG', program_code: 'BSEE', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 12, name: 'Dr. Evelyn Morales', email: 'emorales@example.com', avatar_url: null, role: 'College Head', college_code: 'CED', program_code: null, status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 13, name: 'Patricia Gomez', email: 'pgomez@example.com', avatar_url: null, role: 'AACCUP Accreditor', college_code: 'CED', program_code: 'BSED', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
    { id: 14, name: 'Grace Villanueva', email: 'gvillanueva@example.com', avatar_url: null, role: 'Task Force Member', college_code: 'CS', program_code: 'BSMET', status: 'active', created_at: 'Aug 25, 2026', created_ago: '3 weeks ago' },
];

const usersList = ref(mockAccounts);
const searchQuery = ref('');
const selectedRole = ref('all');
const activeTab = ref('all');
const currentPage = ref(1);
const itemsPerPage = 10;

const isModalOpen = ref(false);
const modalDefaultRole = ref('Task Force Member');

const openPreRegisterModal = (role = 'Task Force Member') => {
    modalDefaultRole.value = role;
    isModalOpen.value = true;
};

const handleUserCreated = (newUser) => {
    usersList.value.unshift({
        id: Date.now(),
        name: newUser.name,
        email: newUser.email,
        avatar_url: null,
        role: newUser.role,
        college_code: newUser.college?.includes('Science') ? 'CS' : 'CENG',
        program_code: 'BSIT',
        status: 'active',
        created_at: 'Just now',
        created_ago: 'Today',
    });
};

const handleToggleStatus = (targetUser) => {
    targetUser.status = targetUser.status === 'active' ? 'deactivated' : 'active';
};

const counts = computed(() => ({
    all: usersList.value.length,
    active: usersList.value.filter(u => u.status === 'active').length,
    pending: usersList.value.filter(u => u.status === 'pending').length,
    deactivated: usersList.value.filter(u => u.status === 'deactivated').length,
}));

const filteredUsers = computed(() => {
    return usersList.value.filter(u => {
        if (activeTab.value !== 'all' && u.status !== activeTab.value) return false;
        if (selectedRole.value !== 'all' && u.role !== selectedRole.value) return false;
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            return u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
        }
        return true;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / itemsPerPage)));

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredUsers.value.slice(start, start + itemsPerPage);
});

const showingStart = computed(() => filteredUsers.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage + 1);
const showingEnd = computed(() => Math.min(currentPage.value * itemsPerPage, filteredUsers.value.length));
</script>

<template>
    <Head title="Accounts Management" />

    <AppShell :breadcrumbs="breadcrumbs">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Page Header with Action Buttons -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#0B1B3D]">Accounts Management</h1>
                    <p class="text-xs text-slate-500 mt-1">Pre-register and manage institutional user accounts, roles, and college affiliations.</p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        type="button"
                        class="btn btn-sm bg-[#F26522] hover:bg-[#D95314] text-white font-medium text-xs rounded-lg border-0 shadow-2xs inline-flex items-center gap-2"
                        @click="openPreRegisterModal('Task Force Member')"
                    >
                        <Users class="w-4 h-4 stroke-[2]" />
                        <span>Quick Pre-Register Task Force</span>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm bg-[#0B1B3D] hover:bg-[#132A5C] text-white font-medium text-xs rounded-lg border-0 shadow-2xs inline-flex items-center gap-2"
                        @click="openPreRegisterModal('AACCUP Accreditor')"
                    >
                        <UserPlus class="w-4 h-4 stroke-[2]" />
                        <span>Pre-Register User</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <UserFilterBar
                v-model:searchQuery="searchQuery"
                v-model:selectedRole="selectedRole"
                v-model:activeTab="activeTab"
                :counts="counts"
                :total-showing="filteredUsers.length"
                :role-options="roleOptions"
            />

            <!-- User Accounts Table with Pagination -->
            <UserTable
                :users="paginatedUsers"
                :current-page="currentPage"
                :total-pages="totalPages"
                :total-users="filteredUsers.length"
                :showing-start="showingStart"
                :showing-end="showingEnd"
                @page-change="currentPage = $event"
                @toggle-user-status="handleToggleStatus"
            />
        </div>

        <!-- Pre-Register Dialog Modal -->
        <PreRegisterModal
            :is-open="isModalOpen"
            :default-role="modalDefaultRole"
            :role-options="roleOptions"
            @close="isModalOpen = false"
            @submit="handleUserCreated"
        />
    </AppShell>
</template>
