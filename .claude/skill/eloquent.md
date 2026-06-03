# Eloquent Models — Conventions

## PhiModel Base Class

Tất cả models liên quan đến PHI phải extend `PhiModel`:

```php
abstract class PhiModel extends Model
{
    // KHÔNG dùng $fillable = ['*'] — whitelist explicit
    use SoftDeletes;

    protected static function booted(): void
    {
        // Tự động fire PhiAccessed event khi retrieve
        static::retrieved(function (self $model) {
            event(new PhiAccessed($model));
        });

        // Không cho hard delete
        static::deleting(function (self $model) {
            if (! $model->isForceDeleting()) {
                return;
            }
            throw new \Exception('Hard delete của PHI records không được phép.');
        });
    }
}
```

## Patient Model

```php
class Patient extends PhiModel
{
    protected $fillable = ['mrn', 'status'];

    public function currentDemographics(): HasOne
    {
        return $this->hasOne(PatientDemographic::class)->where('is_current', true);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class)->where('clinical_status', 'active');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class)->where('clinical_status', 'active');
    }

    public function latestVitals(): HasOne
    {
        return $this->hasOne(VitalSign::class)->latestOfMany('recorded_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
```

## Encryption Casts

Sensitive fields phải dùng `EncryptedCast`:

```php
protected $casts = [
    'phone' => EncryptedCast::class,
    'email' => EncryptedCast::class,
];
```

Xem danh sách fields cần encrypt tại [memory/config.md](../memory/config.md).

## Authorization Policy

Dùng Policy, không check role trong controller:

```php
class PatientPolicy
{
    public function view(User $user, Patient $patient): bool
    {
        // Bác sĩ/điều dưỡng: chỉ xem patient trong department của mình
        // Admin: xem tất cả
        // Billing: chỉ xem demographics + billing info
        // KHÔNG xem nếu không có encounter active
    }

    public function viewFull(User $user, Patient $patient): bool
    {
        // Chỉ treating provider của encounter hiện tại
        // Hoặc "break the glass" với lý do ghi lại
    }
}
```
