<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            // Type controls which panel(s) members of this org belong to.
            // Values match config('makeen.organization_types').
            $table->enum('type', ['association', 'donor', 'excellence_team', 'consultant_firm'])
                ->index();

            // Identity
            $table->string('name_ar');
            $table->string('name_en')->nullable();

            // Sprint-1 association registration fields (subset reused for other types)
            $table->string('license_number')->nullable();
            $table->string('license_authority')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('website')->nullable();

            // Lifecycle (mainly for self-registered associations awaiting admin approval)
            $table->enum('status', ['pending', 'active', 'suspended', 'archived'])
                ->default('pending')
                ->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
