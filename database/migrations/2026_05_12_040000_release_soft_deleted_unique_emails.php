<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->where('email', 'not like', '%#deleted-%')
            ->orderBy('id')
            ->select(['id', 'email'])
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['email' => $user->email.'#deleted-'.$user->id]);
                }
            });

        DB::table('organizations')
            ->whereNotNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', 'not like', '%#deleted-%')
            ->orderBy('id')
            ->select(['id', 'email'])
            ->chunkById(100, function ($organizations): void {
                foreach ($organizations as $organization) {
                    DB::table('organizations')
                        ->where('id', $organization->id)
                        ->update(['email' => $organization->email.'#deleted-'.$organization->id]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
