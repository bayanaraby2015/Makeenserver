<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Step 1: make `initiatives.domain` nullable so new records can omit it
     *         (the form-facing field is now `specializations` only).
     * Step 2: backfill `specializations` from any legacy `domain` value
     *         when the array is empty/null — keeps historical records
     *         visible under the unified "مجال المبادرة" column.
     */
    public function up(): void
    {
        Schema::table('initiatives', function (Blueprint $table): void {
            $table->string('domain', 64)->nullable()->change();
        });

        DB::table('initiatives')
            ->whereNotNull('domain')
            ->where(function ($query): void {
                $query->whereNull('specializations')
                    ->orWhere('specializations', '[]')
                    ->orWhere('specializations', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('initiatives')
                        ->where('id', $row->id)
                        ->update(['specializations' => json_encode([$row->domain], JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('initiatives', function (Blueprint $table): void {
            $table->string('domain', 64)->nullable(false)->change();
        });
    }
};
