<!--
================================================================================
IQArchive v2 — PDF Document Viewer Component
================================================================================
File: resources/js/Components/PdfViewer.vue
Role: Renders PDF documents using vue-pdf-embed (Mozilla PDF.js) with DaisyUI controls.
UI Standard: DaisyUI join, btn-xs, badge, loading spinner, and alert components.
================================================================================
-->

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import VuePdfEmbed from 'vue-pdf-embed';
import 'vue-pdf-embed/dist/styles/annotationLayer.css';
import 'vue-pdf-embed/dist/styles/textLayer.css';
import {
    ChevronLeft,
    ChevronRight,
    ZoomIn,
    ZoomOut,
    RotateCw,
    Columns,
    Layers,
    RotateCcw,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    source: {
        type: String,
        required: true,
    },
    fileName: {
        type: String,
        default: 'document.pdf',
    },
});

const containerRef = ref(null);
const pdfEmbedRef = ref(null);

// Viewer State
const pageCount = ref(1);
const currentPage = ref(1);
const viewMode = ref('all'); // 'all' (continuous scroll) | 'single' (page-by-page)
const rotation = ref(0);
const zoomScale = ref(1.0);
const containerWidth = ref(600);
const isLoading = ref(true);
const renderError = ref(null);

// Measure container width for responsive fit
function updateContainerWidth() {
    if (containerRef.value) {
        // Leave room for padding (16px * 2 = 32px)
        const measured = containerRef.value.clientWidth - 32;
        if (measured > 100) {
            containerWidth.value = measured;
        }
    }
}

let resizeObserver = null;
onMounted(() => {
    updateContainerWidth();
    if (window.ResizeObserver && containerRef.value) {
        resizeObserver = new ResizeObserver(() => {
            updateContainerWidth();
        });
        resizeObserver.observe(containerRef.value);
    }
});

onUnmounted(() => {
    if (resizeObserver) {
        resizeObserver.disconnect();
    }
});

// Reset state when source changes
watch(() => props.source, () => {
    currentPage.value = 1;
    rotation.value = 0;
    zoomScale.value = 1.0;
    isLoading.value = true;
    renderError.value = null;
    updateContainerWidth();
});

// Computed rendered width in pixels
const renderedWidth = computed(() => {
    return Math.round(containerWidth.value * zoomScale.value);
});

// Event Handlers
function onLoaded(pdfDoc) {
    pageCount.value = pdfDoc.numPages || 1;
    isLoading.value = false;
    renderError.value = null;
}

function onLoadingFailed(err) {
    isLoading.value = false;
    renderError.value = err?.message || 'Failed to load PDF document.';
}

function onRendered() {
    isLoading.value = false;
}

function onRenderingFailed(err) {
    isLoading.value = false;
    renderError.value = err?.message || 'Failed to render PDF page.';
}

// Navigation Controls
function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function nextPage() {
    if (currentPage.value < pageCount.value) {
        currentPage.value++;
    }
}

function toggleViewMode() {
    viewMode.value = viewMode.value === 'all' ? 'single' : 'all';
}

// Zoom Controls (min 50%, max 250%, step 15%)
function zoomIn() {
    zoomScale.value = Math.min(2.5, parseFloat((zoomScale.value + 0.15).toFixed(2)));
}

function zoomOut() {
    zoomScale.value = Math.max(0.5, parseFloat((zoomScale.value - 0.15).toFixed(2)));
}

function resetZoom() {
    zoomScale.value = 1.0;
}

// Rotation (90 deg clockwise)
function rotateClockwise() {
    rotation.value = (rotation.value + 90) % 360;
}
</script>

