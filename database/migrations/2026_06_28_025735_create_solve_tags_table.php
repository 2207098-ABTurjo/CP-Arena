<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solve_tags', function (Blueprint $table) {
            $table->increments('tag_id');
            $table->integer('user_id');
            $table->string('tags', 100)->nullable();
            $table->integer('solved_count')->default(0);
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solve_tags');
    }
};