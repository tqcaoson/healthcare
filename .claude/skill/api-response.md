# API Response Format

Tất cả API responses tuân theo format chuẩn qua `ApiResponse`.

## ApiResponse Class

```php
// app/Http/Responses/ApiResponse.php
class ApiResponse
{
    public static function success(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success'   => false,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $resourceClass): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $resourceClass::collection($paginator),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
```

## Usage

```php
public function index(Request $request): JsonResponse
{
    $patients = Patient::active()
        ->with('currentDemographics')
        ->paginate($request->integer('per_page', 20));

    return ApiResponse::paginated($patients, PatientResource::class);
}
```

## Error Handler

```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->renderable(function (ModelNotFoundException $e, Request $request) {
        if ($request->expectsJson()) {
            return ApiResponse::error('Không tìm thấy dữ liệu', 404);
        }
    });

    $this->renderable(function (AuthorizationException $e, Request $request) {
        if ($request->expectsJson()) {
            // KHÔNG tiết lộ resource có tồn tại hay không (ngăn enumeration)
            return ApiResponse::error('Không có quyền truy cập', 403);
        }
    });

    $this->renderable(function (ValidationException $e, Request $request) {
        if ($request->expectsJson()) {
            return ApiResponse::error('Dữ liệu không hợp lệ', 422, $e->errors());
        }
    });

    $this->renderable(function (CriticalDrugInteractionException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), 409, [
            'alert_type' => 'critical_drug_interaction',
            'details'    => $e->getAlertDetails(),
        ]);
    });

    // Log tất cả 5xx nhưng không leak stack trace trong response
    $this->reportable(function (\Throwable $e) {
        if ($e instanceof \Exception && !$e instanceof HttpException) {
            app(AuditService::class)->log('system.error', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                // Không log PHI trong stack trace
            ]);
        }
    });
}
```
