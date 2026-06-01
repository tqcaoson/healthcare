<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->string('loinc_code', 20);
            $table->string('loinc_display', 500);
            $table->string('local_code', 50)->nullable();
            $table->string('specimen_type', 100)->nullable();
            $table->boolean('fasting_required')->default(false);
            $table->timestamp('routed_to_lis_at')->nullable();
            $table->string('lis_accession', 50)->nullable();
        });

        Schema::create('medication_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->string('rxnorm_code', 20)->nullable();
            $table->string('drug_name', 255);
            $table->decimal('dose_value', 10, 3)->nullable();
            $table->string('dose_unit', 20)->nullable();
            $table->string('route', 50)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->smallInteger('duration_days')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('substitution_allowed')->default(true);
            $table->unsignedBigInteger('verified_by_pharmacist')->nullable();
            $table->timestamp('verified_at')->nullable();
        });

        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->enum('modality', ['XR','CT','MR','US','NM','PET','DEXA','FLUORO','MAMMO']);
            $table->string('body_part', 100);
            $table->enum('laterality', ['left','right','bilateral','na'])->default('na');
            $table->boolean('contrast')->default(false);
            $table->boolean('portable')->default(false);
            $table->text('clinical_history')->nullable();
            $table->timestamp('routed_to_ris_at')->nullable();
            $table->string('ris_accession', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('medication_orders');
        Schema::dropIfExists('lab_orders');
    }
};
