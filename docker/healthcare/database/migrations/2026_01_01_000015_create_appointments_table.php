<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('patient_id')->constrained();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->enum('status', ['proposed','pending','booked','arrived','fulfilled','cancelled','noshow','entered-in-error','checked-in','waitlist'])->default('booked');
            $table->string('appointment_type', 100)->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('roomed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['provider_id', 'start_at']);
            $table->index(['department_id', 'start_at']);
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained();
            $table->enum('channel', ['sms','email','push']);
            $table->timestamp('send_at');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending','sent','failed','cancelled'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('appointments');
    }
};
