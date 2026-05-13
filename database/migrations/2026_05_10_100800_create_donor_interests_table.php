<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_organization_id')->nullable()->constrained('organizations')->nullOnDelete();

            $table->decimal('proposed_amount', 15, 2)->nullable();
            $table->text('message')->nullable();

            $table->string('status', 32)->default('pending');
            // pending | acknowledged | matched | declined

            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['initiative_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_interests');
    }
};
