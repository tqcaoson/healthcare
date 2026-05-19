<?php

namespace App\Domain\Patient\Models;

use App\Domain\Common\Models\PhiModel;
use App\Domain\Clinical\Models\Allergy;
use App\Domain\Clinical\Models\Condition;
use App\Domain\Clinical\Models\VitalSign;
use App\Domain\Encounter\Models\Encounter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Patient extends PhiModel
{
    use HasFactory;
    use HasUuids;
    use Searchable;

    protected $table = 'patients';

    protected $fillable = [
        'uuid',
        'mrn',
        'status',
        'deceased_at',
    ];

    protected $casts = [
        'deceased_at' => 'datetime',
    ];

    public function currentDemographics(): HasOne
    {
        return $this->hasOne(PatientDemographic::class)
            ->where('is_current', true);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class)
            ->where('clinical_status', 'active');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class)
            ->where('clinical_status', 'active');
    }

    public function latestVitals(): HasOne
    {
        return $this->hasOne(VitalSign::class)
            ->latestOfMany('recorded_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'mrn' => $this->mrn,
            'status' => $this->status,
            'given_name' => $this->currentDemographics?->given_name,
            'family_name' => $this->currentDemographics?->family_name,
            'birth_date' => $this->currentDemographics?->birth_date,
        ];
    }
}