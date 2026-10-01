<!--
================================================================================
IQArchive v2 — Common Documents Table Partial
================================================================================
File: resources/js/Pages/Documents/Common-Documents/Partials/DocumentsTable.vue
Role: Displays filterable table of documents for the active office.
UI Standard: DaisyUI table, input, btn.
Line count target: < 180 lines.
================================================================================
-->

<script setup>
import { ref } from 'vue';
import { Eye, Search, X } from 'lucide-vue-next';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';
import { formatBytes } from '@/Utils/formatters';

const props = defineProps({
    documents: { type: Array, required: true },
    selectedOffice: { type: Object, default: null },
    filters: { type: Object, default: () => ({ search: '' }) },
    canUpload: { type: Boolean, default: false },
});

const emit = defineEmits(['filter', 'openUpload']);

const searchQuery = ref(props.filters.search || '');
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

function handleSearchChange() {
    emit('filter', {
        search: searchQuery.value,
    });
}

function clearSearch() {
    searchQuery.value = '';
    handleSearchChange();
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <div class="card card-border bg-base-100 shadow-xs flex flex-col h-full min-h-0">
        <!-- Office Header & Search Toolbar -->
        <div class="p-4 border-b border-slate-100 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-bold text-slate-900 leading-snug">
                    {{ selectedOffice ? selectedOffice.name : 'All Common Documents' }}
                </h3>
                <p
                    v-if="selectedOffice?.description"
                    class="text-xs text-slate-500 mt-0.5 truncate"
                    :title="selectedOffice.description"
                >
                    {{ selectedOffice.description }}
                </p>
            </div>

            <!-- Standalone Search Bar -->
            <div class="w-full sm:w-64 shrink-0">
                <label class="input input-sm input-bordered flex items-center gap-2 w-full rounded-lg bg-base-100">
                    <Search class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <input
                        v-model="searchQuery"
                        @input="handleSearchChange"
                        type="text"
                        placeholder="Search documents..."
                        class="grow text-xs"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="clearSearch"
                        class="text-slate-400 hover:text-slate-600 transition cursor-pointer"
                        title="Clear search"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </label>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="overflow-x-auto overflow-y-auto flex-1 min-h-0">
            <table class="table table-zebra table-sm table-fixed w-full">
                <thead>
                    <tr class="text-slate-500 text-[11px] uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                        <th class="py-3.5 pl-6 pr-4 text-left font-semibold">Document</th>
                        <th class="py-3.5 px-3 text-left font-semibold w-32">Uploader</th>
                        <th class="py-3.5 px-3 text-left font-semibold w-28">Date</th>
                        <th class="py-3.5 px-2 text-center font-semibold w-20" title="Times cited in accreditation requirements">Uses</th>
                        <th class="py-3.5 pl-2 pr-4 text-right font-semibold w-20">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="doc in documents" :key="doc.id" class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 pl-6 pr-4">
                            <div class="flex items-start gap-3">
                                <DocumentTypeIcon :document="doc" size-class="w-4 h-4 mt-0.5" />
                                <div class="min-w-0 flex-1 space-y-0.5">
                                    <button
                                        type="button"
                                        @click="openPreview(doc)"
                                        class="text-xs font-bold text-slate-800 hover:text-bu-blue-700 transition text-left cursor-pointer leading-snug block w-full truncate"
                                        :title="doc.title"
                                    >
                                        {{ doc.title }}
                                    </button>
                                    <div
                                        class="text-[11px] text-slate-400 font-mono tabular-nums truncate"
                                        :title="`${doc.original_filename} (${formatBytes(doc.file_size_bytes)})`"
                                    >
                                        {{ doc.original_filename }} &bull; {{ formatBytes(doc.file_size_bytes) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-3 text-xs text-slate-600 truncate">
                            {{ doc.uploader?.name || 'System Admin' }}
                        </td>
                        <td class="py-3.5 px-3 text-xs text-slate-500 font-mono tabular-nums whitespace-nowrap">
                            {{ formatDate(doc.created_at) }}
                        </td>
                        <td class="py-3.5 px-2 text-center whitespace-nowrap">
                            <span
                                v-if="doc.document_links_count > 0"
                                class="badge badge-xs badge-info badge-soft font-semibold"
                                :title="`${doc.document_links_count} active accreditation link(s)`"
                            >
                                {{ doc.document_links_count }} {{ doc.document_links_count === 1 ? 'use' : 'uses' }}
                            </span>
                            <span
                                v-else
                                class="badge badge-xs badge-ghost text-slate-400 font-normal"
                                title="Not yet cited in accreditation"
                            >
                                0 uses
                            </span>
                        </td>
                        <td class="py-3.5 pl-2 pr-6 text-right whitespace-nowrap">
                            <button
                                type="button"
                                @click="openPreview(doc)"
                                class="btn btn-xs btn-ghost text-bu-blue-700 hover:bg-blue-50 gap-1 rounded-md"
                                title="View Document Details & Preview"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>View</span>
                            </button>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="documents.length === 0">
                        <td colspan="5" class="py-12 px-6 text-center">
                            <div class="flex flex-col items-center justify-center space-y-2 max-w-sm mx-auto">
                                <FileText class="w-10 h-10 text-slate-300" />
                                <p class="text-xs font-bold text-slate-700">No documents found</p>
                                <p class="text-[11px] text-slate-400">
                                    {{ searchQuery ? 'No records match your search query.' : 'No records match this office filter. Check back later.' }}
                                </p>
                                <button
                                    v-if="canUpload && !searchQuery"
                                    type="button"
                                    @click="emit('openUpload')"
                                    class="btn btn-xs btn-outline border-sidebar-blue text-sidebar-blue hover:bg-sidebar-blue hover:border-sidebar-blue hover:text-white gap-1.5 mt-2"
                                >   
                                    Upload First Document
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Global Half-Screen Document Preview Drawer -->
        <DocumentPreviewDrawer
            :show="isPreviewOpen"
            :document="previewDoc"
            :office="selectedOffice"
            :hide-status="true"
            @close="closePreview"
        />
    </div>
</template>
