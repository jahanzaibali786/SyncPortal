<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_label', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lead_id'); // matches leads.id (int unsigned)
            $table->unsignedBigInteger('label_id'); // matches pipeline_labels.id (bigint unsigned)
            $table->timestamps();

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            $table->foreign('label_id')
                ->references('id')
                ->on('pipeline_labels')
                ->onDelete('cascade');

            $table->unique(['lead_id', 'label_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_label');
    }
};

