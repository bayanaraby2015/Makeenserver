<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order_index');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->text('linked_outputs')->nullable();
            $table->timestamps();

            $table->index(['initiative_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_payments');
    }
};
