<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (
            Blueprint $table
        ) {

            $table->id();

            $table->string('event_type');

            $table->timestamp('event_at');

            $table->unsignedBigInteger('user_id')
                ->nullable();

            $table->string('user_name')
                ->nullable();

            $table->string('user_role')
                ->nullable();

            $table->string('session_id')
                ->nullable();

            $table->string('ip_address')
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->string('resource_type')
                ->nullable();

            $table->unsignedBigInteger('resource_id')
                ->nullable();

            $table->unsignedBigInteger('patient_id')
                ->nullable();

            $table->string('action')
                ->nullable();

            $table->json('old_values')
                ->nullable();

            $table->json('new_values')
                ->nullable();

            $table->string('request_path')
                ->nullable();

            $table->string('request_method')
                ->nullable();

            $table->uuid('correlation_id');

            $table->string('log_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};