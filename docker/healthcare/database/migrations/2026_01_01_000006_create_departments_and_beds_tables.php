<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 200);
            $table->enum('type', ['inpatient','outpatient','icu','ed','or','radiology','lab','pharmacy','admin']);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->string('bed_number', 20);
            $table->string('room_number', 20);
            $table->enum('status', ['available','occupied','housekeeping','maintenance','reserved'])->default('available');
            $table->enum('type', ['general','icu','isolation','bariatric'])->default('general');
            $table->unsignedBigInteger('current_patient_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
        Schema::dropIfExists('departments');
    }
};
