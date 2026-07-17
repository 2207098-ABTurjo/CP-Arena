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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};