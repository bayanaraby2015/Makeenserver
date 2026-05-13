<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();

            // 'developmental_impact' | 'sustainability' | 'institutional_empowerment'
            $table->string('domain', 64);

            // Sub-criterion / category within the domain
            $table->string('criterion');

            // Indicator name (Arabic — used in evaluation forms)
            $table->text('indicator');

            $table->unsignedSmallInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['domain', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_definitions');
    }
};
