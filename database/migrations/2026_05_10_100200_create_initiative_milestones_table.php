<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order_index');
            $table->string('phase');
            $table->text('outputs')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->json('execution_months')->nullable(); // [1, 2, 3, ...] — 12 month grid
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['initiative_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_milestones');
    }
};
