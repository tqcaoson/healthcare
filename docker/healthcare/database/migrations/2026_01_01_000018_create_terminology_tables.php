<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminology_value_sets', function (Blueprint $table) {
            $table->id();
            $table->string('vs_name', 100);
            $table->text('description')->nullable();
            $table->string('version', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('terminology_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_set_id')->constrained('terminology_value_sets');
            $table->string('code', 50);
            $table->string('code_system', 255);
            $table->string('display', 500);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->json('properties')->nullable();
            $table->unique(['value_set_id', 'code', 'code_system'], 'term_concepts_unique');
        });

        Schema::create('terminology_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_system', 100);
            $table->string('source_code', 100);
            $table->string('source_display', 500)->nullable();
            $table->string('target_system', 255);
            $table->string('target_code', 50);
            $table->string('target_display', 500)->nullable();
            $table->enum('mapping_type', ['exact','broad','narrow','related'])->default('exact');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->unsignedBigInteger('mapped_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unique(['source_system', 'source_code', 'target_system'], 'term_mappings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminology_mappings');
        Schema::dropIfExists('terminology_concepts');
        Schema::dropIfExists('terminology_value_sets');
    }
};
