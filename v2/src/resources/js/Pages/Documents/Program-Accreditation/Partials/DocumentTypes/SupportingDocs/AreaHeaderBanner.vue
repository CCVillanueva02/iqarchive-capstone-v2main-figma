<!--
================================================================================
IQArchive v2 — Area Header Banner Component
================================================================================
File: resources/js/Pages/Documents/Program-Accreditation/Partials/DocumentTypes/SupportingDocs/AreaHeaderBanner.vue
Role: Displays active Area title, description, and completion radial progress.
UI Standard: DaisyUI card, badge, radial-progress (< 60 lines).
================================================================================
-->

<script setup>
defineProps({
    area: { type: Object, default: null },
    stats: { type: Object, required: true },
});
</script>

<template>
    <div class="card card-border bg-base-100 border-slate-200 p-4 rounded-2xl shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary badge-soft text-xs font-bold font-mono">
                        AREA {{ area?.area_number }}
                    </span>
                    <h2 class="text-base font-bold text-slate-900">
                        {{ area?.name }}
                    </h2>
                </div>
                <p class="text-xs text-slate-500">
                    {{ area?.description || 'Institutional Accreditation Evidence Matrix' }}
                </p>
            </div>

            <!-- Progress Pill -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-800 tabular-nums">
                        {{ stats.completed }} / {{ stats.total }} Benchmarks
                    </div>
                    <div class="text-[11px] text-slate-600 font-medium">
                        {{ stats.pct }}% Evidence Attached
                    </div>
                </div>
                <div
                    class="radial-progress bg-slate-100 text-bu-orange-500 text-[11px] font-bold"
                    :style="{ '--value': stats.pct, '--size': '2.75rem', '--thickness': '3px' }"
                >
                    {{ stats.pct }}%
                </div>
            </div>
        </div>
    </div>
</template>
