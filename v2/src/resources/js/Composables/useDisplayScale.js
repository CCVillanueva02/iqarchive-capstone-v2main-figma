/**
 * ================================================================================
 * IQArchive v2 — Display Scale Composable
 * ================================================================================
 * File: resources/js/Composables/useDisplayScale.js
 * Role: Manages interactive interface scale preferences (100%, 110%, 120%)
 *       with persistence in localStorage and immediate root font-size application.
 * ================================================================================
 */

import { ref } from 'vue';

const STORAGE_KEY = 'iq_display_scale';
const DEFAULT_SCALE = '110%';

export const AVAILABLE_SCALES = [
    { label: 'Standard (100%)', value: '100%', description: 'Default browser scale' },
    { label: 'Comfortable (110%)', value: '110%', description: 'Default median desktop scale' },
    { label: 'Spacious (120%)', value: '120%', description: 'Expansive desktop scale' },
];

const currentScale = ref(DEFAULT_SCALE);

export function useDisplayScale() {
    function applyScale(scale) {
        if (!AVAILABLE_SCALES.some(s => s.value === scale)) {
            scale = DEFAULT_SCALE;
        }

        currentScale.value = scale;

        try {
            localStorage.setItem(STORAGE_KEY, scale);
        } catch (e) {
            // Storage access restricted or disabled
        }

        if (typeof document !== 'undefined') {
            document.documentElement.style.fontSize = scale;
        }
    }

    function initScale() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved && AVAILABLE_SCALES.some(s => s.value === saved)) {
                applyScale(saved);
                return;
            }
        } catch (e) {}

        applyScale(DEFAULT_SCALE);
    }

    return {
        currentScale,
        availableScales: AVAILABLE_SCALES,
        setScale: applyScale,
        initScale,
    };
}
