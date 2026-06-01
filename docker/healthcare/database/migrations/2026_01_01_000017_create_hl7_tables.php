<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hl7_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_id');
            $table->string('message_type', 20);
            $table->string('destination', 100);
            $table->longText('payload');
            $table->enum('status', ['pending','sent','failed','dead'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();

            $table->index(['status', 'next_retry_at']);
        });

        Schema::create('hl7_inbox', function (Blueprint $table) {
            $table->id();
            $table->string('message_control_id', 100)->unique();
            $table->string('message_type', 20);
            $table->string('source', 100);
            $table->longText('payload');
            $table->enum('status', ['received','processing','processed','failed'])->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hl7_inbox');
        Schema::dropIfExists('hl7_outbox');
    }
};
