<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('freelancer_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();

            $table->foreignId('job_id')
                ->constrained('job_posts')
                ->cascadeOnDelete();

            $table->text('cover_letter');

            $table->unsignedInteger('estimated_duration')->nullable();

            $table->decimal('bid_amount', 10, 2);

            $table->decimal('agreed_amount', 10, 2)->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'withdrawn',
            ])->default('pending');

            $table->unique(['freelancer_id', 'job_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};