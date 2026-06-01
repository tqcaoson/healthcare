<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old incomplete version if it exists (superseded by this migration)
        Schema::dropIfExists('patient_demographics');

        Schema::create('patient_demographics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->unsignedInteger('version')->default(1);
            $table->string('given_name', 100);
            $table->string('family_name', 100);
            $table->date('birth_date');
            $table->enum('gender', ['male','female','other','unknown']);
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address_line', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('language', 10)->default('vi');
            $table->boolean('is_current')->default(true);
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');
        });

        Schema::create('patient_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->string('system', 255);
            $table->string('value', 100);
            $table->string('assigning_authority', 100)->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->unique(['system', 'value']);
        });

        Schema::create('next_of_kin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->string('relationship', 50);
            $table->string('name', 200);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_emergency_contact')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('next_of_kin');
        Schema::dropIfExists('patient_identifiers');
        Schema::dropIfExists('patient_demographics');
    }
};
