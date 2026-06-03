# Terminology Lookup Tables

Không hardcode codes — quản lý qua database.

## Database Tables

```sql
CREATE TABLE terminology_value_sets (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vs_name     VARCHAR(100) NOT NULL,          -- 'VitalSignLoincCodes', 'DiabetesIcd10'
    description TEXT,
    version     VARCHAR(20) NOT NULL,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE terminology_concepts (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    value_set_id BIGINT UNSIGNED NOT NULL,
    code         VARCHAR(50) NOT NULL,
    code_system  VARCHAR(255) NOT NULL,         -- 'http://loinc.org', 'http://snomed.info/sct'
    display      VARCHAR(500) NOT NULL,
    is_active    BOOLEAN DEFAULT TRUE,
    valid_from   DATE,
    valid_to     DATE NULL,
    properties   JSON NULL,
    UNIQUE KEY (value_set_id, code, code_system),
    FOREIGN KEY (value_set_id) REFERENCES terminology_value_sets(id)
) ENGINE=InnoDB;

CREATE TABLE terminology_mappings (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_system  VARCHAR(100) NOT NULL,       -- 'LIS_LOCAL', 'PHARMACY_LOCAL'
    source_code    VARCHAR(100) NOT NULL,
    source_display VARCHAR(500),
    target_system  VARCHAR(255) NOT NULL,       -- 'http://loinc.org'
    target_code    VARCHAR(50) NOT NULL,
    target_display VARCHAR(500),
    mapping_type   ENUM('exact','broad','narrow','related') DEFAULT 'exact',
    valid_from     DATE,
    valid_to       DATE NULL,
    mapped_by      BIGINT UNSIGNED NULL,
    reviewed_at    TIMESTAMP NULL,
    UNIQUE KEY (source_system, source_code, target_system)
) ENGINE=InnoDB;
```

## TerminologyService

```php
class TerminologyService
{
    public function resolve(string $vsName, string $code): ?array
    {
        return Cache::remember("terminology.{$vsName}.{$code}", now()->addHours(24), function () use ($vsName, $code) {
            return TerminologyConcept::whereHas('valueSet', fn($q) => $q->where('vs_name', $vsName)->where('is_active', true))
                ->where('code', $code)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', today()))
                ->first()?->toArray();
        });
    }

    public function mapLocalCode(string $sourceSystem, string $sourceCode): ?array
    {
        return Cache::remember("terminology.map.{$sourceSystem}.{$sourceCode}", now()->addHours(24), fn() =>
            TerminologyMapping::where('source_system', $sourceSystem)
                ->where('source_code', $sourceCode)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', today()))
                ->first()?->only(['target_system', 'target_code', 'target_display'])
        );
    }
}
```
