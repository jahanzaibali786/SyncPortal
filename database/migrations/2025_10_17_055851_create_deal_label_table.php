<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_label', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('label_id');
            $table->timestamps();

            $table->foreign('deal_id')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');

            $table->foreign('label_id')
                ->references('id')
                ->on('pipeline_labels')
                ->onDelete('cascade');

            $table->unique(['deal_id', 'label_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_label');
    }
};
