<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_verified')->default(false)->after('role');
            });
        }

        if (!Schema::hasTable('student_rosters')) {
            Schema::create('student_rosters', function (Blueprint $table) {
                $table->id();
                $table->string('lrn', 12)->unique();
                $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        $now = now();
        foreach (['Grade 7 - Rizal', 'Grade 7 - Bonifacio', 'Grade 7 - Jacinto'] as $name) {
            DB::table('sections')->insertOrIgnore([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_rosters');

        if (Schema::hasColumn('users', 'is_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_verified');
            });
        }
    }
};
