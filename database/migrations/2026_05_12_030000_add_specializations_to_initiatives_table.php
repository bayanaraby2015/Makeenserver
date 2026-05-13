<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('initiatives', function (Blueprint $table): void {
            $table->json('specializations')->nullable()->after('domain');
        });

        DB::table('initiatives')
            ->select(['id', 'domain'])
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('initiatives')
                        ->where('id', $row->id)
                        ->update([
                            'specializations' => json_encode([$row->domain], JSON_UNESCAPED_UNICODE),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('initiatives', function (Blueprint $table): void {
            $table->dropColumn('specializations');
        });
    }
};
