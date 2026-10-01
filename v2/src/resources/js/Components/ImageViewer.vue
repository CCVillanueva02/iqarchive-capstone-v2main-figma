<!--
================================================================================
IQArchive v2 — Native Image Preview Component
================================================================================
File: resources/js/Components/ImageViewer.vue
Role: Interactive image viewer supporting zoom, pan, rotate, and fit-to-view.
UI Standard: DaisyUI btn, loading (< 140 lines).
================================================================================
-->

<script setup>
import { ref } from 'vue';
import { ZoomIn, ZoomOut, RotateCw, Maximize2 } from 'lucide-vue-next';

const props = defineProps({
    source: { type: String, required: true },
    fileName: { type: String, default: 'Image preview' },
});

const zoomScale = ref(1);
const rotation = ref(0);
const isLoading = ref(true);
const hasError = ref(false);

function zoomIn() {
    if (zoomScale.value < 3) zoomScale.value = Math.min(3, +(zoomScale.value + 0.25).toFixed(2));
}

function zoomOut() {
    if (zoomScale.value > 0.5) zoomScale.value = Math.max(0.5, +(zoomScale.value - 0.25).toFixed(2));
}

function resetView() {
    zoomScale.value = 1;
    rotation.value = 0;
}

function rotate() {
    rotation.value = (rotation.value + 90) % 360;
}
</script>

<template>
    <div class="flex flex-col h-full bg-slate-900/5 select-none">
        <!-- Image Control Toolbar -->
        <div class="h-10 px-4 border-b border-slate-200 bg-white flex items-center justify-between gap-2 shrink-0">
            <span class="text-xs font-semibold text-slate-600 truncate max-w-xs" :title="fileName">
                {{ fileName }}
            </span>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    @click="zoomOut"
                    :disabled="zoomScale <= 0.5"
                    class="btn btn-ghost btn-xs btn-circle text-slate-600"
                    title="Zoom Out"
                >
                    <ZoomOut class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    @click="resetView"
                    class="btn btn-ghost btn-xs font-mono text-[11px] text-slate-700 min-w-12"
                    title="Reset Zoom"
                >
                    {{ Math.round(zoomScale * 100) }}%
                </button>

                <button
                    type="button"
                    @click="zoomIn"
                    :disabled="zoomScale >= 3"
                    class="btn btn-ghost btn-xs btn-circle text-slate-600"
                    title="Zoom In"
                >
                    <ZoomIn class="w-3.5 h-3.5" />
                </button>

                <div class="divider divider-horizontal my-2 mx-0.5"></div>

                <button
                    type="button"
                    @click="rotate"
                    class="btn btn-ghost btn-xs btn-circle text-slate-600"
                    title="Rotate 90°"
                >
                    <RotateCw class="w-3.5 h-3.5" />
                </button>

                <button
                    type="button"
                    @click="resetView"
                    class="btn btn-ghost btn-xs btn-circle text-slate-600"
                    title="Fit to Screen"
                >
                    <Maximize2 class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- Image Canvas Container -->
        <div class="flex-1 min-h-0 overflow-auto flex items-center justify-center p-6 relative">
            <!-- Loading Indicator -->
            <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-slate-50/80 z-10">
                <span class="loading loading-spinner loading-md text-bu-blue-700"></span>
            </div>

            <!-- Error Fallback -->
            <div v-if="hasError" class="text-center p-6">
                <p class="text-xs font-semibold text-error">Unable to render image</p>
                <p class="text-[11px] text-slate-400 mt-1">The image file could not be loaded or is corrupted.</p>
            </div>

            <!-- Image Asset -->
            <img
                v-show="!hasError"
                :src="source"
                :alt="fileName"
                @load="isLoading = false"
                @error="hasError = true; isLoading = false"
                class="max-w-full max-h-full object-contain rounded-lg shadow-sm border border-slate-200/60 bg-white transition-transform duration-150 origin-center"
                :style="{
                    transform: `scale(${zoomScale}) rotate(${rotation}deg)`,
                }"
            />
        </div>
    </div>
</template>
