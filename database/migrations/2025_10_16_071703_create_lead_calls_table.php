<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_calls', function (Blueprint $table) {
            $table->id();

            // Foreign keys / relationships
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Call details
            $table->string('to_number', 50)->nullable();
            $table->string('subject')->nullable();
            $table->enum('call_type', ['inbound', 'outbound'])->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds or minutes');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->string('recording')->nullable()->comment('Recording file path or URL');
            $table->string('status')->nullable()->comment('e.g., completed, missed, failed');
            $table->text('description')->nullable();
            $table->string('call_result')->nullable()->comment('Outcome or result of the call');

            $table->timestamps();

            // (Optional) Foreign key constraints
            // $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_calls');
    }
};
