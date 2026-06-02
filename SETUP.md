# Hướng dẫn Setup — Healthcare Information System

## Yêu cầu hệ thống

| Công cụ | Phiên bản tối thiểu |
|---|---|
| Docker | 24+ |
| Docker Compose | v2+ (`docker compose`) |
| Git | bất kỳ |

Không cần cài PHP, Node, MySQL, Redis trên máy host — tất cả chạy trong container.

---

## Cấu trúc thư mục

```
healthcare/
├── docker-compose.yml
├── docker/
│   ├── healthcare/          ← Source code Laravel
│   │   ├── .env             ← Biến môi trường (đã có sẵn cho local)
│   │   └── ...
│   ├── nginx/
│   │   └── default.conf     ← Nginx config
│   └── php/
│       ├── Dockerfile
│       └── entrypoint.sh    ← Chạy tự động khi container khởi động
├── SETUP.md                 ← File này
└── USAGE.md                 ← Hướng dẫn URL & API
```

---

## Khởi động nhanh (lần đầu)

```bash
# 1. Clone dự án
git clone <repo-url>
cd healthcare

# 2. Build image và khởi động toàn bộ services
docker compose up --build
```

Khi container `app` khởi động, `entrypoint.sh` sẽ tự động:
- Cài Composer dependencies
- Generate `APP_KEY` (nếu chưa có)
- Chạy tất cả migrations
- Chạy seeders (tạo roles/permissions + user mẫu)
- Tạo storage symlink
- Khởi động `php-fpm`

> Lần đầu build có thể mất 2–5 phút tùy tốc độ mạng.

Sau khi thấy log `Starting php-fpm...`, mở trình duyệt:

```
http://localhost
```

---

## Các lần chạy tiếp theo

```bash
# Khởi động (không rebuild)
docker compose up

# Chạy nền (background)
docker compose up -d

# Dừng
docker compose down

# Dừng và xóa volume (reset toàn bộ database)
docker compose down -v
```

---

## Build frontend (Vite)

Frontend được build riêng qua service `node` (profile `build`):

```bash
docker compose --profile build run --rm node
```

Lệnh này sẽ chạy `npm install && npm run build` rồi thoát. Chỉ cần chạy lại khi có thay đổi JS/CSS.

Trong development, bạn có thể chạy Vite dev server trực tiếp trên máy host:

```bash
cd docker/healthcare
npm install
npm run dev
```

---

## Biến môi trường

File `.env` nằm tại `docker/healthcare/.env` — đã được cấu hình sẵn cho môi trường local:

| Biến | Giá trị mặc định | Ghi chú |
|---|---|---|
| `APP_URL` | `http://localhost` | |
| `DB_HOST` | `mysql` | Tên service trong Docker network |
| `DB_DATABASE` | `healthcare` | |
| `DB_USERNAME` | `healthcare` | |
| `DB_PASSWORD` | `secret` | |
| `REDIS_HOST` | `redis` | |
| `MEILISEARCH_HOST` | `http://meilisearch:7700` | |
| `MEILISEARCH_KEY` | `masterKey` | |
| `SESSION_LIFETIME` | `15` | Idle timeout (phút) — HIPAA |
| `MFA_REQUIRED` | `false` | Đặt `true` trên production |

Để thay đổi, sửa file `.env` rồi restart container:

```bash
docker compose restart app
```

---

## Kết nối database từ máy host

MySQL expose ra port `3306`, có thể kết nối bằng bất kỳ client nào (TablePlus, DBeaver, MySQL Workbench):

```
Host:     localhost
Port:     3306
Database: healthcare
Username: healthcare
Password: secret
```

---

## Chạy lệnh Artisan

```bash
# Mở shell vào container app
docker compose exec app sh

# Hoặc chạy lệnh trực tiếp
docker compose exec app php artisan <command>
```

Ví dụ thường dùng:

```bash
# Chạy migration mới
docker compose exec app php artisan migrate

# Rollback migration
docker compose exec app php artisan migrate:rollback

# Reset và chạy lại toàn bộ (xóa sạch data)
docker compose exec app php artisan migrate:fresh --seed

# Tạo Sanctum API token cho user
docker compose exec app php artisan tinker
# >>> \App\Models\User::first()->createToken('dev')->plainTextToken

# Xem danh sách routes
docker compose exec app php artisan route:list

# Clear cache
docker compose exec app php artisan optimize:clear
```

---

## Services và ports

| Service | Port host | Mô tả |
|---|---|---|
| Nginx (Web app) | `80` | Điểm vào chính → `http://localhost` |
| MySQL | `3306` | Kết nối DB từ máy host |
| Redis | `6379` | Cache / Queue / Session |
| Meilisearch | `7700` | Search UI → `http://localhost:7700` |

---

## Xử lý lỗi thường gặp

### Container `app` không khởi động được

Xem log để tìm nguyên nhân:

```bash
docker compose logs app
```

### Lỗi `SQLSTATE[HY000]: Can't connect to MySQL`

MySQL chưa kịp sẵn sàng. Chờ thêm 10–15 giây rồi restart `app`:

```bash
docker compose restart app
```

### Lỗi `Permission denied` trên storage

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Trang trắng / lỗi 500

```bash
# Xem Laravel log
docker compose exec app tail -f storage/logs/laravel.log

# Hoặc bật debug tạm thời trong .env
APP_DEBUG=true
docker compose restart app
```

### Reset hoàn toàn (xóa sạch DB, volumes)

```bash
docker compose down -v
docker compose up --build
```

---

## Cấu trúc services (Docker Compose)

```
nginx ──► app (php-fpm:9000)
           │
           ├── mysql:3306
           ├── redis:6379
           └── meilisearch:7700
```

`nginx` nhận request HTTP và forward sang `app` qua FastCGI. `app` phụ thuộc vào `mysql` (healthcheck) và `redis` trước khi khởi động.
