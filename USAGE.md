# Hướng dẫn sử dụng — Healthcare Information System

> Base URL mặc định: `http://localhost` (hoặc domain triển khai của bạn)

---

## Mục lục

- [Web UI (Blade)](#web-ui)
- [REST API v1](#rest-api-v1)
- [FHIR R4 API](#fhir-r4-api)
- [SMART on FHIR](#smart-on-fhir)
- [Xác thực](#xác-thực)
- [Ví dụ cURL nhanh](#ví-dụ-curl-nhanh)

---

## Web UI

Giao diện người dùng dựa trên Blade + Alpine.js, yêu cầu đăng nhập.

| Trang | URL | Mô tả |
|---|---|---|
| Trang chủ (redirect) | `GET /` | Tự động chuyển về `/dashboard` |
| Dashboard | `GET /dashboard` | Tổng quan hệ thống |
| Danh sách bệnh nhân | `GET /patients` | Tìm kiếm, lọc bệnh nhân |
| Thêm bệnh nhân | `GET /patients/create` | Form tạo bệnh nhân mới |
| Chi tiết bệnh nhân | `GET /patients/{id}` | Hồ sơ đầy đủ |
| Sửa bệnh nhân | `GET /patients/{id}/edit` | Cập nhật thông tin |
| Danh sách lượt khám | `GET /encounters` | Lịch sử khám/nhập viện |
| Chi tiết lượt khám | `GET /encounters/{id}` | Thông tin lượt khám |
| Danh sách y lệnh | `GET /orders` | Quản lý CPOE |
| Chi tiết y lệnh | `GET /orders/{id}` | Xem/hủy y lệnh |
| Kết quả xét nghiệm | `GET /results` | Danh sách kết quả lab |
| Chi tiết kết quả | `GET /results/{id}` | Xem kết quả + flag nguy kịch |
| Lịch hẹn | `GET /appointments` | Quản lý lịch hẹn |
| Chi tiết lịch hẹn | `GET /appointments/{id}` | Xem / cập nhật lịch hẹn |

> **Lưu ý:** Tất cả các trang Web UI đều yêu cầu đăng nhập (`auth` middleware). Truy cập khi chưa đăng nhập sẽ bị redirect về trang login do Fortify xử lý.

---

## REST API v1

Base path: `/api/v1`

Yêu cầu header:
```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

> Toàn bộ endpoint API đều yêu cầu **MFA đã xác thực** (`require.mfa` middleware) và mọi truy cập PHI đều được ghi vào audit log.

### Patients

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/patients` | Danh sách bệnh nhân (có phân trang) |
| `POST` | `/api/v1/patients` | Tạo bệnh nhân mới |
| `GET` | `/api/v1/patients/{id}` | Chi tiết bệnh nhân theo UUID |
| `PUT/PATCH` | `/api/v1/patients/{id}` | Cập nhật thông tin |
| `DELETE` | `/api/v1/patients/{id}` | Soft delete (không xóa PHI cứng) |

Query params cho `GET /api/v1/patients`:
- `per_page` — số bản ghi mỗi trang (mặc định: 20)
- `search` — tìm theo tên / MRN

### Encounters

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/encounters` | Danh sách lượt khám |
| `POST` | `/api/v1/encounters` | Tạo lượt khám mới |
| `GET` | `/api/v1/encounters/{id}` | Chi tiết lượt khám |
| `PUT/PATCH` | `/api/v1/encounters/{id}` | Cập nhật trạng thái |
| `DELETE` | `/api/v1/encounters/{id}` | Hủy (soft delete) |

### Orders (CPOE)

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/orders` | Danh sách y lệnh |
| `POST` | `/api/v1/orders` | Đặt y lệnh mới |
| `GET` | `/api/v1/orders/{id}` | Chi tiết y lệnh |
| `PUT/PATCH` | `/api/v1/orders/{id}` | Cập nhật y lệnh |
| `DELETE` | `/api/v1/orders/{id}` | Hủy y lệnh (soft delete) |

### Results (Kết quả xét nghiệm)

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/results` | Danh sách kết quả |
| `POST` | `/api/v1/results` | Nhập kết quả mới |
| `GET` | `/api/v1/results/{id}` | Chi tiết kết quả |
| `PUT/PATCH` | `/api/v1/results/{id}` | Cập nhật kết quả |

### Medications

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/medications` | Danh sách thuốc / y lệnh thuốc |
| `POST` | `/api/v1/medications` | Tạo y lệnh thuốc |
| `GET` | `/api/v1/medications/{id}` | Chi tiết y lệnh thuốc |
| `PUT/PATCH` | `/api/v1/medications/{id}` | Cập nhật |

### Vital Signs

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/vital-signs` | Danh sách dấu hiệu sinh tồn |
| `POST` | `/api/v1/vital-signs` | Ghi nhận dấu hiệu sinh tồn |
| `GET` | `/api/v1/vital-signs/{id}` | Chi tiết |
| `PUT/PATCH` | `/api/v1/vital-signs/{id}` | Cập nhật |

### Allergies

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/allergies` | Danh sách dị ứng |
| `POST` | `/api/v1/allergies` | Thêm dị ứng |
| `GET` | `/api/v1/allergies/{id}` | Chi tiết |
| `PUT/PATCH` | `/api/v1/allergies/{id}` | Cập nhật |
| `DELETE` | `/api/v1/allergies/{id}` | Xóa mềm |

### Appointments

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/appointments` | Danh sách lịch hẹn |
| `POST` | `/api/v1/appointments` | Tạo lịch hẹn |
| `GET` | `/api/v1/appointments/{id}` | Chi tiết lịch hẹn |
| `PUT/PATCH` | `/api/v1/appointments/{id}` | Cập nhật / hủy |

### Reports

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/api/v1/reports/quality` | Báo cáo chất lượng (readmission rate, ALOS, v.v.) |
| `GET` | `/api/v1/dashboard` | Dữ liệu tổng quan cho dashboard |

---

## FHIR R4 API

Base path: `/fhir/R4`

Yêu cầu:
```
Authorization: Bearer {sanctum_token}
Accept: application/fhir+json
```

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/fhir/R4/Patient` | Tìm kiếm bệnh nhân theo FHIR search params |
| `GET` | `/fhir/R4/Patient/{id}` | Đọc FHIR Patient resource theo UUID |

Query params cho `/fhir/R4/Patient`:
- `name` — tìm theo tên (family / given)
- `birthdate` — lọc theo ngày sinh (`YYYY-MM-DD`)
- `identifier` — tìm theo MRN / BHYT / CCCD

**Ví dụ response:**
```json
{
  "resourceType": "Patient",
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "identifier": [{ "system": "MRN", "value": "MRN-000001" }],
  "name": [{ "family": "Nguyễn", "given": ["Văn A"] }],
  "gender": "male",
  "birthDate": "1980-05-15"
}
```

---

## SMART on FHIR

Base path: `/smart` *(xem `routes/smart.php`)*

Dùng để tích hợp ứng dụng bên thứ ba theo chuẩn SMART on FHIR.

| Method | URL | Mô tả |
|---|---|---|
| `GET` | `/smart/authorize` | Authorization endpoint — redirect bệnh nhân/user tới đây |
| `POST` | `/smart/token` | Token endpoint — đổi authorization code lấy access token |

**Flow cơ bản:**
1. App bên thứ ba redirect user tới `GET /smart/authorize?response_type=code&client_id=...&redirect_uri=...&scope=...`
2. User đồng ý → server redirect về `redirect_uri?code=AUTH_CODE`
3. App POST lên `/smart/token` với `code=AUTH_CODE` để nhận `access_token`
4. Dùng `access_token` gọi FHIR API như bình thường

---

## Xác thực

### Đăng nhập (Web)

Fortify xử lý tự động tại `/login`. Sau khi đăng nhập thành công, MFA TOTP sẽ được yêu cầu nếu `mfa_enabled = true`.

### Lấy Sanctum Token (API)

```
POST /api/v1/login
Content-Type: application/json

{
  "email": "user@hospital.vn",
  "password": "your-password",
  "device_name": "postman"
}
```

Response:
```json
{ "token": "1|abc123..." }
```

Sau đó đính kèm token vào mọi request:
```
Authorization: Bearer 1|abc123...
```

### Logout

```
POST /api/v1/logout
Authorization: Bearer {token}
```

---

## Ví dụ cURL nhanh

**Lấy danh sách bệnh nhân:**
```bash
curl -X GET "http://localhost/api/v1/patients?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Tạo bệnh nhân mới:**
```bash
curl -X POST "http://localhost/api/v1/patients" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "mrn": "MRN-000099",
    "status": "active",
    "demographics": {
      "given_name": "Văn B",
      "family_name": "Trần",
      "birth_date": "1990-03-20",
      "gender": "male"
    }
  }'
```

**Tra cứu FHIR Patient:**
```bash
curl -X GET "http://localhost/fhir/R4/Patient?name=Nguyen&birthdate=1980-05-15" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/fhir+json"
```

---

## Health Check

Kiểm tra trạng thái hệ thống (không cần xác thực):

| URL | Mô tả |
|---|---|
| `GET /up` | Laravel built-in health check — trả về `200 OK` nếu app chạy bình thường |
