/**
 * ============================================================================
 * IQArchive v2 — Administrative Office Icons Registry
 * ============================================================================
 * File: resources/js/Pages/Documents/Common-Documents/Partials/officeIcons.js
 * Role: Centralized catalog of selectable office icons and fallback resolver.
 * ============================================================================
 */

import { markRaw } from 'vue';
import {
    Building2,
    Users,
    GraduationCap,
    BookOpen,
    Landmark,
    Wallet,
    Wrench,
    ShieldCheck,
    Briefcase,
    FileText,
    Award,
    Globe,
    Laptop,
    HeartHandshake,
    Stethoscope,
    Scale,
} from 'lucide-vue-next';

/**
 * Curated list of selectable icons for BU administrative offices and units.
 */
export const OFFICE_ICON_OPTIONS = [
    { id: 'building-2', label: 'Administration / General', icon: markRaw(Building2) },
    { id: 'users', label: 'Human Resources / Personnel', icon: markRaw(Users) },
    { id: 'graduation-cap', label: 'Academic / Registrar', icon: markRaw(GraduationCap) },
    { id: 'book-open', label: 'Student Affairs / Library', icon: markRaw(BookOpen) },
    { id: 'landmark', label: 'Governance / Secretariat', icon: markRaw(Landmark) },
    { id: 'wallet', label: 'Budget & Finance', icon: markRaw(Wallet) },
    { id: 'wrench', label: 'General Services / Facilities', icon: markRaw(Wrench) },
    { id: 'shield-check', label: 'Quality Assurance / Security', icon: markRaw(ShieldCheck) },
    { id: 'briefcase', label: 'Executive / Planning', icon: markRaw(Briefcase) },
    { id: 'file-text', label: 'Records / Archives', icon: markRaw(FileText) },
    { id: 'award', label: 'Accreditation / Honors', icon: markRaw(Award) },
    { id: 'globe', label: 'International / External Affairs', icon: markRaw(Globe) },
    { id: 'laptop', label: 'ICTO / Digital Systems', icon: markRaw(Laptop) },
    { id: 'heart-handshake', label: 'Guidance / Extension', icon: markRaw(HeartHandshake) },
    { id: 'stethoscope', label: 'Health / Medical Services', icon: markRaw(Stethoscope) },
    { id: 'scale', label: 'Legal / Ethics / Grievance', icon: markRaw(Scale) },
];

export const OFFICE_ICON_MAP = Object.fromEntries(
    OFFICE_ICON_OPTIONS.map((item) => [item.id, item.icon])
);

/**
 * Fallback name-based mapping for legacy seeded offices without an explicit DB icon.
 */
const LEGACY_NAME_MATCHES = [
    { match: 'hrdo',             icon: markRaw(Users) },
    { match: 'registrar',        icon: markRaw(GraduationCap) },
    { match: 'osas',             icon: markRaw(BookOpen) },
    { match: 'vpaa',             icon: markRaw(BookOpen) },
    { match: 'bor',              icon: markRaw(Landmark) },
    { match: 'budget',           icon: markRaw(Wallet) },
    { match: 'finance',          icon: markRaw(Wallet) },
    { match: 'general services', icon: markRaw(Wrench) },
];

/**
 * Resolves the appropriate Lucide component for an office object.
 * Priority: 1. Explicit DB icon attribute -> 2. Name-based heuristic -> 3. Building2 default.
 */
export function resolveOfficeIcon(office) {
    if (office?.icon && OFFICE_ICON_MAP[office.icon]) {
        return OFFICE_ICON_MAP[office.icon];
    }
    const lower = (office?.name || '').toLowerCase();
    const found = LEGACY_NAME_MATCHES.find((entry) => lower.includes(entry.match));
    return found ? found.icon : markRaw(Building2);
}
