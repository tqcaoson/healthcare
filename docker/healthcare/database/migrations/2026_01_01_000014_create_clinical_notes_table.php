<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('encounter_id')->constrained();
            $table->unsignedBigInteger('author_id');
            $table->string('loinc_type_code', 20);
            $table->string('loinc_type_display', 255);
            $table->enum('note_type', ['progress','admission','discharge','procedure','consult','nursing','other']);
            $table->longText('content');
            $table->enum('content_type', ['text/plain','text/html'])->default('text/plain');
            $table->enum('status', ['draft','preliminary','final','amended','corrected','cancelled'])->default('draft');
            $table->unsignedBigInteger('attested_by')->nullable();
            $table->timestamp('attested_at')->nullable();
            $table->timestamp('documented_at');
            $table->timestamps();
        });

        Schema::create('clinical_note_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('clinical_notes');
            $table->unsignedInteger('version');
            $table->longText('content');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
            $table->string('change_reason', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_note_versions');
        Schema::dropIfExists('clinical_notes');
    }
};
