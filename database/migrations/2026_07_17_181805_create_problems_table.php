<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->increments('problem_id');
            $table->string('title', 200);
            $table->integer('rating')->nullable();
            $table->string('tags', 255)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('cf_contest_id', 20)->nullable();
            $table->string('cf_index', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};