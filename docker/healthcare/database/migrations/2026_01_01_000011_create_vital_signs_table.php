<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('encounter_id')->constrained();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('recorded_at');
            $table->unsignedSmallInteger('systolic_bp')->nullable();
            $table->unsignedSmallInteger('diastolic_bp')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedTinyInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->enum('temp_route', ['oral','rectal','axillary','tympanic','temporal'])->nullable();
            $table->unsignedTinyInteger('spo2')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->unsignedTinyInteger('pain_score')->nullable();
            $table->unsignedTinyInteger('gcs_total')->nullable();
            $table->text('notes')->nullable();

            $table->index(['patient_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
