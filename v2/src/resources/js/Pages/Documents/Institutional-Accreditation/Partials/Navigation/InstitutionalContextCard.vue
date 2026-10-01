<!--
================================================================================
IQArchive v2 — Institutional Context Card (Upper-Right Header Component)
================================================================================
File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/Navigation/InstitutionalContextCard.vue
Role: Renders university-wide contextual details and assessment metrics:
      - BU seal / AACCUP accreditation identity
      - Active assessment cycle: IA Cycle 2024–2028
      - 9 Areas evaluated & 4 Document Types
UI Standard: DaisyUI card, badge (< 120 lines).
================================================================================
-->

<script setup>
import { computed } from 'vue';
import { Building2 } from 'lucide-vue-next';

const props = defineProps({
    activeCategory: { type: String, default: 'hub' },
    activeCategoryTitle: { type: String, default: '' },
    institution: { type: Object, default: () => ({ name: 'Bicol University', acronym: 'BU', areasCount: 9 }) },
});

const isHub = computed(() => props.activeCategory === 'hub');
</script>

<template>
    <div
        class="card card-border bg-base-100 border-slate-200 p-3 rounded-2xl shadow-xs flex flex-row items-center justify-between gap-3 min-h-24"
    >
        <!-- Left Side: Institution Seal & Information -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="avatar shrink-0">
                <div class="w-14 h-14 mask mask-squircle bg-slate-50 border border-slate-200/80 flex items-center justify-center">
                    <img
                        src="/bulogo.png"
                        alt="Bicol University"
                        class="w-10 h-10 object-contain mx-auto my-auto"
                        @error="$event.target.style.display = 'none'"
                    />
                    <Building2 v-if="false" class="w-6 h-6 text-bu-blue-700 m-auto" />
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <div class="text-xs font-extrabold text-slate-900 tracking-tight leading-snug truncate" :title="institution?.name || 'Bicol University'">
                    {{ institution?.name || 'Bicol University' }}
                </div>
                <div class="text-[11px] font-medium text-slate-500 mt-0.5 flex items-center gap-1.5">
                    <span>Institutional Accreditation</span>
                </div>
                <div class="mt-1">
                    <span v-if="isHub" class="badge badge-xs badge-soft badge-primary font-bold">
                        IA Cycle 2024–2028
                    </span>
                    <span v-else class="badge badge-xs badge-soft badge-neutral font-semibold truncate max-w-45">
                        {{ activeCategoryTitle }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Side: Evaluation Metrics -->
        <div class="text-right shrink-0 pl-2.5 pr-1.5 border-l border-slate-100 flex flex-col justify-center">
            <div class="text-lg font-black text-slate-900 tabular-nums leading-none text-center">
                {{ institution?.areasCount || 9 }}
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 text-center">
                Areas
            </div>
        </div>
    </div>
</template>
