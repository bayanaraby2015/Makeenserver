<?php

namespace App\Support;

use App\Models\Initiative;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConsultantInitiativeScope
{
    public static function queryFor(?User $user): Builder
    {
        $query = Initiative::query();

        self::apply($query, $user?->consultantSpecializations()->pluck('specialization')->all() ?? []);

        return $query;
    }

    /**
     * @param  array<int, string>  $specializations
     */
    public static function apply(Builder $query, array $specializations): Builder
    {
        return $query->where(function (Builder $query) use ($specializations): void {
            if ($specializations === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            foreach ($specializations as $specialization) {
                $query->orWhereJsonContains('specializations', $specialization)
                    ->orWhere(function (Builder $fallback) use ($specialization): void {
                        $fallback
                            ->whereNull('specializations')
                            ->where('domain', $specialization);
                    });
            }
        });
    }
}
