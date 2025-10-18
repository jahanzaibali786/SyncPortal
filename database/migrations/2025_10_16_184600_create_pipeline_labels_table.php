<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipeline_id'); // Foreign key to lead_pipelines
            $table->string('name');
            $table->string('label_color')->nullable(); // Optional color
            $table->unsignedBigInteger('added_by'); // User who added
            $table->timestamps();

            $table->foreign('pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_labels');
    }
};
