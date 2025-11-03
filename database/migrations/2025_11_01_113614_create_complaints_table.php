<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('complaint_from');     // users.id (INT)
            $table->unsignedInteger('complaint_against');   // users.id (INT)

            $table->string('title', 191);
            $table->date('complaint_date')->nullable();
            $table->string('description', 191)->nullable();

            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['complaint_from', 'complaint_against']);
            $table->index('created_by');

            // FKs — INT ↔ INT
            $table->foreign('complaint_from')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('complaint_against')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
