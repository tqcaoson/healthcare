# Environment Variables Bắt buộc

```ini
# App
APP_ENV=production
APP_KEY=                          # 32-char random key

# Database
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=healthsystem
DB_USERNAME=
DB_PASSWORD=

# Redis
REDIS_HOST=
REDIS_PASSWORD=

# HL7 Connections
LIS_HOST=
LIS_PORT=6661
RIS_HOST=
RIS_PORT=6662
PHARMACY_HOST=
PHARMACY_PORT=6663

# Storage (S3-compatible)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=
AWS_ENDPOINT=                     # nếu dùng MinIO

# Security
SESSION_LIFETIME=15               # idle timeout minutes (HIPAA requirement)
PASSWORD_EXPIRY_DAYS=90
MFA_REQUIRED=true

# Queues
QUEUE_CONNECTION=redis
HORIZON_ENABLED=true

# Search
MEILISEARCH_HOST=
MEILISEARCH_KEY=

# Notifications
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Monitoring
SENTRY_DSN=                       # error tracking
LOG_CHANNEL=stack
LOG_LEVEL=warning                 # production: warning, không log PHI
```
