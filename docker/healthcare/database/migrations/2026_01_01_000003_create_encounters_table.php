<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (
            Blueprint $table
        ) {

            $table->id();

            $table->uuid('uuid')
                ->unique();

            $table->foreignId('patient_id')
                ->constrained();

            $table->string('encounter_type');

            $table->string('status');

            $table->string('class');

            $table->timestamp('period_start');

            $table->timestamp('period_end')
                ->nullable();

            $table->unsignedBigInteger('department_id')
                ->nullable();

            $table->unsignedBigInteger('bed_id')
                ->nullable();

            $table->text('reason_text')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};