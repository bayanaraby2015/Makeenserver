<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('service_evaluations')
            ->select('service_type', 'service_id', 'evaluator_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as duplicate_count'))
            ->whereNotNull('service_id')
            ->whereNotNull('evaluator_id')
            ->groupBy('service_type', 'service_id', 'evaluator_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('service_evaluations')
                ->where('service_type', $duplicate->service_type)
                ->where('service_id', $duplicate->service_id)
                ->where('evaluator_id', $duplicate->evaluator_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('service_evaluations', function (Blueprint $table): void {
            $table->unique(
                ['service_type', 'service_id', 'evaluator_id'],
                'service_evaluations_once_per_user_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('service_evaluations', function (Blueprint $table): void {
            $table->dropUnique('service_evaluations_once_per_user_unique');
        });
    }
};
