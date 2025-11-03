<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travels', function (Blueprint $table) {
            // Match Worksuite user PK type (INT)
            $table->increments('id');

            // FKs that point to users.id (unsigned INT)
            $table->unsignedInteger('employee_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('purpose_of_visit', 191)->nullable();
            $table->string('place_of_visit', 191)->nullable();
            $table->string('description', 191)->nullable();

            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('created_by');

            // FKs — INT ↔ INT
            $table->foreign('employee_id')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travels');
    }
};
