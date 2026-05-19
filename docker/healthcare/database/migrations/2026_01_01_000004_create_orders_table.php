<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (
            Blueprint $table
        ) {

            $table->id();

            $table->uuid('uuid')
                ->unique();

            $table->string('order_number')
                ->unique();

            $table->foreignId('encounter_id')
                ->constrained();

            $table->foreignId('patient_id')
                ->constrained();

            $table->string('order_type');

            $table->string('status');

            $table->string('priority');

            $table->unsignedBigInteger('ordered_by');

            $table->timestamp('ordered_at');

            $table->timestamp('scheduled_for')
                ->nullable();

            $table->text('clinical_indication')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};