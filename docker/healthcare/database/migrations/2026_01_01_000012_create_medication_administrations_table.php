<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('encounter_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['in-progress','completed','not-done','on-hold','stopped','unknown'])->default('completed');
            $table->boolean('patient_verified_by_barcode')->default(false);
            $table->boolean('medication_verified_by_barcode')->default(false);
            $table->string('rxnorm_code', 20)->nullable();
            $table->string('drug_name', 255);
            $table->decimal('dose_value', 10, 3);
            $table->string('dose_unit', 20);
            $table->string('route', 50);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('administered_at');
            $table->unsignedBigInteger('administered_by');
            $table->boolean('is_prn')->default(false);
            $table->text('prn_indication')->nullable();
            $table->text('prn_effectiveness')->nullable();
            $table->string('site', 100)->nullable();
            $table->decimal('wasted_amount', 10, 3)->nullable();
            $table->unsignedBigInteger('waste_witnessed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
    }
};
