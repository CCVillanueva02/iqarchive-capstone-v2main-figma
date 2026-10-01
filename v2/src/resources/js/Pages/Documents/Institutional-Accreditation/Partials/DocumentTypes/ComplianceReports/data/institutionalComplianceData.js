/**
 * ============================================================================
 * IQArchive v2 — Institutional Compliance Reports Dataset & Definitions
 * ============================================================================
 * File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/ComplianceReports/data/institutionalComplianceData.js
 * Source: Official Bicol University Institutional Accreditation Compliance Reports (Areas I to IX)
 * Responsibility: Master 9-Area compliance recommendations, actions taken, and persistence.
 * ============================================================================
 */

import rawComplianceData from './institutionalComplianceData.json';

export const INSTITUTIONAL_COMPLIANCE_AREAS = Object.entries(rawComplianceData).map(([roman, area]) => {
    return {
        id: `ia_comp_area_${roman.toLowerCase()}`,
        areaNumber: area.areaNumber,
        code: `AREA ${roman}`,
        title: area.name,
        fullName: area.fullName,
        recommendations: (area.recommendations || []).map((rec) => {
            const actionsList = rec.actionsTaken
                ? rec.actionsTaken.split(';').map((a) => a.trim()).filter(Boolean)
                : [];

            const docsList = (rec.supportingDocs || []).map((doc, dIdx) => ({
                id: `ia_cdoc_${rec.id}_${dIdx + 1}`,
                title: doc.title,
                filename: doc.title + (doc.type === 'pdf' ? '.pdf' : ''),
                type: doc.type || (doc.url ? 'link' : 'pdf'),
                size: doc.size || (doc.url ? 'Cloud Link' : '1.5 MB'),
                external_url: doc.url || null,
            }));

            return {
                id: rec.id,
                number: rec.number,
                recommendation: rec.recommendation,
                actions_taken: actionsList,
                supporting_documents: docsList,
                status: 'fully_complied',
                remarks: rec.remarks || '',
            };
        }),
    };
});

export const COMPLIANCE_STATUSES = [
    { key: 'all', label: 'All Recommendations', badgeClass: 'badge-neutral' },
    { key: 'fully_complied', label: 'Fully Complied', badgeClass: 'badge-success' },
    { key: 'partial', label: 'Partial', badgeClass: 'badge-warning' },
    { key: 'not_started', label: 'Not Started', badgeClass: 'badge-error' },
];

export function getAreaComplianceStats(recommendations = []) {
    const total = recommendations.length;
    const fullyComplied = recommendations.filter((r) => r.status === 'fully_complied').length;
    const partial = recommendations.filter((r) => r.status === 'partial').length;
    const notStarted = recommendations.filter((r) => r.status === 'not_started').length;
    const percentage = total ? Math.round((fullyComplied / total) * 100) : 0;

    return { total, fullyComplied, partial, notStarted, percentage };
}

export function getComplianceStatusConfig(status, hasEntries = false) {
    if (status === 'fully_complied') {
        return {
            label: 'Completed',
            badgeClass: 'badge-success badge-soft text-emerald-700 border-emerald-200',
            numClass: 'bg-emerald-100/80 text-emerald-800 border-emerald-300',
        };
    }
    if (status === 'partial' || hasEntries) {
        return {
            label: 'Partial',
            badgeClass: 'badge-warning badge-soft text-amber-700 border-amber-200',
            numClass: 'bg-amber-100/80 text-amber-800 border-amber-300',
        };
    }
    return {
        label: 'Not started',
        badgeClass: 'badge-error badge-soft text-rose-700 border-rose-200',
        numClass: 'bg-rose-100/80 text-rose-800 border-rose-300',
    };
}

export function loadSavedComplianceAreas() {
    if (typeof window === 'undefined') return JSON.parse(JSON.stringify(INSTITUTIONAL_COMPLIANCE_AREAS));
    try {
        const key = 'iqarchive_ia_compliance_areas';
        const saved = localStorage.getItem(key);
        if (saved) {
            return JSON.parse(saved);
        }
    } catch (e) {
        console.warn('Failed to load institutional compliance data from localStorage:', e);
    }
    return JSON.parse(JSON.stringify(INSTITUTIONAL_COMPLIANCE_AREAS));
}

export function saveComplianceAreas(areas) {
    if (typeof window === 'undefined') return;
    try {
        const key = 'iqarchive_ia_compliance_areas';
        localStorage.setItem(key, JSON.stringify(areas));
    } catch (e) {
        console.warn('Failed to save institutional compliance data to localStorage:', e);
    }
}
