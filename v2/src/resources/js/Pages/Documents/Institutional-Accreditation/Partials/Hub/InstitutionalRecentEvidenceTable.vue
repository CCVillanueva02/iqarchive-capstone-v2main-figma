<!--
================================================================================
IQArchive v2 — Institutional Evidence Repository Table
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/Hub/InstitutionalRecentEvidenceTable.vue
Role: Displays university-level accreditation evidence files and links with
      search, category filter tabs, status pills, and preview drawer.
UI Standard: DaisyUI table, badge, input, btn (< 150 lines).
================================================================================
-->

<script setup>
import { ref, computed } from 'vue';
import { Eye, Search, FolderArchive, ExternalLink } from 'lucide-vue-next';
import DocumentPreviewDrawer from '@/Components/DocumentPreviewDrawer.vue';
import DocumentTypeIcon from '@/Components/Icons/DocumentTypeIcon.vue';

const props = defineProps({
    documents: { type: Array, default: () => [] },
});

const searchQuery = ref('');
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

const filteredDocuments = computed(() => {
    const list = props.documents || [];
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
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="card card-border bg-base-100 border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Header & Search Toolbar -->
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">
                    Institutional Evidence Repository
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Official documents, university policies, and accreditation exhibits
                </p>
            </div>

            <!-- Search Field -->
            <div class="relative w-full sm:w-64">
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search documents..."
                    class="input input-bordered input-sm w-full pl-9 text-xs rounded-xl bg-slate-50"
                />
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto max-h-96">
            <table v-if="filteredDocuments.length > 0" class="table table-zebra table-pin-rows text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-2.5 pl-4">Evidence Document</th>
                        <th class="py-2.5">Category</th>
                        <th class="py-2.5">Uploader</th>
                        <th class="py-2.5">Date Added</th>
                        <th class="py-2.5 pr-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-slate-50/60 transition-colors">
                        <td class="pl-4 py-2.5 font-medium text-slate-800">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <DocumentTypeIcon :mime-type="doc.mime_type" :filename="doc.original_filename" :external-url="doc.external_url" class="shrink-0" />
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate max-w-xs" :title="doc.title">
                                        {{ doc.title }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">
                                        {{ doc.original_filename || (doc.external_url ? 'Google Drive Link' : 'Document') }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 text-slate-600">
                            <span class="badge badge-xs badge-soft badge-neutral font-medium">
                                {{ doc.category?.name || 'Institutional Evidence' }}
                            </span>
                        </td>
                        <td class="py-2.5 text-slate-600">
                            {{ doc.uploader?.name || 'IQA Staff' }}
                        </td>
                        <td class="py-2.5 text-slate-500 tabular-nums">
                            {{ formatDate(doc.created_at) }}
                        </td>
                        <td class="pr-4 py-2.5 text-right">
                            <a
                                v-if="doc.external_url"
                                :href="doc.external_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-ghost btn-xs text-bu-blue-600 hover:bg-blue-50 gap-1"
                            >
                                <ExternalLink class="w-3.5 h-3.5" />
                                <span>Open Link</span>
                            </a>
                            <button
                                v-else
                                type="button"
                                @click="openPreview(doc)"
                                class="btn btn-ghost btn-xs text-slate-700 hover:text-bu-blue-700 hover:bg-slate-100 gap-1"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>Preview</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-else class="p-10 text-center space-y-2">
                <FolderArchive class="w-8 h-8 text-slate-300 mx-auto" />
                <h4 class="text-xs font-bold text-slate-700">No institutional documents found</h4>
                <p class="text-[11px] text-slate-400">
                    {{ searchQuery ? 'Try adjusting your search criteria.' : 'Official documents will appear here once archived.' }}
                </p>
            </div>
        </div>

        <!-- Global Preview Drawer -->
        <DocumentPreviewDrawer
            :show="isPreviewOpen"
            :document="previewDoc"
            @close="closePreview"
        />
    </div>
</template>
