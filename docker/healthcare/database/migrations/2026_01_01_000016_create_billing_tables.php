<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->string('cpt_code', 10)->nullable();
            $table->string('cpt_display', 500)->nullable();
            $table->string('icd10_code', 10)->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['billable','billed','not-billable','aborted'])->default('billable');
            $table->date('service_date');
            $table->unsignedBigInteger('billed_by');
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number', 50)->unique();
            $table->foreignId('encounter_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->enum('claim_type', ['institutional','professional','pharmacy'])->default('professional');
            $table->enum('status', ['draft','submitted','pending','accepted','rejected','partial','paid','cancelled'])->default('draft');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('patient_portion', 12, 2)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
        Schema::dropIfExists('charge_items');
    }
};
