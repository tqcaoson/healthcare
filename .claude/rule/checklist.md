# Checklist trước khi deploy

## Security
- [ ] MFA enabled cho tất cả accounts
- [ ] TLS 1.2+ enforce (disable TLS 1.0/1.1)
- [ ] AES-256 encryption cho sensitive DB columns
- [ ] Audit log không thể bị xóa/sửa
- [ ] PHI scrubbing middleware active
- [ ] Rate limiting configured
- [ ] Session idle timeout = 15 phút
- [ ] Password complexity + expiry configured

## Integration
- [ ] HL7 outbox pattern implemented (atomic với business events)
- [ ] Idempotency check cho incoming HL7 (MSH-10)
- [ ] DLQ configured cho failed messages
- [ ] MLLP retry với exponential backoff

## Clinical Safety
- [ ] Critical value workflow: notify → acknowledge → escalate
- [ ] CDS alerts không block CPOE
- [ ] Medication barcode verification bắt buộc
- [ ] Allergy check tự động khi đặt lệnh thuốc
- [ ] 5 Rights of Medication enforced

## Operations
- [ ] DB backup daily, test restore hàng tuần
- [ ] Queue monitoring (Horizon dashboard)
- [ ] Error tracking (Sentry) configured
- [ ] Health check endpoint `/health`
- [ ] Downtime procedures documented
