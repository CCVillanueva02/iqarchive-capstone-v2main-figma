<?php

/**
 * ============================================================================
 * IQArchive v2 — InstrumentCriterion Model
 * ============================================================================
 * File: app/Models/InstrumentCriterion.php
 * Responsibility: Individual evaluation benchmarks against which evidence is mapped.
 * Architecture: Model Layer (3NF Schema)
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentCriterion extends Model
{
    use HasFactory;

    protected $table = 'instrument_criteria';

    public const UPDATED_AT = null;

    protected $fillable = [
        'instrument_parameter_id',
        'benchmark_code',
        'title',
        'suggested_evidence',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'suggested_evidence' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(InstrumentParameter::class, 'instrument_parameter_id');
    }

    public function complianceRequirements(): HasMany
    {
        return $this->hasMany(ComplianceRequirement::class, 'instrument_criteria_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'criterion_id');
    }
}