<template>
    <div class="flex flex-col h-full bg-slate-100/90 select-none overflow-hidden">
        <!-- ─── Compact DaisyUI Controls Toolbar ─── -->
        <div class="flex items-center justify-between px-3 py-1.5 bg-white border-b border-slate-200/80 shrink-0 gap-2 z-10">
            <!-- Left: Page Navigation & Mode -->
            <div class="flex items-center gap-1.5">
                <div class="join">
                    <button
                        class="btn btn-xs join-item btn-ghost text-slate-600 hover:bg-slate-100"
                        :disabled="viewMode === 'all' || currentPage <= 1"
                        title="Previous page"
                        @click="prevPage"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" />
                    </button>

                    <span
                        v-if="viewMode === 'single'"
                        class="join-item px-2 py-0.5 text-[11px] font-mono font-medium text-slate-700 bg-slate-50 border-x border-slate-200 flex items-center tabular-nums"
                    >
                        {{ currentPage }} / {{ pageCount }}
                    </span>

                    <span
                        v-else
                        class="join-item px-2 py-0.5 text-[11px] font-medium text-slate-700 bg-slate-50 border-x border-slate-200 flex items-center tabular-nums"
                    >
                        All ({{ pageCount }} {{ pageCount === 1 ? 'page' : 'pages' }})
                    </span>

                    <button
                        class="btn btn-xs join-item btn-ghost text-slate-600 hover:bg-slate-100"
                        :disabled="viewMode === 'all' || currentPage >= pageCount"
                        title="Next page"
                        @click="nextPage"
                    >
                        <ChevronRight class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Continuous vs Single Page toggle -->
                <button
                    class="btn btn-xs btn-ghost gap-1 text-[11px] font-medium text-slate-600 hover:bg-slate-100"
                    :title="viewMode === 'all' ? 'Switch to single page view' : 'Switch to continuous scroll'"
                    @click="toggleViewMode"
                >
                    <Layers v-if="viewMode === 'all'" class="w-3.5 h-3.5 text-bu-blue-700" />
                    <Columns v-else class="w-3.5 h-3.5 text-slate-500" />
                    <span class="hidden sm:inline">{{ viewMode === 'all' ? 'Continuous' : 'Paged' }}</span>
                </button>
            </div>

            <!-- Right: Zoom & Rotate Controls -->
            <div class="flex items-center gap-1.5">
                <div class="join">
                    <button
                        class="btn btn-xs join-item btn-ghost text-slate-600 hover:bg-slate-100"
                        :disabled="zoomScale <= 0.5"
                        title="Zoom out"
                        @click="zoomOut"
                    >
                        <ZoomOut class="w-3.5 h-3.5" />
                    </button>

                    <button
                        class="join-item px-2 py-0.5 text-[11px] font-mono font-medium text-slate-700 bg-slate-50 border-x border-slate-200 flex items-center tabular-nums hover:bg-slate-100"
                        title="Reset to 100%"
                        @click="resetZoom"
                    >
                        {{ Math.round(zoomScale * 100) }}%
                    </button>

                    <button
                        class="btn btn-xs join-item btn-ghost text-slate-600 hover:bg-slate-100"
                        :disabled="zoomScale >= 2.5"
                        title="Zoom in"
                        @click="zoomIn"
                    >
                        <ZoomIn class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Rotation -->
                <button
                    class="btn btn-xs btn-ghost btn-square text-slate-600 hover:bg-slate-100"
                    title="Rotate 90° clockwise"
                    @click="rotateClockwise"
                >
                    <RotateCw class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- ─── PDF Canvas Scroll Container ─── -->
        <div
            ref="containerRef"
            class="flex-1 overflow-auto p-4 flex flex-col items-center relative select-text"
        >
            <!-- Loading Indicator -->
            <div
                v-if="isLoading"
                class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-100/80 backdrop-blur-xs gap-2"
            >
                <span class="loading loading-spinner loading-md text-bu-blue-700"></span>
                <span class="text-xs font-medium text-slate-600">Rendering document…</span>
            </div>

            <!-- Error Banner -->
            <div
                v-if="renderError"
                class="alert alert-error max-w-md my-8 shadow-sm text-xs"
            >
                <AlertCircle class="w-4 h-4 shrink-0" />
                <div>
                    <span class="font-bold">Failed to render PDF:</span> {{ renderError }}
                </div>
            </div>

            <!-- vue-pdf-embed instance -->
            <div
                v-show="!renderError"
                class="flex flex-col items-center max-w-full transition-all duration-150"
            >
                <VuePdfEmbed
                    ref="pdfEmbedRef"
                    :source="source"
                    :page="viewMode === 'single' ? currentPage : undefined"
                    :width="renderedWidth"
                    :rotation="rotation"
                    text-layer
                    annotation-layer
                    class="overflow-visible [&_.vue-pdf-embed__page]:mb-3 [&_.vue-pdf-embed__page]:shadow-md [&_.vue-pdf-embed__page]:rounded-xs [&_.vue-pdf-embed__page]:bg-white"
                    @loaded="onLoaded"
                    @loading-failed="onLoadingFailed"
                    @rendered="onRendered"
                    @rendering-failed="onRenderingFailed"
                />
            </div>
        </div>
    </div>
</template>
