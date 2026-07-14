<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    if (!Schema::hasTable('resetpassword')) {
        Schema::create('resetpassword', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->integer('verification_code');
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
        });
    }
}
};
