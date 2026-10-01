/**
 * Shared formatting utilities for IQArchive frontend views.
 * Author: Carl Justine Tuazon
 */

/**
 * Format date to standard institutional format (e.g., "Sep 22, 2026").
 */
export function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
}

/**
 * Format file size in bytes to human-readable string (e.g. 1 KB, 2.4 MB).
 * Avoids awkward fractions on small files (e.g. 1018 bytes -> 1 KB, not 1018.0 B).
 */
export function formatBytes(bytes, decimals = 1) {
    if (!bytes || bytes <= 0) return '0 KB';
    if (bytes < 1024) return '1 KB';

    const k = 1024;
    const sizes = ['KB', 'MB', 'GB', 'TB'];
    let i = Math.floor(Math.log(bytes) / Math.log(k)) - 1;
    i = Math.max(0, Math.min(i, sizes.length - 1));

    let val = bytes / Math.pow(k, i + 1);
    if (parseFloat(val.toFixed(decimals)) >= 1024 && i < sizes.length - 1) {
        val /= k;
        i += 1;
    }

    return `${parseFloat(val.toFixed(decimals))} ${sizes[i]}`;
}

/**
 * Return semantic DaisyUI badge class based on AACCUP numerical score (1.00 - 5.00).
 */
export function getScoreBadgeVariant(score) {
    const num = parseFloat(score);
    if (isNaN(num)) return 'badge-ghost';
    if (num >= 4.5) return 'badge-success';
    if (num >= 3.5) return 'badge-info';
    if (num >= 2.5) return 'badge-warning';
    return 'badge-error';
}

/**
 * Detect document type category ('pdf', 'image', 'drive', 'word', 'excel', 'presentation', 'archive', 'generic').
 */
export function detectDocumentType(doc) {
    if (!doc) return 'generic';

    if (typeof doc === 'object' && doc.external_url) {
        return 'drive';
    }

    const name = typeof doc === 'string'
        ? doc
        : (doc.original_filename || doc.title || doc.name || doc.file_path || '');

    const mime = typeof doc === 'object'
        ? (doc.mime_type || doc.type || '')
        : '';

    const lowerName = name.toLowerCase();
    const lowerMime = mime.toLowerCase();

    if (lowerMime.includes('pdf') || lowerName.endsWith('.pdf')) {
        return 'pdf';
    }
    if (
        lowerMime.startsWith('image/') ||
        /\.(png|jpe?g|webp|gif|svg|bmp)$/i.test(lowerName)
    ) {
        return 'image';
    }
    if (
        lowerMime.includes('word') ||
        lowerMime.includes('officedocument.wordprocessingml') ||
        /\.(docx?|rtf)$/i.test(lowerName)
    ) {
        return 'word';
    }
    if (
        lowerMime.includes('spreadsheet') ||
        lowerMime.includes('excel') ||
        lowerMime.includes('csv') ||
        /\.(xlsx?|csv)$/i.test(lowerName)
    ) {
        return 'excel';
    }
    if (
        lowerMime.includes('presentation') ||
        lowerMime.includes('powerpoint') ||
        /\.(pptx?)$/i.test(lowerName)
    ) {
        return 'presentation';
    }
    if (
        lowerMime.includes('zip') ||
        lowerMime.includes('compressed') ||
        /\.(zip|rar|7z|tar\.gz)$/i.test(lowerName)
    ) {
        return 'archive';
    }

    return 'generic';
}

/**
 * Extract embeddable Google Drive preview URL from standard sharing/view links.
 */
export function getGoogleDrivePreviewUrl(url) {
    if (!url) return null;
    const match = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (match && match[1]) {
        return `https://drive.google.com/file/d/${match[1]}/preview`;
    }
    return null;
}

