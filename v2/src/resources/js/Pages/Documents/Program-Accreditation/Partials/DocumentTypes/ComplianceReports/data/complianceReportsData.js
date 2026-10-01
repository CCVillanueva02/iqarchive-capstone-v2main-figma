/**
 * ============================================================================
 * IQArchive v2 — Compliance Reports Dataset & Definitions
 * ============================================================================
 * Source: Official AACCUP Master Survey Instrument (Areas I to X)
 * & Program Summary of Findings and Recommendations (SFR)
 * Responsibility: Recommendations, actions taken, evidence links, and persistence.
 * ============================================================================
 */

import complianceData from './complianceReportsData.json';

export const COMPLIANCE_AREAS = complianceData;

export const COMPLIANCE_STATUSES = [
    { key: 'all', label: 'All Recommendations', badgeClass: 'badge-neutral' },
    { key: 'fully_complied', label: 'Fully Complied', badgeClass: 'badge-success' },
    { key: 'partial', label: 'Partial', badgeClass: 'badge-warning' },
    { key: 'not_started', label: 'Not Started', badgeClass: 'badge-error' },
];

/**
 * Compute compliance statistics for a given area or list of recommendations.
 */
export function getAreaComplianceStats(recommendations = []) {
    const total = recommendations.length;
    const fullyComplied = recommendations.filter((r) => r.status === 'fully_complied').length;
    const partial = recommendations.filter((r) => r.status === 'partial').length;
    const notStarted = recommendations.filter((r) => r.status === 'not_started').length;
    const percentage = total ? Math.round((fullyComplied / total) * 100) : 0;

    return {
        total,
        fullyComplied,
        partial,
        notStarted,
        percentage,
    };
}

/**
 * LocalStorage state persistence helpers for interactive compliance edits.
 */
export function loadSavedComplianceAreas(programId) {
    if (typeof window === 'undefined') return JSON.parse(JSON.stringify(COMPLIANCE_AREAS));
    try {
        const key = `iqarchive_compliance_areas_${programId || 'default'}`;
        const saved = localStorage.getItem(key);
        if (saved) {
            return JSON.parse(saved);
        }
    } catch (e) {
        console.warn('Failed to load compliance data from localStorage:', e);
    }
    return JSON.parse(JSON.stringify(COMPLIANCE_AREAS));
}

export function saveComplianceAreas(programId, areas) {
    if (typeof window === 'undefined') return;
    try {
        const key = `iqarchive_compliance_areas_${programId || 'default'}`;
        localStorage.setItem(key, JSON.stringify(areas));
    } catch (e) {
        console.warn('Failed to save compliance data to localStorage:', e);
    }
}
