<!--
================================================================================
IQArchive v2 — Area Carousel Component (Old Design Fidelity)
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/AreaCarousel.vue
Role: Renders horizontal area tabs with code, title, and progress bar with native scrollbar.
UI Standard: DaisyUI / Original Design (< 100 lines).
================================================================================
-->

<script setup>
import { ref } from 'vue';

const props = defineProps({
    areas: { type: Array, default: () => [] },
    selectedIndex: { type: Number, default: 0 },
    documents: { type: Array, default: () => [] },
});

const emit = defineEmits(['select']);

const scrollContainer = ref(null);

const romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

function getAreaRoman(areaNumber) {
    return romanNumerals[areaNumber - 1] || areaNumber;
}

function getAreaProgress(area) {
    const allCriteria = (area.parameters || []).flatMap((p) => p.criteria || []);
    if (allCriteria.length === 0) return 0;
    const completed = allCriteria.filter((c) => props.documents.some((d) => d.criterion_id === c.id)).length;
    return Math.round((completed / allCriteria.length) * 100);
}
</script>

<template>
    <div class="flex flex-col w-full select-none">
        <!-- Area Cards Carousel with native scrollbar -->
        <div
            ref="scrollContainer"
            class="area-carousel-scroll flex overflow-x-auto gap-3 pb-2.5 w-full scroll-smooth"
        >
            <button
                v-for="(area, idx) in areas"
                :key="area.id"
                type="button"
                @click="emit('select', idx)"
                class="flex-1 shrink-0 min-w-56 max-w-64 bg-white rounded-xl p-3.5 sm:p-4 text-left transition-all duration-200 cursor-pointer flex flex-col justify-between min-h-28 h-28 hover:border-slate-300"
                :class="selectedIndex === idx
                    ? 'border-2 border-slate-900 shadow-xs bg-slate-50/30'
                    : 'border border-slate-200/80 hover:border-slate-300 shadow-2xs'"
            >
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                        AREA {{ getAreaRoman(area.area_number) }}
                    </span>
                    <span class="text-xs sm:text-[13px] font-bold text-slate-900 mt-1 leading-snug line-clamp-2 block">
                        {{ area.name }}
                    </span>
                </div>

                <div class="w-full mt-2.5 shrink-0">
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                            :style="'width: ' + getAreaProgress(area) + '%'"
                        ></div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped>
.area-carousel-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}
.area-carousel-scroll::-webkit-scrollbar {
    height: 6px;
}
.area-carousel-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 9999px;
}
.area-carousel-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
.area-carousel-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
