# Security — Implementation Rules

## Authentication & Authorization

```
- MFA bắt buộc cho mọi user có PHI access
- Password: min 12 chars, complexity, expire 90 ngày
- Account lock sau 5 failed attempts, unlock sau 30 phút
- Session idle timeout: 15 phút
- Dùng Policy-based authorization, không check role trong controller
```

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

## Encryption

Fields nhạy cảm encrypt bằng Laravel AES-256-CBC (EncryptedCast):

```php
// patient_demographics: phone, email, address_line
// patient_identifiers:  value (CCCD, BHYT, passport)
// users:                mfa_secret

protected $casts = [
    'phone' => EncryptedCast::class,
    'email' => EncryptedCast::class,
];
```

## Rate Limiting

```
PHI access:  60 req/phút per user
Auth:        5 req/phút per IP (brute force), 100 req/ngày per user
```

## PHI Audit Trail

- Mọi PHI access đều bị log qua `AuditPhiAccess` middleware
- Audit log WORM: không update, không delete
- Hash chain SHA-256 để detect tampering
- Retention 6 năm (HIPAA minimum), archive sau 2 năm
- PHI scrubbed khỏi application logs bởi `ScrubPhiFromLogs` middleware

## Authorization Response

Khi không có quyền: trả về **403**, không phải 404 — không reveal resource có tồn tại hay không.
