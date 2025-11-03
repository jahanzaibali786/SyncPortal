<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // if (!Schema::hasTable('lead_calls')) {
            Schema::create('lead_calls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->index();
                $table->string('to_number', 15)->nullable();
                $table->string('subject', 191);
                $table->string('call_type', 30);
                $table->string('duration', 20);
                $table->string('start', 155)->nullable();
                $table->string('end', 155)->nullable();
                $table->string('recording', 1000)->nullable();
                $table->string('status', 25)->default('answer')->nullable();
                $table->unsignedInteger('user_id');
                $table->text('description')->nullable();
                $table->text('call_result')->nullable();
                $table->timestamps();
            });
        // }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_calls');
    }
};
