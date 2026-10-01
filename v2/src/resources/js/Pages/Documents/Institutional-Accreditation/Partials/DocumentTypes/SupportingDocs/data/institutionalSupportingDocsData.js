/**
 * ============================================================================
 * IQArchive v2 — Institutional Supporting Documents Dataset & Definitions
 * ============================================================================
 * File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SupportingDocs/data/institutionalSupportingDocsData.js
 * Source: Official Bicol University Institutional Accreditation (IA) Instrument
 * Responsibility: Areas I–IX, parameters, benchmarks (S/I/O/BP), suggested evidence,
 *                 and localStorage draft persistence.
 * ============================================================================
 */

import supportingDocsRaw from './institutionalSupportingDocsData.json';

// Convert object dictionary { "I": { ... }, "II": { ... } } to ordered array
export const INSTITUTIONAL_SUPPORTING_AREAS = Object.keys(supportingDocsRaw).map((key) => {
    const area = supportingDocsRaw[key];
    return {
        id: `ia_area_${key.toLowerCase()}`,
        roman: key,
        area_number: area.areaNumber,
        name: area.name,
        fullName: area.fullName,
        parameters: (area.parameters || []).map((param) => ({
            id: `ia_param_${key}_${param.letter}`,
            letter: param.letter,
            parameter_letter: param.letter,
            name: param.name,
            benchmarks: (param.benchmarks || []).map((bm) => ({
                id: `ia_bm_${key}_${param.letter}_${bm.code}`,
                code: bm.code,
                benchmark_code: bm.code,
                type: bm.type, // 'system', 'impl', 'outcome', 'best_practice'
                title: bm.title,
                suggestedEvidence: bm.suggestedEvidence || [],
            })),
        })),
    };
});

const STORAGE_KEY = 'iqarchive_institutional_supporting_docs';

/**
 * Load persisted supporting documents evidence state.
 */
export function loadSavedSupportingDocs() {
    if (typeof window === 'undefined') return {};
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) return JSON.parse(raw);
    } catch (e) {
        console.warn('Failed to load institutional supporting docs from localStorage:', e);
    }
    return {};
}

/**
 * Save evidence attachments state.
 * Structure: { [benchmarkId]: Array<EvidenceItem> }
 */
export function saveSupportingDocs(evidenceMap) {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(evidenceMap));
    } catch (e) {
        console.warn('Failed to save institutional supporting docs to localStorage:', e);
    }
}
