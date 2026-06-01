<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->string('loinc_code', 20);
            $table->string('loinc_display', 500);
            $table->decimal('value_quantity', 15, 6)->nullable();
            $table->string('value_unit', 50)->nullable();
            $table->string('value_string', 500)->nullable();
            $table->string('value_coded', 50)->nullable();
            $table->string('value_coded_display', 255)->nullable();
            $table->decimal('reference_low', 15, 6)->nullable();
            $table->decimal('reference_high', 15, 6)->nullable();
            $table->string('reference_text', 200)->nullable();
            $table->enum('abnormal_flag', ['N','L','H','LL','HH','A','AA','null'])->default('null');
            $table->string('interpretation', 50)->nullable();
            $table->enum('status', ['registered','preliminary','final','corrected','amended','cancelled'])->default('final');
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('resulted_at');
            $table->string('performer_lab', 255)->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamp('critical_notified_at')->nullable();
            $table->unsignedBigInteger('critical_notified_to')->nullable();
            $table->boolean('critical_readback_confirmed')->default(false);
            $table->timestamps();

            $table->index(['patient_id', 'loinc_code']);
            $table->index('encounter_id');
            $table->index(['is_critical', 'acknowledged_at']);
        });

        Schema::create('lab_result_panels', function (Blueprint $table) {
            $table->id();
            $table->string('panel_name', 200);
            $table->foreignId('order_id')->constrained();
            $table->json('results');
            $table->text('report_text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_panels');
        Schema::dropIfExists('lab_results');
    }
};
