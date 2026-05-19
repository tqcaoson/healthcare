<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('mrn')
                ->unique()
                ->index();

            $table->string('status')
                ->default('active');

            $table->timestamp('deceased_at')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};