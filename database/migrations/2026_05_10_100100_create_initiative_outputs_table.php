<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order_index');
            $table->string('phase');
            $table->text('activities')->nullable();
            $table->string('output')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->text('output_description')->nullable();
            $table->timestamps();

            $table->index(['initiative_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_outputs');
    }
};
