<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('initiative_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order_index');
            $table->text('risk');
            $table->string('likelihood', 16)->nullable(); // high|medium|low
            $table->string('impact', 16)->nullable();     // high|medium|low
            $table->text('mitigation')->nullable();
            $table->timestamps();

            $table->index(['initiative_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initiative_risks');
    }
};
