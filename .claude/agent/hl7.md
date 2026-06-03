# HL7 Integration

## config/hl7.php

```php
return [
    'connections' => [
        'LIS' => [
            'host'    => env('LIS_HOST'),
            'port'    => env('LIS_PORT', 6661),
            'timeout' => 10,
            'retry'   => 3,
        ],
        'RIS' => [
            'host'    => env('RIS_HOST'),
            'port'    => env('RIS_PORT', 6662),
            'timeout' => 10,
        ],
        'PHARMACY' => [
            'host'    => env('PHARMACY_HOST'),
            'port'    => env('PHARMACY_PORT', 6663),
        ],
    ],

    'message_types' => [
        'ADT_A01' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Admit
        'ADT_A03' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Discharge
        'ADT_A08' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Update demographics
        'ORM_O01' => ['destinations' => ['LIS']],                       // Lab order
        'ORM_RAD' => ['destinations' => ['RIS']],                       // Radiology order
        'OMP_O09' => ['destinations' => ['PHARMACY']],                  // Pharmacy order
    ],
];
```

## Outbox Pattern (atomic với business event)

HL7 message phải được ghi vào `hl7_outbox` **trong cùng transaction** với business event. Không gửi trực tiếp trong request — dùng Job.

```php
// Trong CpoeService::placeOrder()
DB::transaction(function () {
    $order = Order::create([...]);
    $this->createTypeSpecificOrder($order, $dto);

    // Ghi outbox TRONG TRANSACTION
    Hl7Outbox::create([
        'correlation_id' => Str::uuid(),
        'message_type'   => 'ORM_O01',
        'destination'    => 'LIS',
        'payload'        => $this->buildHl7($order),
        'status'         => 'pending',
    ]);
});
// Job chạy async → đọc outbox → gửi MLLP → mark 'sent'
```

## Idempotency cho Incoming Messages

```php
// Kiểm tra MSH-10 trước khi process
if (Hl7Inbox::where('message_control_id', $msg->message_control_id)->exists()) {
    return; // đã xử lý, bỏ qua
}
```

## Artisan Commands

```php
// hl7:process — chạy liên tục hoặc theo schedule
Hl7Inbox::where('status', 'received')
    ->orderBy('created_at')
    ->chunk(50, function ($messages) use ($parser) {
        foreach ($messages as $msg) {
            try {
                $msg->update(['status' => 'processing']);
                ProcessIncomingHl7Message::dispatch($msg);
                $msg->update(['status' => 'processed', 'processed_at' => now()]);
            } catch (\Throwable $e) {
                $msg->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            }
        }
    });

// hl7:retry-dead — retry dead letter queue
Hl7Outbox::where('status', 'dead')
    ->limit($this->option('limit'))
    ->get()
    ->each(function ($msg) {
        $msg->update(['status' => 'pending', 'attempts' => 0]);
        SendOutgoingHl7Message::dispatch($msg);
    });
```
