/**
 * ============================================================================
 * IQArchive v2 — Institutional Accreditation Self-Survey Instrument Data
 * ============================================================================
 * File: resources/js/Pages/Documents/Institutional-Accreditation/Partials/DocumentTypes/SelfSurvey/data/institutionalSurveyData.js
 * Source: Bicol University Institutional Quality Assurance Self-Survey Matrix (Areas I to IX)
 * Responsibility: Master 9-Area survey criteria, parameter structures, and rubric definitions.
 * ============================================================================
 */

import rawInstitutionalSurveyAreas from './institutionalSurveyData.json';

export const RATING_SCALE_RUBRIC = [
    { code: 'NA', label: '–', description: 'Not Applicable' },
    { code: '0', label: '–', description: 'Missing' },
    { code: '1', label: 'Poor', description: 'Criterion is met minimally in some respects, but much improvement is needed to overcome weaknesses (75% lesser than the standards)' },
    { code: '2', label: 'Fair', description: 'Criterion is met in most respects, but some improvement is needed to overcome weaknesses (50% lesser than the standards)' },
    { code: '3', label: 'Satisfactory', description: 'Criterion is met in all respects (100% compliance with the standards)' },
    { code: '4', label: 'Very Satisfactory', description: 'Criterion is fully met in all respects, at a level that demonstrates good practice (50% greater than the standards)' },
    { code: '5', label: 'Excellent', description: 'Criterion is fully met with substantial number of good practices, at a level that provides a model for others (75% greater than the standards)' },
];

export const INSTITUTIONAL_SURVEY_AREAS = rawInstitutionalSurveyAreas.map((area) => {
    return {
        id: `ia_ss_area_${area.roman.toLowerCase()}`,
        areaNumber: area.areaNumber,
        code: `AREA ${area.roman}`,
        title: area.name,
        fullName: area.fullName,
        parameters: (area.parameters || []).map((param) => {
            const criteria = param.criteria || [];
            return {
                id: `ia_ss_a${area.areaNumber}_p${param.letter.toLowerCase()}`,
                code: param.letter,
                letter: param.letter,
                title: param.name,
                sections: {
                    system: criteria.filter((c) => c.code.startsWith('S')).map((c) => ({
                        id: `ia_ss_a${area.areaNumber}_${param.letter}_${c.code}`,
                        code: c.code,
                        statement: c.name,
                        defaultRating: c.rating,
                    })),
                    implementation: criteria.filter((c) => c.code.startsWith('I')).map((c) => ({
                        id: `ia_ss_a${area.areaNumber}_${param.letter}_${c.code}`,
                        code: c.code,
                        statement: c.name,
                        defaultRating: c.rating,
                    })),
                    outcome: criteria.filter((c) => c.code.startsWith('O')).map((c) => ({
                        id: `ia_ss_a${area.areaNumber}_${param.letter}_${c.code}`,
                        code: c.code,
                        statement: c.name,
                        defaultRating: c.rating,
                    })),
                },
            };
        }),
    };
});
