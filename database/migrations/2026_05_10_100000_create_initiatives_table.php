<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiatives', function (Blueprint $table) {
            $table->id();

            // Section 2 — Project identity card
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();

            // Domain — one of: 'developmental_impact', 'sustainability', 'institutional_empowerment'
            $table->string('domain', 64);

            // Free-text descriptors
            $table->text('related_criteria')->nullable();
            $table->text('development_justification')->nullable();
            $table->text('main_goal')->nullable();
            $table->text('description')->nullable();
            $table->text('strategic_objectives')->nullable();

            // Ownership
            $table->string('responsible_department')->nullable();
            $table->string('owner_name')->nullable();
            $table->text('partners')->nullable();

            // Scope
            $table->string('beneficiaries_scope')->nullable(); // النطاق البشري (free text)
            $table->unsignedSmallInteger('duration_weeks')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Financial scope (calculated total or manual override)
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('currency', 3)->default('SAR');

            // Workflow
            $table->string('status', 32)->default('draft');
            // draft | submitted | under_review | approved | rejected | revisions_requested
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['organization_id']);
            $table->index(['domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiatives');
    }
};
