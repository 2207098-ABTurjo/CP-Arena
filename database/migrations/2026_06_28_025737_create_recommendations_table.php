<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->increments('rec_id');
            $table->integer('user_id');
            $table->integer('problem_id');
            $table->date('rec_date')->nullable();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('problem_id')->references('problem_id')->on('problems')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};