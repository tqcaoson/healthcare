<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained();
            $table->string('icd10_code', 10);
            $table->string('icd10_display', 500);
            $table->enum('diagnosis_type', ['admitting','primary','secondary','discharge']);
            $table->unsignedTinyInteger('rank')->default(1);
            $table->date('onset_date')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_diagnoses');
    }
};
