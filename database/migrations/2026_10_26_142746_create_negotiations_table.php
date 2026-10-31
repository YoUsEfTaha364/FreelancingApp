<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete();

            $table->foreignId('proposed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('proposed_amount', 10, 2);

            $table->text('message')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'superseded',
                'withdrawn',
            ])->default('pending');

            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('negotiations');
    }
};
