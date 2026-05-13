<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requester_organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('initiative_id')->nullable()->constrained('initiatives')->nullOnDelete();
            $table->foreignId('consultant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('specialization', 120)->nullable();
            $table->string('subject');
            $table->text('details')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', [
                'requested',
                'accepted',
                'rejected',
                'scheduled',
                'completed',
                'cancelled',
            ])->default('requested');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('proposed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};

