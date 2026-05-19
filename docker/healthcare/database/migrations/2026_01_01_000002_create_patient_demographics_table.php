<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_demographics', function (
            Blueprint $table
        ) {

            $table->id();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('given_name');

            $table->string('family_name');

            $table->date('birth_date');

            $table->string('gender');

            $table->string('phone')
                ->nullable();

            $table->string('email')
                ->nullable();

            $table->boolean('is_current')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'patient_demographics'
        );
    }
};