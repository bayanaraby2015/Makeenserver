<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'sustainability' => 'endowments_investment',
            'institutional_empowerment' => 'institutional_planning',
        ];

        foreach ($map as $old => $new) {
            DB::table('consultant_specializations')
                ->where('specialization', $old)
                ->update(['specialization' => $new]);
        }

        DB::table('initiatives')
            ->whereNotNull('specializations')
            ->orderBy('id')
            ->select(['id', 'specializations'])
            ->chunkById(100, function ($initiatives) use ($map): void {
                foreach ($initiatives as $initiative) {
                    $values = json_decode((string) $initiative->specializations, true);

                    if (! is_array($values)) {
                        continue;
                    }

                    $normalized = array_values(array_unique(array_map(
                        fn (string $value): string => $map[$value] ?? $value,
                        array_filter($values, fn (mixed $value): bool => is_string($value) && $value !== ''),
                    )));

                    DB::table('initiatives')
                        ->where('id', $initiative->id)
                        ->update(['specializations' => json_encode($normalized, JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
