<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profile_language', function (Blueprint $table) {
            $table->foreignId('freelancer_profile_id')
                ->constrained('freelancer_profiles')
                ->cascadeOnDelete();

            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();

            $table->string('proficiency');

            $table->primary([
                'freelancer_profile_id',
                'language_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profile_language');
    }
};