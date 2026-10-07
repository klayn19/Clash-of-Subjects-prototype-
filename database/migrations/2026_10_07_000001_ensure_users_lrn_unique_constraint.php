<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure lrn column exists on users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'lrn')) {
                $table->string('lrn', 12)->nullable()->after('id');
            }
        });

        // 2. Ensure unique index exists on users.lrn
        try {
            // Check if unique index already exists on lrn
            $hasUniqueIndex = false;
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM users WHERE Column_name = 'lrn' AND Non_unique = 0");
                $hasUniqueIndex = !empty($indexes);
            }

            if (!$hasUniqueIndex) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('lrn', 'users_lrn_unique');
                });
            }
        } catch (\Throwable $e) {
            // If the unique index or constraint already exists, log or ignore gracefully
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_lrn_unique');
            });
        } catch (\Throwable $e) {
            // Index might not exist
        }
    }
};

