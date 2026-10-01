<!--
================================================================================
IQArchive v2 — Recent Program Evidence Table
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/Hub/RecentEvidenceTable.vue
Role: Displays recent evidence documents with category filter tabs and search.
UI Standard: DaisyUI table, badge, input, btn (< 175 lines).
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { 
    FileText, 
    Eye, 
    Search, 
    X,
    FolderArchive
} from 'lucide-vue-next';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';

const props = defineProps({
    documents: { type: Array, default: () => [] },
    program: { type: Object, required: true },
});

const emit = defineEmits(['selectCategory']);

const searchQuery = ref('');
const selectedCategory = ref('all');
const previewDoc = ref(null);
const isPreviewOpen = ref(false);

function openPreview(doc) {
    previewDoc.value = doc;
    isPreviewOpen.value = true;
}

function closePreview() {
    isPreviewOpen.value = false;
    previewDoc.value = null;
}

const categoryFilters = computed(() => {
    const list = props.documents || [];
    const counts = { all: list.length };
    
    list.forEach((doc) => {
        const catId = doc.category_id || doc.category?.id || 'other';
        counts[catId] = (counts[catId] || 0) + 1;
    });

    const tabs = [{ id: 'all', label: 'All', count: list.length }];
    const seen = new Set();

    list.forEach((doc) => {
        const id = doc.category_id || doc.category?.id;
        const name = doc.category?.name;
        if (id && name && !seen.has(id)) {
            seen.add(id);
            tabs.push({ id, label: name, count: counts[id] || 0 });
        }
    });

    return tabs;
});

const filteredDocuments = computed(() => {
    let list = props.documents || [];

    if (selectedCategory.value !== 'all') {
        list = list.filter((doc) => (doc.category_id || doc.category?.id) === selectedCategory.value);
    }

    if (!searchQuery.value) return list;

    const q = searchQuery.value.toLowerCase().trim();
    return list.filter((doc) => {
        const title = (doc.title || '').toLowerCase();
        const filename = (doc.original_filename || '').toLowerCase();
        const cat = (doc.category?.name || '').toLowerCase();
        const uploader = (doc.uploader?.name || '').toLowerCase();
        return title.includes(q) || filename.includes(q) || cat.includes(q) || uploader.includes(q);
    });
});

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function getStatusBadge(status) {
    switch (status) {
        case 'iqa_appr':
            return { label: 'IQA Approved', class: 'badge-success badge-soft' };
        case 'dean_appr':
        case 'dean_endorsed':
            return { label: 'Dean Endorsed', class: 'badge-info badge-soft' };
        case 'rejected':
            return { label: 'Returned', class: 'badge-error badge-soft' };
        default:
            return { label: 'Draft', class: 'badge-warning badge-soft' };
    }
}
</script>

<template>
    <div class="card card-border bg-base-100 border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
        <!-- Panel Header: Title & Total Count -->
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-2">
                <FolderArchive class="w-4 h-4 text-bu-blue-700" />
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">
                    Program Evidence Repository
                </h3>
                <span class="badge badge-sm bg-slate-100 text-slate-700 border border-slate-200 font-mono text-[11px] tabular-nums font-semibold">
                    {{ documents.length }} {{ documents.length === 1 ? 'file' : 'files' }}
                </span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white">
            <!-- Category Filter Tabs -->
            <div class="flex items-center gap-1.5 flex-wrap select-none">
                <button
                    v-for="tab in categoryFilters"
                    :key="tab.id"
                    type="button"
                    @click="selectedCategory = tab.id"
                    class="btn btn-xs rounded-lg font-semibold transition-all"
                    :class="selectedCategory === tab.id
                        ? 'bg-sidebar-blue text-white shadow-xs'
                        : 'btn-ghost text-slate-600 hover:bg-slate-100'"
                >
                    <span>{{ tab.label }}</span>
                    <span class="opacity-70 text-[10px] tabular-nums">({{ tab.count }})</span>
                </button>
            </div>

            <!-- Integrated Search Bar -->
            <label class="input input-sm input-bordered flex items-center gap-2 bg-base-100 rounded-xl w-full md:w-64 shadow-xs focus-within:border-sidebar-blue focus-within:ring-2 focus-within:ring-sidebar-blue/20 focus-within:outline-hidden transition-all">
                <Search class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search documents..."
                    class="grow text-xs border-none outline-hidden focus:ring-0 bg-transparent"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    @click="searchQuery = ''"
                    class="btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-slate-700 -mr-1"
                    title="Clear search"
                >
                    <X class="w-3 h-3" />
                </button>
            </label>
        </div>

        <!-- Table or Empty State -->
        <div class="overflow-x-auto max-h-95">
            <table v-if="filteredDocuments.length > 0" class="table table-sm table-pin-rows">
                <thead>
                    <tr class="text-slate-500 text-[11px] bg-slate-50">
                        <th class="font-semibold text-center">Document Title</th>
                        <th class="font-semibold text-center">Category</th>
                        <th class="font-semibold text-center">Uploaded By</th>
                        <th class="font-semibold text-center">Date</th>
                        <th class="font-semibold text-center">Status</th>
                        <th class="font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-slate-50/80 transition-colors">
                        <td class="font-medium text-slate-900 max-w-xs truncate py-2.5">
                            <div class="flex items-center gap-2">
                                <DocumentTypeIcon :document="doc" size-class="w-4 h-4" />
                                <button
                                    type="button"
                                    @click="openPreview(doc)"
                                    class="truncate hover:text-bu-blue-700 hover:underline text-left cursor-pointer transition-colors"
                                    :title="doc.title"
                                >
                                    {{ doc.title }}
                                </button>
                            </div>
                        </td>
                        <td class="text-center text-slate-600">
                            <span class="badge badge-ghost badge-xs text-[11px]">
                                {{ doc.category?.name || 'Supporting' }}
                            </span>
                        </td>
                        <td class="text-center text-slate-600">
                            {{ doc.uploader?.name || 'Staff' }}
                        </td>
                        <td class="text-center text-slate-500 font-mono text-[11px] tabular-nums">
                            {{ formatDate(doc.created_at) }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-xs text-[10px] font-medium" :class="getStatusBadge(doc.status).class">
                                {{ getStatusBadge(doc.status).label }}
                            </span>
                        </td>
                        <td class="text-center py-2">
                            <button
                                type="button"
                                @click="openPreview(doc)"
                                class="btn btn-ghost btn-xs text-bu-blue-700 hover:bg-blue-50 gap-1 rounded-md"
                                title="View Document Details & Preview"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>View</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Authentic Empty State -->
            <div v-else class="py-12 px-4 flex flex-col items-center justify-center text-center space-y-2.5">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <FileText class="w-6 h-6 stroke-1.5" />
                </div>
                <div class="max-w-md">
                    <h4 class="text-sm font-bold text-slate-800">
                        {{ searchQuery || selectedCategory !== 'all' ? 'No Matching Evidence Found' : 'No Evidence Files Uploaded Yet' }}
                    </h4>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ searchQuery || selectedCategory !== 'all'
                            ? 'No documents match your active category filter or search query. Try clearing filters.' 
                            : 'This degree program does not have any accreditation documents recorded. Select one of the category dossiers above to view criteria and upload evidence.' 
                        }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Half-Screen Document Preview Drawer -->
        <DocumentPreviewDrawer
            :show="isPreviewOpen"
            :document="previewDoc"
            :program="program"
            @close="closePreview"
        />
    </div>
</template>
