<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->increments('sub_id');
            $table->integer('user_id');
            $table->integer('problem_id');
            $table->text('code')->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('time_ms')->nullable();
            $table->integer('memory_kb')->nullable();
            $table->timestamp('submission_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};