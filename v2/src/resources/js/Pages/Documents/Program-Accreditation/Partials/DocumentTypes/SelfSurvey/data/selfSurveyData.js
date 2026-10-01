/**
 * ============================================================================
 * IQArchive v2 — Program Accreditation Self-Survey Instrument Data
 * ============================================================================
 * Source: Official AACCUP Undergraduate Master Survey Instrument (Areas I to X)
 * Location: v2/IQA-docs/Accreditation/Program Accreditation-Instrument_Areas I to X-Master Survey Instrument/
 * Responsibility: Master 10-Area survey criteria, parameter structures, and rubric definitions.
 * ============================================================================
 */

import programSurveyAreas from './programSurveyData.json';

export const RATING_SCALE_RUBRIC = [
    { code: 'NA', label: '–', description: 'Not Applicable' },
    { code: '0', label: '–', description: 'Missing' },
    { code: '1', label: 'Poor', description: 'Criterion is met minimally in some respects, but much improvement is needed to overcome weaknesses (75% lesser than the standards)' },
    { code: '2', label: 'Fair', description: 'Criterion is met in most respects, but some improvement is needed to overcome weaknesses (50% lesser than the standards)' },
    { code: '3', label: 'Satisfactory', description: 'Criterion is met in all respects (100% compliance with the standards)' },
    { code: '4', label: 'Very Satisfactory', description: 'Criterion is fully met in all respects, at a level that demonstrates good practice (50% greater than the standards)' },
    { code: '5', label: 'Excellent', description: 'Criterion is fully met with substantial number of good practices, at a level that provides a model for others (75% greater than the standards)' },
];

export const PROGRAM_SURVEY_AREAS = programSurveyAreas;
export const INSTITUTIONAL_SURVEY_AREAS = programSurveyAreas;
