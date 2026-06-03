<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->enum('category', ['food','medication','environment','biologic']);
            $table->enum('clinical_status', ['active','inactive','resolved'])->default('active');
            $table->enum('verification', ['unconfirmed','confirmed','refuted','entered-in-error'])->default('unconfirmed');
            $table->string('rxnorm_code', 20)->nullable();
            $table->string('substance_name', 255);
            $table->string('reaction_manifestation', 500)->nullable();
            $table->enum('reaction_severity', ['mild','moderate','severe'])->nullable();
            $table->date('onset_date')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('recorded_at');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();

            $table->index(['patient_id', 'clinical_status']);
        });

        Schema::create('conditions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('patient_id')->constrained();
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->string('icd10_code', 10)->nullable();
            $table->string('icd10_display', 500)->nullable();
            $table->string('snomed_code', 20)->nullable();
            $table->string('snomed_display', 500)->nullable();
            $table->string('free_text', 500)->nullable();
            $table->enum('category', ['encounter-diagnosis','problem-list-item','health-concern'])->default('encounter-diagnosis');
            $table->enum('clinical_status', ['active','recurrence','relapse','inactive','remission','resolved'])->default('active');
            $table->enum('verification', ['unconfirmed','provisional','differential','confirmed','refuted','entered-in-error'])->default('confirmed');
            $table->enum('severity', ['mild','moderate','severe'])->nullable();
            $table->date('onset_date')->nullable();
            $table->date('abatement_date')->nullable();
            $table->timestamp('recorded_at');
            $table->unsignedBigInteger('recorded_by');
            $table->softDeletes();

            $table->index(['patient_id', 'clinical_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conditions');
        Schema::dropIfExists('allergies');
    }
};
